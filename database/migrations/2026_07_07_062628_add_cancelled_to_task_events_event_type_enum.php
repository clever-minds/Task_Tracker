<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE task_events MODIFY event_type ENUM('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused', 'cancelled') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE task_events DROP CONSTRAINT IF EXISTS task_events_event_type_check");
            DB::statement("ALTER TABLE task_events ADD CONSTRAINT task_events_event_type_check CHECK (event_type IN ('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused', 'cancelled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE task_events MODIFY event_type ENUM('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused') NOT NULL");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE task_events DROP CONSTRAINT IF EXISTS task_events_event_type_check");
            DB::statement("ALTER TABLE task_events ADD CONSTRAINT task_events_event_type_check CHECK (event_type IN ('created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused'))");
        }
    }
};
