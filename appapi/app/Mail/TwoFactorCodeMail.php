<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; // To hold the 2FA code

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this
            ->subject('Your Two-Factor Authentication Code')
            ->html($this->generateEmailContent()); // Use the custom HTML content
    }

    private function generateEmailContent()
    {
        return "
        <html>
            <body>
                <h1>Your Two-Factor Authentication Code</h1>
                <p>Your verification code is: <strong>{$this->code}</strong></p>
                <p>This code will expire in 10 minutes.</p>
            </body>
        </html>";
    }
}
