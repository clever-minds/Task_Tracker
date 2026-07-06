<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use App\Models\Task;
use App\Services\ChatMessageService;
use Livewire\Component;

class Show extends Component
{
    public Employee $employee;

    public string $newTaskTitle = '';
    public string $newTaskDescription = '';
    public string $newTaskPriority = 'normal';
    public bool $showTaskForm = false;

    public array $commentText = [];

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;
    }

    public function assignTask(ChatMessageService $chat): void
    {
        $this->validate([
            'newTaskTitle' => 'required|string|max:255',
            'newTaskDescription' => 'nullable|string',
            'newTaskPriority' => 'required|in:normal,urgent',
        ]);

        $task = Task::create([
            'employee_id' => $this->employee->id,
            'title' => $this->newTaskTitle,
            'description' => $this->newTaskDescription ?: null,
            'source' => 'manager_assigned',
            'priority' => $this->newTaskPriority,
            'assigned_by' => auth()->id(),
        ]);

        $chat->pushTask($task);

        $this->reset(['newTaskTitle', 'newTaskDescription', 'newTaskPriority', 'showTaskForm']);
        $this->newTaskPriority = 'normal';
    }

    public function addComment(int $taskId, ChatMessageService $chat): void
    {
        $text = trim($this->commentText[$taskId] ?? '');

        if ($text === '') {
            return;
        }

        $task = Task::findOrFail($taskId);
        $chat->pushComment($task, auth()->user(), $text);

        $this->commentText[$taskId] = '';
    }

    public function render()
    {
        $this->employee->load(['tasks' => fn ($q) => $q->latest(), 'dailyLogs' => fn ($q) => $q->latest('log_date')]);

        $completedTasks = $this->employee->tasks->where('status', 'done');
        $reworkRate = $completedTasks->count() > 0
            ? round($completedTasks->sum('reopened_count') / $completedTasks->count(), 2)
            : null;

        $avgCloseTime = $completedTasks
            ->filter(fn ($t) => $t->started_at && $t->completed_at)
            ->avg(fn ($t) => $t->started_at->diffInHours($t->completed_at));

        $idleDays = $this->employee->dailyLogs->whereNull('replied_at')->count();

        return view('livewire.employees.show', [
            'metrics' => [
                'avg_close_time_hours' => $avgCloseTime ? round($avgCloseTime, 1) : null,
                'rework_rate' => $reworkRate,
                'idle_days' => $idleDays,
            ],
        ]);
    }
}
