<?php

namespace App\Jobs;

use App\Mail\DailyCheckinReminder;
use App\Models\Employee;
use App\Services\ChatMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendDailyPromptJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public Employee $employee)
    {
    }

    public function handle(ChatMessageService $chat): void
    {
        $chat->pushDailyPrompt($this->employee);

        if ($this->employee->email) {
            Mail::to($this->employee->email)->send(new DailyCheckinReminder($this->employee));
        }
    }
}
