<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    // 🔥 FIX: Xóa "use Queueable" để mail gửi SYNCHRONOUSLY
    use SerializesModels;

    public $otp;
    public $name;
    public $type;

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