<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE task_events MODIFY event_type ENUM('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused', 'cancelled') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE task_events MODIFY event_type ENUM('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused') NOT NULL");
        }
    }
};
