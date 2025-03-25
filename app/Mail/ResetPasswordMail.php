<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationCode;
    public $username;

    public function __construct($verificationCode, $username)
    {
        $this->verificationCode = $verificationCode;
        $this->username = $username;
    }

    public function build()
    {
        return $this->markdown('emails.verification-code')
                    ->with([
                        'verificationCode' => $this->verificationCode,
                        'username' => $this->username
                    ])
                    ->subject('Password Reset Verification Code');
    }
}
