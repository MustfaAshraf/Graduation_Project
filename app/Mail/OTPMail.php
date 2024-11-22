<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;
    public $otp;
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.otp',
            with: ['otp' => $this->otp]
        );
    }

    public function attachments()
    {
        return [];
    }
}
