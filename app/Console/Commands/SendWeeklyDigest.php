<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'app:send-weekly-digest';

    protected $description = 'Email the owner a weekly summary of tasks done, carryover, idle days, and rework per employee';

    public function handle(): int
    {
        $start = now()->subWeek()->startOfDay();
        $end = now()->startOfDay();

        $summary = Employee::where('is_active', true)->get()->map(function (Employee $employee) use ($start, $end) {
            $doneThisWeek = $employee->tasks()->where('status', 'done')->whereBetween('completed_at', [$start, $end])->count();
            $carryover = $employee->tasks()->where('status', 'in_progress')->count();
            $idleDays = $employee->dailyLogs()->whereBetween('log_date', [$start, $end])->whereNull('replied_at')->count();
            $reworkCount = $employee->tasks()->where('reopened_count', '>', 0)->sum('reopened_count');

            return [
                'name' => $employee->name,
                'done' => $doneThisWeek,
                'carryover' => $carryover,
                'idle_days' => $idleDays,
                'rework_count' => $reworkCount,
            ];
        });

        $owners = User::where('role', 'owner')->get();

        foreach ($owners as $owner) {
            Mail::to($owner->email)->send(new WeeklyDigestMail($summary, $start, $end));
        }

        $this->info("Weekly digest sent to {$owners->count()} owner(s).");

        return self::SUCCESS;
    }
}
