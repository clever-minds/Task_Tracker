<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Services\ChatMessageService;
use Livewire\Component;

class ChatThread extends Component
{
    public Employee $employee;

    public string $activeTab = 'chat';

    public string $replyText = '';

    public ?string $pendingStatus = null;
    public ?int $selectedTaskId = null;
    public string $nextPlanText = '';

    public bool $showAddTask = false;
    public string $newTaskTitle = '';

    public array $dismissedPrompts = [];
    public bool $isAway = false;

    public string $taskStatusFilter = 'all';
    public ?string $chatDate = null;

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;

        $employee->chatMessages()->whereNull('read_at')->update(['read_at' => now()]);

        $this->isAway = $employee->dailyLogs()
            ->where('log_date', now()->startOfDay())
            ->where('reply_text', 'Marked today as away')
            ->exists();

        $this->chatDate = now()->toDateString();
    }

    public function sendReply(ChatMessageService $chat): void
    {
        $this->chatDate = now()->toDateString();
        $text = trim($this->replyText);

        if ($text === '') {
            return;
        }

        $chat->recordFreeReply($this->employee, $text);
        $this->replyText = '';
    }

    public function chooseStatus(string $status): void
    {
        $this->pendingStatus = $status;
        $this->selectedTaskId = null;
        $this->nextPlanText = '';
    }

    public function selectTaskForStatus(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
    }

    public function submitStatusUpdate(ChatMessageService $chat): void
    {
        $this->chatDate = now()->toDateString();
        if (! $this->pendingStatus || ! $this->selectedTaskId) {
            return;
        }

        $task = Task::where('employee_id', $this->employee->id)->findOrFail($this->selectedTaskId);
        $chat->recordStatusUpdate($this->employee, $task, $this->pendingStatus, $this->nextPlanText ?: null);

        $this->reset(['pendingStatus', 'selectedTaskId', 'nextPlanText']);
    }

    public function cancelStatusFlow(): void
    {
        $this->reset(['pendingStatus', 'selectedTaskId', 'nextPlanText']);
    }

    public function addTask(ChatMessageService $chat): void
    {
        $this->chatDate = now()->toDateString();
        $title = trim($this->newTaskTitle);

        if ($title === '') {
            return;
        }

        $task = Task::create([
            'employee_id' => $this->employee->id,
            'title' => $title,
            'source' => 'self_added',
        ]);

        $chat->noteSelfAddedTask($task);

        $this->reset(['newTaskTitle', 'showAddTask']);
    }

    public function markAway(ChatMessageService $chat): void
    {
        $chat->recordAway($this->employee);
        $this->isAway = true;
    }

    public function previousDay(): void
    {
        $this->chatDate = \Illuminate\Support\Carbon::parse($this->chatDate)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $currentDate = \Illuminate\Support\Carbon::parse($this->chatDate);
        if ($currentDate->lt(now()->startOfDay())) {
            $this->chatDate = $currentDate->addDay()->toDateString();
        }
    }

    public function updateTaskStatus(int $taskId, string $status, ChatMessageService $chat): void
    {
        $this->chatDate = now()->toDateString();
        if (! in_array($status, ['todo', 'in_progress', 'done', 'paused', 'cancelled'])) {
            return;
        }

        $task = Task::where('employee_id', $this->employee->id)->findOrFail($taskId);

        if ($status === 'todo') {
            $task->update(['status' => 'todo']);
            $this->employee->update(['last_seen_at' => now()]);

            return;
        }

        $chat->changeTaskStatus($this->employee, $task, $status);
    }

    public function acknowledgeTask(int $taskId): void
    {
        $this->dismissedPrompts[$taskId] = true;
    }

    public function switchToTask(int $taskId, ChatMessageService $chat): void
    {
        $newTask = Task::where('employee_id', $this->employee->id)->findOrFail($taskId);

        $current = Task::where('employee_id', $this->employee->id)
            ->where('status', 'in_progress')
            ->where('id', '!=', $taskId)
            ->first();

        if ($current) {
            $current->update(['status' => 'paused']);
            TaskEvent::create([
                'task_id' => $current->id,
                'actor_type' => 'employee',
                'actor_id' => $this->employee->id,
                'event_type' => 'paused',
                'message' => "Paused to switch to: {$newTask->title}",
            ]);
        }

        $wasDone = $newTask->status === 'done';
        $newTask->update([
            'status' => 'in_progress',
            'started_at' => $newTask->started_at ?? now(),
            'completed_at' => null,
            'reopened_count' => $wasDone ? $newTask->reopened_count + 1 : $newTask->reopened_count,
        ]);

        TaskEvent::create([
            'task_id' => $newTask->id,
            'actor_type' => 'employee',
            'actor_id' => $this->employee->id,
            'event_type' => $wasDone ? 'reopened' : 'status_changed',
            'message' => 'in_progress',
        ]);

        $chat->recordTaskSwitch($this->employee, $current, $newTask);

        $this->employee->update(['last_seen_at' => now()]);

        $this->dismissedPrompts[$taskId] = true;
        $this->activeTab = 'mytasks';
    }

    public function render()
    {
        $messagesQuery = $this->employee->chatMessages()->with('task')->oldest();
        if ($this->chatDate) {
            $messagesQuery->whereDate('created_at', $this->chatDate);
        }
        $messages = $messagesQuery->get();

        $openTasks = $this->employee->tasks()->whereIn('status', ['todo', 'in_progress', 'blocked'])->latest()->get();

        $myTasksQuery = $this->employee->tasks()->latest();
        if ($this->taskStatusFilter !== 'all') {
            $myTasksQuery->where('status', $this->taskStatusFilter);
        }
        $myTasks = $myTasksQuery->get();

        return view('livewire.chat-thread', [
            'messages' => $messages,
            'openTasks' => $openTasks,
            'myTasks' => $myTasks,
        ]);
    }
}
