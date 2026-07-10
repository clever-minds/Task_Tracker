<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmployee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Employee $employee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome, {$this->employee->name}!",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome-employee',
            with: ['link' => url('/c/'.$this->employee->chat_token)],
        );
    }
}
