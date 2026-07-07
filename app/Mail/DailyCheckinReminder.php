<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyCheckinReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Employee $employee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quick update whenever you can',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-checkin-reminder',
            with: ['link' => url('/c/'.$this->employee->chat_token)],
        );
    }
}
