<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\DailyLog;
use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Models\User;

class ChatMessageService
{
    public function pushDailyPrompt(Employee $employee): ChatMessage
    {
        return ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'system',
            'message_type' => 'daily_prompt',
            'content' => "What moved today? What's next whenever you get a sec 🙂",
            'delivered_at' => now(),
        ]);
    }

    public function pushTask(Task $task): ChatMessage
    {
        $managerName = $task->assignedBy?->name ?? 'Manager';

        $message = ChatMessage::create([
            'employee_id' => $task->employee_id,
            'sender_type' => 'manager',
            'sender_id' => $task->assigned_by,
            'message_type' => 'task_push',
            'content' => "📌 New task from {$managerName}: {$task->title}",
            'task_id' => $task->id,
            'delivered_at' => now(),
        ]);

        TaskEvent::create([
            'task_id' => $task->id,
            'actor_type' => 'manager',
            'actor_id' => $task->assigned_by,
            'event_type' => 'assigned',
            'message' => $task->title,
        ]);

        return $message;
    }

    public function pushComment(Task $task, User $manager, string $comment): ChatMessage
    {
        $message = ChatMessage::create([
            'employee_id' => $task->employee_id,
            'sender_type' => 'manager',
            'sender_id' => $manager->id,
            'message_type' => 'comment',
            'content' => $comment,
            'task_id' => $task->id,
            'delivered_at' => now(),
        ]);

        TaskEvent::create([
            'task_id' => $task->id,
            'actor_type' => 'manager',
            'actor_id' => $manager->id,
            'event_type' => 'commented',
            'message' => $comment,
        ]);

        return $message;
    }

    /**
     * Record an employee's free-text reply in their thread and roll it into
     * today's daily_logs row (used for idle calc + weekly digest).
     */
    public function recordFreeReply(Employee $employee, string $text): ChatMessage
    {
        $message = ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'employee',
            'message_type' => 'free_reply',
            'content' => $text,
            'delivered_at' => now(),
        ]);

        DailyLog::updateOrCreate(
            ['employee_id' => $employee->id, 'log_date' => now()->toDateString()],
            ['reply_text' => $text, 'replied_at' => now()]
        );

        $employee->update(['last_seen_at' => now()]);

        return $message;
    }

    /**
     * Record a status-chip reply (Done/In Progress/Blocked) tied to a task,
     * plus the optional "what's next" text.
     */
    public function recordStatusUpdate(Employee $employee, Task $task, string $status, ?string $nextPlan = null): ChatMessage
    {
        $labels = ['done' => '✅ Done', 'in_progress' => '🔄 In Progress', 'blocked' => '🚧 Blocked'];
        $content = trim(($labels[$status] ?? $status)." — {$task->title}".($nextPlan ? "\nNext: {$nextPlan}" : ''));

        $message = ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'employee',
            'message_type' => 'status_update',
            'content' => $content,
            'task_id' => $task->id,
            'delivered_at' => now(),
        ]);

        DailyLog::updateOrCreate(
            ['employee_id' => $employee->id, 'log_date' => now()->toDateString()],
            [
                'task_id' => $task->id,
                'status_reported' => $status,
                'next_plan' => $nextPlan,
                'replied_at' => now(),
            ]
        );

        if ($status === 'done' && $task->status !== 'done') {
            $task->update(['status' => 'done', 'completed_at' => now()]);
            TaskEvent::create([
                'task_id' => $task->id,
                'actor_type' => 'employee',
                'actor_id' => $employee->id,
                'event_type' => 'status_changed',
                'message' => 'Marked done',
            ]);
        } elseif (in_array($status, ['in_progress', 'blocked'])) {
            $wasDone = $task->status === 'done';

            $task->update([
                'status' => $status,
                'started_at' => $task->started_at ?? now(),
                'reopened_count' => $wasDone ? $task->reopened_count + 1 : $task->reopened_count,
            ]);

            TaskEvent::create([
                'task_id' => $task->id,
                'actor_type' => 'employee',
                'actor_id' => $employee->id,
                'event_type' => $wasDone ? 'reopened' : 'status_changed',
                'message' => $status,
            ]);
        }

        $employee->update(['last_seen_at' => now()]);

        return $message;
    }

    /**
     * Employee marks today as away, so idle calc doesn't flag them falsely.
     */
    public function recordAway(Employee $employee): ChatMessage
    {
        DailyLog::updateOrCreate(
            ['employee_id' => $employee->id, 'log_date' => now()->toDateString()],
            ['reply_text' => 'Marked today as away', 'replied_at' => now()]
        );

        $employee->update(['last_seen_at' => now()]);

        return ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'employee',
            'message_type' => 'system_note',
            'content' => 'Marked today as away.',
            'delivered_at' => now(),
        ]);
    }

    public function noteSelfAddedTask(Task $task): ChatMessage
    {
        return ChatMessage::create([
            'employee_id' => $task->employee_id,
            'sender_type' => 'employee',
            'message_type' => 'system_note',
            'content' => "+ Added task: {$task->title}",
            'task_id' => $task->id,
            'delivered_at' => now(),
        ]);
    }
}
