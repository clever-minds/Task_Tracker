<?php

namespace Tests\Feature;

use App\Models\BacklogItem;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BacklogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_backlog_item(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->actingAs($admin);

        Livewire::test(\App\Livewire\Backlog\Index::class)
            ->set('title', 'Refactor database indexes')
            ->set('description', 'Add compound index to task_events')
            ->set('priority', 5)
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('backlog_items', [
            'title' => 'Refactor database indexes',
            'description' => 'Add compound index to task_events',
            'priority' => 5,
            'status' => 'open',
        ]);
    }

    public function test_admin_can_close_backlog_item(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->actingAs($admin);

        $item = BacklogItem::create([
            'title' => 'Fix sidebar overlap',
            'status' => 'open',
            'priority' => 1,
        ]);

        Livewire::test(\App\Livewire\Backlog\Index::class)
            ->call('close', $item->id);

        $this->assertDatabaseHas('backlog_items', [
            'id' => $item->id,
            'status' => 'closed',
        ]);
    }

    public function test_admin_can_suggest_backlog_item_to_idle_employee(): void
    {
        $admin = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->actingAs($admin);

        $employee = Employee::create([
            'name' => 'Idle Jane',
            'checkin_frequency' => 'daily',
            'chat_token' => 'jane-token',
            'last_seen_at' => now()->subHours(30),
        ]);

        $item = BacklogItem::create([
            'title' => 'Automate checkin emails',
            'description' => 'Write cron schedule',
            'status' => 'open',
            'priority' => 10,
        ]);

        Livewire::test(\App\Livewire\IdlePanel::class)
            ->call('suggestFromBacklog', $employee->id);

        // Assert backlog item is now status = assigned
        $this->assertDatabaseHas('backlog_items', [
            'id' => $item->id,
            'status' => 'assigned',
        ]);

        // Assert a new task was created and assigned to the employee
        $this->assertDatabaseHas('tasks', [
            'employee_id' => $employee->id,
            'title' => 'Automate checkin emails',
            'description' => 'Write cron schedule',
            'source' => 'backlog',
        ]);
    }
}
