<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Password Reset Mail',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.reset_password',
            with: ['token' => $this->token]
        );
    }

    public function attachments()
    {
        return [];
    }
}
