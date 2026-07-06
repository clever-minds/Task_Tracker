<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backlog_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('suitable_role', ['fresher_mvp', 'laravel_dev', 'flutter_dev', 'freelancer_fullstack', 'any'])->default('any');
            $table->integer('priority')->default(0);
            $table->enum('status', ['open', 'assigned', 'closed'])->default('open');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backlog_items');
    }
};
