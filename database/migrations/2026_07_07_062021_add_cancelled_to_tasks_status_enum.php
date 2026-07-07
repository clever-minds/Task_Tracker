<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('todo', 'in_progress', 'blocked', 'done', 'paused', 'cancelled') NOT NULL DEFAULT 'todo'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('todo', 'in_progress', 'blocked', 'done', 'paused') NOT NULL DEFAULT 'todo'");
        }
    }
};
