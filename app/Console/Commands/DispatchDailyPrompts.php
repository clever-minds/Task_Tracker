<?php

namespace App\Console\Commands;

use App\Jobs\SendDailyPromptJob;
use App\Models\Employee;
use Illuminate\Console\Command;

class DispatchDailyPrompts extends Command
{
    protected $signature = 'app:dispatch-daily-prompts';

    protected $description = 'Dispatch the once-daily check-in prompt to each active employee, respecting their checkin_frequency';

    public function handle(): int
    {
        $today = now();
        $dispatched = 0;

        Employee::where('is_active', true)->each(function (Employee $employee) use ($today, &$dispatched) {
            if (! $this->isDueToday($employee, $today)) {
                return;
            }

            SendDailyPromptJob::dispatch($employee);
            $dispatched++;
        });

        $this->info("Dispatched daily prompts to {$dispatched} employee(s).");

        return self::SUCCESS;
    }

    private function isDueToday(Employee $employee, \Illuminate\Support\Carbon $today): bool
    {
        return match ($employee->checkin_frequency) {
            'daily' => true,
            'every_2_days' => $today->dayOfYear % 2 === $employee->id % 2,
            'weekly' => $today->isMonday(),
            default => true,
        };
    }
}
