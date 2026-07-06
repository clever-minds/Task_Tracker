<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['system', 'employee', 'manager']);
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('message_type', ['daily_prompt', 'free_reply', 'status_update', 'task_push', 'comment', 'system_note']);
            $table->text('content');
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
