<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Mail\OtpMail;
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'required|digits:10|regex:/^0[3-9][0-9]{8}$/|unique:users,phone',
            'password'  => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.email'        => 'Email không hợp lệ.',
            'email.unique'       => 'Email này đã được đăng ký.',
            'phone.digits'       => 'Số điện thoại phải có đúng 10 số.',
            'phone.regex'        => 'Số điện thoại không hợp lệ (ví dụ: 0901234567).',
            'phone.unique'       => 'Số điện thoại này đã được đăng ký.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.min'       => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.regex'     => 'Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&).',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $otp = rand(100000, 999999);

        try {
            // Tạo user mới
            $user = User::create([
                'full_name'   => $request->full_name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'otp_code'    => (string)$otp,
                'otp_expiry'  => Carbon::now()->addMinutes(5),
                'status'      => 2,
                'email_verified_at' => null,
            ]);

            Log::info('User created successfully', ['user_id' => $user->user_id, 'email' => $request->email]);

            // 🔥 FIX: Gửi mail ở BACKGROUND (không chờ)
            // Dispatch job vào queue để không block request
            dispatch(function () use ($request, $otp) {
                try {
                    Mail::to($request->email)->send(new OtpMail($otp, $request->full_name, 'register'));
                    Log::info('OTP mail sent successfully', ['email' => $request->email]);
                } catch (\Exception $e) {
                    Log::error('OTP mail failed', [
                        'email' => $request->email,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->onQueue('default')->delay(0);

            // Lưu email vào session
            $request->session()->put('email', $request->email);

            // 🔥 FIX: Redirect ngay (không chờ mail gửi xong)
            return redirect()->route('verify-otp.show')
                            ->with('success', 'Đăng ký thành công! Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (bao gồm cả thư rác/spam).');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Không thể hoàn tất đăng ký (lỗi hệ thống). Vui lòng thử lại sau.')
                        ->withInput($request->only('full_name', 'email', 'phone'));
        }
    }
}