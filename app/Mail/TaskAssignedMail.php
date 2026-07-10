<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Employee $employee, public Task $task)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New task: {$this->task->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.task-assigned',
            with: [
                'link' => url('/c/'.$this->employee->chat_token),
                'description' => $this->task->description,
                'priority' => $this->task->priority,
            ],
        );
    }
}
