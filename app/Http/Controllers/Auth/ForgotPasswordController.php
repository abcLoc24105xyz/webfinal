<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // 1. Trang nhập email để yêu cầu đặt lại mật khẩu
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // 2. Gửi OTP qua email
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.'
        ]);

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            // Kiểm tra trạng thái tài khoản
            if ($user->status != 1) {
                Log::warning('Password reset attempt on unverified account', [
                    'email' => $request->email,
                    'status' => $user->status
                ]);

                return back()->with('error', 'Tài khoản chưa được xác minh. Vui lòng kiểm tra email để xác minh tài khoản trước.');
            }

            // Kiểm tra tài khoản đăng nhập bằng Google
            if ($user->provider === 'google') {
                Log::info('Password reset attempt on Google account', [
                    'email' => $request->email
                ]);

                return back()->with('error', 'Tài khoản Google không thể đặt lại mật khẩu. Vui lòng sử dụng Google để đăng nhập.');
            }

            $otp = rand(100000, 999999);

            $user->update([
                'otp_code'   => $otp,
                'otp_expiry' => Carbon::now()->addMinutes(5),
            ]);

            Mail::to($user->email)->send(new OtpMail($otp, $user->full_name, 'reset'));

            Log::info('OTP sent for password reset', [
                'email' => $request->email,
                'timestamp' => now()
            ]);

            // Sửa tên route cho đúng với định nghĩa ở routes/web.php
            return redirect()->route('password.combined')
                           ->with('email', $user->email)
                           ->with('success', 'Mã OTP đã được gửi đến email của bạn!');

        } catch (\Exception $e) {
            Log::error('Error sending OTP', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Không thể gửi OTP. Vui lòng thử lại sau.');
        }
    }

    // 3. Hiển thị form kết hợp nhập OTP + mật khẩu mới
    public function showCombinedForm()
    {
        // Nếu không có email trong session thì chuyển hướng về form nhập email
        if (!session('email')) {
            return redirect()->route('password.request')
                           ->with('error', 'Phiên đặt lại mật khẩu đã hết hạn hoặc không hợp lệ.');
        }

        return view('auth.passwords.combined');
    }

    // 4. Xử lý xác nhận OTP và cập nhật mật khẩu mới
    public function processCombined(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'otp'                   => 'required|digits:6',
            'password'              => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'password_confirmation' => 'required'
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.digits'   => 'Mã OTP phải có 6 chữ số.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ hoa, một chữ thường, một số và một ký tự đặc biệt (@$!%*?&).',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.'
        ]);

        try {
            $user = User::where('email', $request->email)->firstOrFail();

            // So sánh OTP an toàn
            if ((string)$request->otp !== (string)$user->otp_code) {
                Log::warning('Wrong OTP for password reset', [
                    'email' => $request->email,
                    'ip'    => $request->ip()
                ]);

                return back()->withErrors(['otp' => 'Mã OTP không đúng. Vui lòng thử lại.']);
            }

            // Kiểm tra OTP đã hết hạn chưa
            if (!$user->otp_expiry || Carbon::now()->isAfter($user->otp_expiry)) {
                Log::warning('OTP expired for password reset', [
                    'email' => $request->email
                ]);

                return back()->with('error', 'Mã OTP đã hết hạn! Vui lòng gửi lại mã OTP mới.')
                           ->with('otpExpired', true);
            }

            // Không cho phép mật khẩu mới trùng với mật khẩu cũ
            if (Hash::check($request->password, $user->password)) {
                return back()->with('email', $request->email)
                            ->withErrors(['password' => 'Mật khẩu mới không được trùng với mật khẩu cũ!']);
            }

            $user->update([
                'password'            => Hash::make($request->password),
                'password_changed_at' => now(),
                'otp_code'            => null,
                'otp_expiry'          => null,
            ]);

            // Xóa email khỏi session sau khi hoàn tất
            $request->session()->forget('email');

            Log::info('Password reset successfully', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'timestamp' => now()
            ]);

            return redirect()->route('login')
                           ->with('success', 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay bây giờ.');

        } catch (\Exception $e) {
            Log::error('Error processing password reset', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Lỗi xử lý đặt lại mật khẩu. Vui lòng thử lại.');
        }
    }
}