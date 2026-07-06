<?php

namespace App\Livewire;

use App\Models\Employee;
use Livewire\Component;

class TeamOverview extends Component
{
    public function render()
    {
        $employees = Employee::query()
            ->where('is_active', true)
            ->with([
                'tasks' => fn ($q) => $q->whereIn('status', ['todo', 'in_progress', 'blocked'])->latest(),
                'dailyLogs' => fn ($q) => $q->latest('log_date')->limit(1),
            ])
            ->get()
            ->map(function (Employee $employee) {
                $lastLog = $employee->dailyLogs->first();

                $employee->current_task = $employee->tasks->firstWhere('status', 'in_progress') ?? $employee->tasks->first();
                $employee->last_reply_summary = $lastLog?->reply_text ?? ($lastLog?->status_reported ? ucfirst(str_replace('_', ' ', $lastLog->status_reported)) : null);

                return $employee;
            });

        return view('livewire.team-overview', ['employees' => $employees]);
    }
}
