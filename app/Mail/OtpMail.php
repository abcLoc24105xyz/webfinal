<?php

namespace App\Mail;

<<<<<<< HEAD
=======
use Illuminate\Bus\Queueable;
>>>>>>> 3a03ec3 (final)
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
<<<<<<< HEAD
    // 🔥 FIX: Xóa "use Queueable" để mail gửi SYNCHRONOUSLY
    use SerializesModels;

    public $otp;
    public $name;
    public $type;
=======
    use Queueable, SerializesModels;

    public $otp;
    public $name;
    public $type; // 'register' hoặc 'reset'
>>>>>>> 3a03ec3 (final)

    public function __construct($otp, $name = null, $type = 'register')
    {
        $this->otp  = $otp;
        $this->name = $name ?? 'Quý khách';
        $this->type = $type;
    }

    public function build()
    {
        $subject = $this->type === 'reset'
            ? 'Đặt lại mật khẩu tài khoản GhienCine'
            : 'Xác minh tài khoản GhienCine';

        return $this->subject('GhienCine | ' . $subject)
                    ->view('emails.otp');
    }
}