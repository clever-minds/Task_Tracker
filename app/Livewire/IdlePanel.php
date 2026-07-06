<?php

namespace App\Livewire;

use App\Models\BacklogItem;
use App\Models\Employee;
use App\Models\Task;
use App\Services\ChatMessageService;
use Livewire\Component;

class IdlePanel extends Component
{
    const IDLE_THRESHOLD_HOURS = 24;

    public function suggestFromBacklog(Employee $employee, ChatMessageService $chat): void
    {
        $backlogItem = BacklogItem::where('status', 'open')
            ->whereIn('suitable_role', [$employee->role, 'any'])
            ->orderByDesc('priority')
            ->first();

        if (! $backlogItem) {
            $this->dispatch('backlog-empty');

            return;
        }

        $task = Task::create([
            'employee_id' => $employee->id,
            'title' => $backlogItem->title,
            'description' => $backlogItem->description,
            'source' => 'backlog',
            'priority' => 'normal',
            'assigned_by' => auth()->id(),
        ]);

        $chat->pushTask($task);

        $backlogItem->update(['status' => 'assigned']);
    }

    public function render()
    {
        $idleEmployees = Employee::query()
            ->where('is_active', true)
            ->whereDoesntHave('tasks', fn ($q) => $q->where('status', 'in_progress'))
            ->where(function ($q) {
                $q->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', now()->subHours(self::IDLE_THRESHOLD_HOURS));
            })
            ->get();

        return view('livewire.idle-panel', ['idleEmployees' => $idleEmployees]);
    }
}
