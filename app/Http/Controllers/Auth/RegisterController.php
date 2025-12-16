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
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email', // Thêm unique cho email
            'phone'     => 'required|digits:10|regex:/^0[3-9][0-9]{8}$/|unique:users,phone',
            'password'  => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'phone.digits' => 'Số điện thoại phải có đúng 10 số.',
            'phone.regex'  => 'Số điện thoại không hợp lệ (ví dụ: 0901234567).',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&).',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        // Tạo OTP
        $otp = rand(100000, 999999);

        try {
            // Tạo user mới (status = 2: chưa xác minh)
            $user = User::create([
                'full_name'   => $request->full_name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'otp_code'    => $otp,
                'otp_expiry'  => Carbon::now()->addMinutes(5),
                'status'      => 2, // chưa xác minh
            ]);

            // Gửi email OTP
            Mail::to($request->email)->send(new OtpMail($otp, $request->full_name, 'register'));

            Log::info('User registered successfully - OTP sent', [
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            session()->put('email', $request->email);

            return redirect()->route('verify-otp.show')
                            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để nhập mã OTP.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Không thể hoàn tất đăng ký. Vui lòng thử lại sau.')
                        ->withInput($request->only('full_name', 'email', 'phone'));
        }
    }
}