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

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;

        $employee->chatMessages()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function sendReply(ChatMessageService $chat): void
    {
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
    }

    public function acknowledgeTask(int $taskId): void
    {
        $this->dismissedPrompts[$taskId] = true;
    }

    public function switchToTask(int $taskId): void
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

        $newTask->update(['status' => 'in_progress', 'started_at' => $newTask->started_at ?? now()]);
        TaskEvent::create([
            'task_id' => $newTask->id,
            'actor_type' => 'employee',
            'actor_id' => $this->employee->id,
            'event_type' => 'status_changed',
            'message' => 'in_progress',
        ]);

        $this->dismissedPrompts[$taskId] = true;
    }

    public function render()
    {
        $messages = $this->employee->chatMessages()->with('task')->oldest()->get();
        $openTasks = $this->employee->tasks()->whereIn('status', ['todo', 'in_progress', 'blocked'])->latest()->get();
        $myTasks = $this->employee->tasks()->whereIn('status', ['todo', 'in_progress', 'blocked', 'paused'])->latest()->get();

        return view('livewire.chat-thread', [
            'messages' => $messages,
            'openTasks' => $openTasks,
            'myTasks' => $myTasks,
        ]);
    }
}
