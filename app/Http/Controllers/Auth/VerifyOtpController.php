<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
use Carbon\Carbon;

class VerifyOtpController extends Controller
{
    protected $maxOtpAttempts = 5;
    protected $maxResendAttempts = 3;
    protected $lockoutMinutes = 15;

    public function show(Request $request)
    {
        if (!$request->session()->has('email')) {
            return redirect()->route('register')
                           ->with('error', 'Phiên xác minh đã hết hạn. Vui lòng đăng ký lại.');
        }

        $email = $request->session()->get('email');
        $user = User::where('email', $email)->firstOrFail();

        $otpExpired = $user->otp_expiry && Carbon::now()->isAfter($user->otp_expiry);

        $cacheKey = "otp_wrong_attempts_{$email}";
        $wrongAttempts = Cache::get($cacheKey, 0);
        $isLocked = $wrongAttempts >= $this->maxOtpAttempts;

        return view('auth.verify-otp', compact('email', 'otpExpired', 'isLocked', 'wrongAttempts'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $email = $request->session()->get('email');
        if (!$email) {
            return redirect()->route('register');
        }

        $user = User::where('email', $email)->firstOrFail();

        $cacheKey = "otp_wrong_attempts_{$email}";
        $wrongAttempts = Cache::get($cacheKey, 0);

        if ($wrongAttempts >= $this->maxOtpAttempts) {
            return back()->with('error', 'Bạn đã nhập sai OTP quá nhiều lần. Vui lòng gửi lại mã mới.');
        }

        if (!$user->otp_expiry || Carbon::now()->isAfter($user->otp_expiry)) {
            return back()->with('error', 'Mã OTP đã hết hạn! Vui lòng gửi lại mã mới.')
                        ->with('otpExpired', true);
        }

        if ((string)$request->otp !== (string)$user->otp_code) {
            $wrongAttempts++;
            Cache::put($cacheKey, $wrongAttempts, now()->addMinutes($this->lockoutMinutes));

            return back()->withErrors(['otp' => "Mã OTP không đúng. Còn " . ($this->maxOtpAttempts - $wrongAttempts) . " lần thử."]);
        }

        $user->update([
            'status'            => 1,
            'otp_code'          => null,
            'otp_expiry'        => null,
            'email_verified_at' => Carbon::now(),
        ]);

        Cache::forget($cacheKey);
        Cache::forget("otp_resend_attempts_{$email}");

        Auth::login($user);
        $request->session()->forget('email');

        return redirect()->route('home')
                        ->with('success', 'Xác minh thành công! Chào mừng bạn đến với GhienCine');
    }

    public function resend(Request $request)
    {
        $email = $request->session()->get('email');
        if (!$email) {
            return redirect()->route('register');
        }

        $user = User::where('email', $email)->firstOrFail();

        if ($user->status == 1) {
            return back()->with('error', 'Tài khoản đã được xác minh.');
        }

        $resendCacheKey = "otp_resend_attempts_{$email}";
        $resendAttempts = Cache::get($resendCacheKey, 0);

        if ($resendAttempts >= $this->maxResendAttempts) {
            return back()->with('error', 'Bạn đã gửi lại OTP quá nhiều lần. Vui lòng thử lại sau.');
        }

        $otp = rand(100000, 999999);

        $user->update([
            'otp_code'   => $otp,
            'otp_expiry' => Carbon::now()->addMinutes(5),
        ]);

        Mail::to($email)->send(new OtpMail($otp, $user->full_name, 'register'));

        Cache::put($resendCacheKey, $resendAttempts + 1, now()->addMinutes($this->lockoutMinutes));
        Cache::forget("otp_wrong_attempts_{$email}");

        return back()->with('success', 'Mã OTP mới đã được gửi!');
    }
}