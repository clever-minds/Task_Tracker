<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->enum('actor_type', ['manager', 'employee', 'system']);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->enum('event_type', ['created', 'assigned', 'reassigned', 'status_changed', 'commented', 'reopened', 'paused']);
            $table->text('message')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_events');
    }
};
