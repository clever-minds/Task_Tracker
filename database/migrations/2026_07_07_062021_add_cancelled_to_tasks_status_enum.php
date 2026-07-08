<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('todo', 'in_progress', 'blocked', 'done', 'paused', 'cancelled') NOT NULL DEFAULT 'todo'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('todo', 'in_progress', 'blocked', 'done', 'paused', 'cancelled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('todo', 'in_progress', 'blocked', 'done', 'paused') NOT NULL DEFAULT 'todo'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check");
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('todo', 'in_progress', 'blocked', 'done', 'paused'))");
        }
    }
};
