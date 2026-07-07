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

    public string $adminMessage = '';
    public ?string $filterDate = null;

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

    public function sendAdminMessage(ChatMessageService $chat): void
    {
        $text = trim($this->adminMessage);

        if ($text === '') {
            return;
        }

        if (str_starts_with($text, '/task ')) {
            $taskTitle = trim(substr($text, 6));

            if ($taskTitle !== '') {
                $parts = explode('|', $taskTitle);
                $title = trim($parts[0]);
                $description = isset($parts[1]) ? trim($parts[1]) : null;
                $priority = isset($parts[2]) ? strtolower(trim($parts[2])) : 'normal';

                if (! in_array($priority, ['normal', 'urgent'])) {
                    $priority = 'normal';
                }

                $task = Task::create([
                    'employee_id' => $this->employee->id,
                    'title' => $title,
                    'description' => $description,
                    'source' => 'manager_assigned',
                    'priority' => $priority,
                    'assigned_by' => auth()->id(),
                ]);

                $chat->pushTask($task);
            }
        } else {
            $chat->pushManagerMessage($this->employee, auth()->user(), $text);
        }

        $this->adminMessage = '';
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        if ($task->status !== 'done') {
            $this->employee->chatMessages()->where('task_id', $task->id)->delete();
            $task->delete();
        }
    }

    public function render()
    {
        $this->employee->load(['tasks' => fn ($q) => $q->latest(), 'dailyLogs' => fn ($q) => $q->latest('log_date')->with('task')]);

        $allTasks = $this->employee->tasks;
        $completedTasks = $allTasks->where('status', 'done');

        if ($this->filterDate) {
            $completedTasks = $completedTasks->filter(fn ($t) => $t->completed_at && $t->completed_at->toDateString() === $this->filterDate);
        }

        $reworkRate = $completedTasks->count() > 0
            ? round($completedTasks->sum('reopened_count') / $completedTasks->count(), 2)
            : null;

        $avgCloseTime = $completedTasks
            ->filter(fn ($t) => $t->started_at && $t->completed_at)
            ->avg(fn ($t) => $t->started_at->diffInHours($t->completed_at));

        $idleDays = $this->employee->dailyLogs->whereNull('replied_at')->count();

        // Calculate advanced metrics
        $totalTasksCount = $allTasks->count();
        $completionRate = $totalTasksCount > 0
            ? round(($completedTasks->count() / $totalTasksCount) * 100)
            : 0;

        $inProgressTask = $allTasks->firstWhere('status', 'in_progress');
        $hasAwayLogToday = $this->employee->dailyLogs
            ->where('log_date', now()->startOfDay())
            ->where('reply_text', 'Marked today as away')
            ->isNotEmpty();

        $activeStatus = 'active';
        $activeStatusDetails = 'Online & Active';

        if ($inProgressTask) {
            $activeStatus = 'working';
            $activeStatusDetails = 'Working on: ' . $inProgressTask->title;
        } elseif ($hasAwayLogToday) {
            $activeStatus = 'away';
            $activeStatusDetails = 'Away for today';
        } elseif ($this->employee->last_seen_at === null || $this->employee->last_seen_at->lt(now()->subHours(24))) {
            $activeStatus = 'idle';
            $activeStatusDetails = 'Idle / Inactive';
        }

        $messagesQuery = $this->employee->chatMessages()->with('task')->oldest();
        if ($this->filterDate) {
            $messagesQuery->whereDate('created_at', $this->filterDate);
        }
        $messages = $messagesQuery->get();

        $dailyLogs = $this->employee->dailyLogs;
        if ($this->filterDate) {
            $dailyLogs = $dailyLogs->filter(fn ($l) => $l->log_date && $l->log_date->toDateString() === $this->filterDate);
        }

        return view('livewire.employees.show', [
            'metrics' => [
                'avg_close_time_hours' => $avgCloseTime ? round($avgCloseTime, 1) : null,
                'rework_rate' => $reworkRate,
                'idle_days' => $idleDays,
                'completion_rate' => $completionRate,
                'active_status' => $activeStatus,
                'active_status_details' => $activeStatusDetails,
            ],
            'messages' => $messages,
            'dailyLogs' => $dailyLogs,
            'completedTasks' => $completedTasks,
        ]);
    }
}
