<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        try {
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

            // 🔥 FIX: Gửi mail SYNCHRONOUSLY (không queue)
            try {
                Mail::to($request->email)->send(new OtpMail($otp, $request->full_name, 'register'));
                Log::info('OTP mail sent successfully', ['email' => $request->email, 'otp' => $otp]);
            } catch (\Exception $mailError) {
                Log::warning('OTP mail failed - but continuing', [
                    'email' => $request->email,
                    'error' => $mailError->getMessage(),
                ]);
                // Không throw - user vẫn có thể nhấn "Gửi lại"
            }

            // Lưu email vào session
            $request->session()->put('email', $request->email);

            // 🔥 FIX: Redirect ngay sang verify-otp
            return redirect()->route('verify-otp.show')
                            ->with('success', 'Đăng ký thành công! Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (bao gồm cả thư rác/spam).');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error - return back with errors
            return back()->withErrors($e->validator)->withInput($request->only('full_name', 'email', 'phone'));

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Lỗi: ' . $e->getMessage())
                        ->withInput($request->only('full_name', 'email', 'phone'));
        }
    }
}