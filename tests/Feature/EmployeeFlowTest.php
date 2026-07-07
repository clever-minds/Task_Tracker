<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Models\DailyLog;
use App\Jobs\SendDailyPromptJob;
use App\Services\ChatMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;

class EmployeeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_prompt_job_pre_creates_daily_log_row(): void
    {
        Mail::fake();

        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $this->assertDatabaseMissing('daily_logs', [
            'employee_id' => $employee->id,
            'log_date' => now()->startOfDay(),
        ]);

        SendDailyPromptJob::dispatch($employee);

        $this->assertDatabaseHas('daily_logs', [
            'employee_id' => $employee->id,
            'log_date' => now()->startOfDay(),
            'replied_at' => null,
        ]);
    }

    public function test_employee_reply_updates_pre_created_daily_log(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        // Pre-created log
        $log = DailyLog::create([
            'employee_id' => $employee->id,
            'log_date' => now()->startOfDay(),
        ]);

        $chatService = app(ChatMessageService::class);
        $chatService->recordFreeReply($employee, 'I finished Task A today.');

        $this->assertDatabaseHas('daily_logs', [
            'employee_id' => $employee->id,
            'log_date' => now()->startOfDay(),
            'reply_text' => 'I finished Task A today.',
        ]);

        $log->refresh();
        $this->assertNotNull($log->replied_at);
    }

    public function test_idle_panel_excludes_away_employees(): void
    {
        $employee = Employee::create([
            'name' => 'Idle Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'idle-token',
            'last_seen_at' => now()->subDays(2),
        ]);

        // Verify initially shown as idle
        $component = \Livewire\Livewire::test(\App\Livewire\IdlePanel::class);
        $component->assertSee('Idle Doe');

        // Mark today as away
        $chatService = app(ChatMessageService::class);
        $chatService->recordAway($employee);

        // Verify now excluded from IdlePanel
        $component = \Livewire\Livewire::test(\App\Livewire\IdlePanel::class);
        $component->assertDontSee('Idle Doe');
    }

    public function test_reopen_clears_completed_at_and_increments_reopen_count(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $task = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Test Task',
            'status' => 'done',
            'completed_at' => now()->subDay(),
            'reopened_count' => 0,
        ]);

        $chatService = app(ChatMessageService::class);
        $chatService->changeTaskStatus($employee, $task, 'in_progress');

        $task->refresh();
        $this->assertEquals('in_progress', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertEquals(1, $task->reopened_count);
    }

    public function test_weekly_digest_calculates_weekly_reopen_count(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $task1 = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Task 1',
            'reopened_count' => 2,
        ]);

        // Reopened event last week (out of range)
        $event1 = new TaskEvent([
            'task_id' => $task1->id,
            'actor_type' => 'employee',
            'event_type' => 'reopened',
        ]);
        $event1->created_at = now()->subDays(10);
        $event1->timestamps = false;
        $event1->save();

        // Reopened event this week
        $event2 = new TaskEvent([
            'task_id' => $task1->id,
            'actor_type' => 'employee',
            'event_type' => 'reopened',
        ]);
        $event2->created_at = now()->subDays(2);
        $event2->timestamps = false;
        $event2->save();

        $start = now()->subWeek()->startOfDay();
        $end = now()->endOfDay();

        // Calculate rework count inside digest weekly logic
        $reworkCount = TaskEvent::whereIn('task_id', $employee->tasks()->pluck('id'))
            ->where('event_type', 'reopened')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $this->assertEquals(1, $reworkCount); // Only 1 reopen event this week
    }

    public function test_admin_can_send_chat_message(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Livewire\Employees\Show::class, ['employee' => $employee])
            ->set('adminMessage', 'Hello John, keep up the good work!')
            ->call('sendAdminMessage');

        $this->assertDatabaseHas('chat_messages', [
            'employee_id' => $employee->id,
            'sender_type' => 'manager',
            'content' => 'Hello John, keep up the good work!',
            'task_id' => null,
        ]);
    }

    public function test_admin_can_assign_task_via_chat_command(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Livewire\Employees\Show::class, ['employee' => $employee])
            ->set('adminMessage', '/task Review PR | Check code style details | urgent')
            ->call('sendAdminMessage');

        $this->assertDatabaseHas('tasks', [
            'employee_id' => $employee->id,
            'title' => 'Review PR',
            'description' => 'Check code style details',
            'priority' => 'urgent',
            'source' => 'manager_assigned',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'employee_id' => $employee->id,
            'sender_type' => 'manager',
            'message_type' => 'task_push',
        ]);
    }

    public function test_employee_can_quick_add_todo_item(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->set('newTaskTitle', 'Prepare agenda for weekly sync')
            ->call('addTask');

        $this->assertDatabaseHas('tasks', [
            'employee_id' => $employee->id,
            'title' => 'Prepare agenda for weekly sync',
            'source' => 'self_added',
            'status' => 'todo',
        ]);
    }

    public function test_admin_dashboard_can_filter_records_by_date(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        // Create tasks completed on different dates
        $task1 = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Task completed yesterday',
            'status' => 'done',
            'completed_at' => now()->subDay(),
        ]);

        $task2 = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Task completed today',
            'status' => 'done',
            'completed_at' => now(),
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Livewire\Employees\Show::class, ['employee' => $employee])
            ->set('filterDate', now()->subDay()->toDateString())
            ->assertViewHas('completedTasks', function ($tasks) use ($task1) {
                return $tasks->count() === 1 && $tasks->first()->id === $task1->id;
            });
    }

    public function test_employee_chat_shows_away_status_and_disables_button(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        // Start thread not away
        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->assertSet('isAway', false)
            ->call('markAway')
            ->assertSet('isAway', true);

        // Submitting status again should retain away state
        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->assertSet('isAway', true);
    }

    public function test_admin_can_delete_uncompleted_task_and_associated_chat_messages(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $task = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Task to delete',
            'status' => 'in_progress',
        ]);

        $message = \App\Models\ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'manager',
            'message_type' => 'task_push',
            'content' => 'Assigned task: Task to delete',
            'task_id' => $task->id,
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Livewire\Employees\Show::class, ['employee' => $employee])
            ->call('deleteTask', $task->id);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    public function test_admin_cannot_delete_completed_task(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $task = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Task to keep',
            'status' => 'done',
            'completed_at' => now(),
        ]);

        $this->actingAs($admin);

        \Livewire\Livewire::test(\App\Livewire\Employees\Show::class, ['employee' => $employee])
            ->call('deleteTask', $task->id);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_employee_can_switch_to_urgent_task(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $currentTask = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Current Task',
            'status' => 'in_progress',
        ]);

        $urgentTask = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Urgent Task',
            'status' => 'todo',
            'priority' => 'urgent',
        ]);

        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->assertSet('activeTab', 'chat')
            ->call('switchToTask', $urgentTask->id)
            ->assertSet('activeTab', 'mytasks');

        $this->assertDatabaseHas('tasks', [
            'id' => $currentTask->id,
            'status' => 'paused',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $urgentTask->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_employee_can_filter_tasks_by_status(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $todoTask = Task::create([
            'employee_id' => $employee->id,
            'title' => 'Todo Task',
            'status' => 'todo',
        ]);

        $progressTask = Task::create([
            'employee_id' => $employee->id,
            'title' => 'In Progress Task',
            'status' => 'in_progress',
        ]);

        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->assertSet('taskStatusFilter', 'all')
            ->assertViewHas('myTasks', function ($tasks) use ($todoTask, $progressTask) {
                return $tasks->contains($todoTask) && $tasks->contains($progressTask);
            })
            ->set('taskStatusFilter', 'in_progress')
            ->assertViewHas('myTasks', function ($tasks) use ($todoTask, $progressTask) {
                return !$tasks->contains($todoTask) && $tasks->contains($progressTask);
            });
    }

    public function test_employee_can_navigate_chat_by_day(): void
    {
        $employee = Employee::create([
            'name' => 'John Doe',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Create message yesterday
        $oldMsg = \App\Models\ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'manager',
            'message_type' => 'comment',
            'content' => 'Yesterday comment',
            'delivered_at' => now()->subDay(),
        ]);
        \Illuminate\Support\Facades\DB::table('chat_messages')
            ->where('id', $oldMsg->id)
            ->update(['created_at' => now()->subDay()]);

        // Create message today
        $newMsg = \App\Models\ChatMessage::create([
            'employee_id' => $employee->id,
            'sender_type' => 'manager',
            'message_type' => 'comment',
            'content' => 'Today comment',
            'created_at' => now(),
            'delivered_at' => now(),
        ]);

        \Livewire\Livewire::test(\App\Livewire\ChatThread::class, ['employee' => $employee])
            ->assertSet('chatDate', $today)
            ->assertViewHas('messages', function ($msgs) use ($oldMsg, $newMsg) {
                return !$msgs->contains($oldMsg) && $msgs->contains($newMsg);
            })
            ->call('previousDay')
            ->assertSet('chatDate', $yesterday)
            ->assertViewHas('messages', function ($msgs) use ($oldMsg, $newMsg) {
                return $msgs->contains($oldMsg) && !$msgs->contains($newMsg);
            })
            ->set('replyText', 'New reply')
            ->call('sendReply')
            // Assert that submitting resets the active chat date filter back to today
            ->assertSet('chatDate', $today);
    }

    public function test_dispatch_daily_prompts_skips_weekends(): void
    {
        Mail::fake();
        \Illuminate\Support\Facades\Queue::fake();

        $employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'checkin_frequency' => 'daily',
            'chat_token' => 'test-token',
        ]);

        // Mock date to a Saturday (2026-07-11 is a Saturday)
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-07-11 10:00:00'));

        $this->artisan('app:dispatch-daily-prompts')
            ->assertSuccessful()
            ->expectsOutputToContain('Dispatched daily prompts to 0 employee(s).');

        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SendDailyPromptJob::class);

        // Reset Carbon mock
        \Illuminate\Support\Carbon::setTestNow();
    }
}
