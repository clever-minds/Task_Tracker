<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->text('reply_text')->nullable();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status_reported', ['done', 'in_progress', 'blocked'])->nullable();
            $table->text('next_plan')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['employee_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
