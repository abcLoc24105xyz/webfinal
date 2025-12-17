<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
<<<<<<< HEAD
use Illuminate\Support\Facades\Log;
=======
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
>>>>>>> 3a03ec3 (final)
use Carbon\Carbon;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
<<<<<<< HEAD
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

            // 1️⃣ Tạo user
            $user = User::create([
=======
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'required|digits:10|regex:/^0[3-9][0-9]{8}$/|unique:users,phone',
            'password'  => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', // ✅ FIX: Mật khẩu phải có chữ hoa, thường, số, ký tự đặc biệt
            ],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email tối đa 255 ký tự.',
            'phone.digits' => 'Số điện thoại phải có đúng 10 số.',
            'phone.regex'  => 'Số điện thoại không hợp lệ (ví dụ: 0901234567).',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&).',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        // ✅ FIX: Kiểm tra email và phone trong 1 query
        $existingUser = User::where('email', $request->email)
                            ->orWhere('phone', $request->phone)
                            ->first();

        if ($existingUser) {
            if ($existingUser->email === $request->email) {
                if ($existingUser->status == 1) {
                    // Email đã được kích hoạt
                    return back()->withErrors(['email' => 'Email này đã được đăng ký.'])
                                ->withInput($request->only('full_name', 'email', 'phone'));
                } else {
                    // Tài khoản chưa kích hoạt (status = 2), cập nhật lại thay vì xóa
                    // ✅ FIX: Update OTP thay vì delete
                    $otp = rand(100000, 999999);
                    
                    $existingUser->update([
                        'full_name'  => $request->full_name,
                        'password'   => Hash::make($request->password),
                        'otp_code'   => $otp,
                        'otp_expiry' => Carbon::now()->addMinutes(5),
                        'status'     => 2,
                    ]);

                    try {
                        Mail::to($request->email)->send(new OtpMail($otp, $request->full_name, 'register'));
                        
                        session()->put('email', $request->email);
                        
                        Log::info('OTP resent for existing unverified user', [
                            'email' => $request->email,
                            'timestamp' => now()
                        ]);

                        return redirect()->route('verify-otp.show')
                                        ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để nhập mã OTP.');
                    } catch (\Exception $e) {
                        Log::error('Failed to send OTP email', [
                            'email' => $request->email,
                            'error' => $e->getMessage()
                        ]);

                        return back()->with('error', 'Không thể gửi email OTP. Vui lòng thử lại sau.')
                                    ->withInput($request->only('full_name', 'email', 'phone'));
                    }
                }
            }

            if ($existingUser->phone === $request->phone) {
                if ($existingUser->status == 1) {
                    return back()->withErrors(['phone' => 'Số điện thoại này đã được đăng ký.'])
                                ->withInput($request->only('full_name', 'email', 'phone'));
                }
            }
        }

        // Tạo OTP mới
        $otp = rand(100000, 999999);

        try {
            // ✅ FIX: Tạo user trước, rồi gửi email
            User::create([
>>>>>>> 3a03ec3 (final)
                'full_name'   => $request->full_name,
                'email'       => $request->email,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
<<<<<<< HEAD
                'otp_code'    => (string)$otp,
                'otp_expiry'  => Carbon::now()->addMinutes(5),
                'status'      => 2,
                'email_verified_at' => null,
            ]);

            Log::info('✅ User created', ['user_id' => $user->id, 'email' => $request->email]);

            // 2️⃣ Lưu session
            $request->session()->put('email', $request->email);

            // 3️⃣ 🔥 Gửi mail ở BACKGROUND (không chặn response)
            $this->sendOtpEmailAsync($request->email, $otp, $request->full_name);

            // 4️⃣ REDIRECT NGAY (không chờ mail)
            return redirect()->route('verify-otp.show')
                            ->with('success', 'Đăng ký thành công! Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (bao gồm cả thư rác/spam).');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput($request->only('full_name', 'email', 'phone'));

        } catch (\Exception $e) {
            Log::error('❌ Registration error', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Không thể hoàn tất đăng ký (lỗi hệ thống). Vui lòng thử lại sau.')
                        ->withInput($request->only('full_name', 'email', 'phone'));
        }
    }

    // 🔥 Gửi mail ở background - KHÔNG BLOCK request
    private function sendOtpEmailAsync($email, $otp, $fullName)
    {
        register_shutdown_function(function () use ($email, $otp, $fullName) {
            try {
                // Tạo HTML email
                $subject = 'Xác minh tài khoản GhienCine';
                $htmlContent = $this->getOtpEmailHtml($otp, $fullName);

                // 🔥 Gửi email thô (raw email)
                $this->sendRawEmail($email, $subject, $htmlContent);

                Log::info('✅ OTP email sent', ['email' => $email]);
            } catch (\Exception $e) {
                Log::error('⚠️ OTP email failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    // Gửi email raw (không dùng Mail facade)
    private function sendRawEmail($to, $subject, $htmlContent)
    {
        $fromEmail = env('MAIL_FROM_ADDRESS', 'noreply@ghiencine.com');
        $fromName = env('MAIL_FROM_NAME', 'Ghien Cine');

        // Headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: GhienCine OTP System\r\n";

        // 🔥 Gửi mail bằng PHP mail() function
        $result = mail($to, $subject, $htmlContent, $headers);

        if (!$result) {
            throw new \Exception('PHP mail() failed');
        }
    }

    // HTML template cho OTP email
    private function getOtpEmailHtml($otp, $fullName)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh tài khoản - GhienCine</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { text-align: center; padding: 30px 20px; background: linear-gradient(135deg, #a855f7, #ec4899); color: white; }
        .header h2 { font-size: 28px; margin: 0; font-weight: bold; }
        .content { padding: 30px; text-align: center; color: #333333; }
        .otp-box { display: inline-block; background-color: #fef3c7; border: 2px solid #fcd34d; border-radius: 8px; padding: 15px 30px; margin: 25px 0; }
        .otp { font-size: 36px; font-weight: 900; color: #d97706; letter-spacing: 5px; margin: 0; }
        .footer { text-align: center; padding: 15px; font-size: 11px; color: #888; background-color: #f9f9f9; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>GhienCine</h2>
        </div>
        <div class="content">
            <h3 style="font-size: 20px; color: #444; margin-top: 0;">
                Xin chào {$fullName},
            </h3>
            <p style="font-size: 16px; line-height: 1.6;">
                Cảm ơn bạn đã đăng ký tài khoản! Vui lòng sử dụng mã xác minh (OTP) dưới đây để kích hoạt tài khoản:
            </p>
            <div class="otp-box">
                <span class="otp">{$otp}</span>
            </div>
            <p style="font-size: 15px; line-height: 1.5; color: #555;">
                Vui lòng nhập mã này trong vòng <strong>5 phút</strong> để hoàn tất.
            </p>
            <p style="font-size: 14px; line-height: 1.5; color: #777; margin-top: 30px;">
                Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">&copy; 2025 GhienCine. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
=======
                'otp_code'    => $otp,
                'otp_expiry'  => Carbon::now()->addMinutes(5),
                'status'      => 2, // chưa xác minh
            ]);

            // Gửi email OTP
            Mail::to($request->email)->send(new OtpMail($otp, $request->full_name, 'register'));

            Log::info('User registered successfully', [
                'email' => $request->email,
                'phone' => $request->phone,
                'timestamp' => now()
            ]);

            session()->put('email', $request->email);

            return redirect()->route('verify-otp.show')
                            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để nhập mã OTP.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Không thể hoàn tất đăng ký. Vui lòng thử lại sau.')
                        ->withInput($request->only('full_name', 'email', 'phone'));
        }
    }
>>>>>>> 3a03ec3 (final)
}