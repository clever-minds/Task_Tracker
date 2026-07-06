<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('role', ['fresher_mvp', 'laravel_dev', 'flutter_dev', 'freelancer_fullstack']);
            $table->string('chat_token', 64)->unique();
            $table->string('email', 150)->nullable();
            $table->enum('checkin_frequency', ['daily', 'every_2_days', 'weekly'])->default('daily');
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
