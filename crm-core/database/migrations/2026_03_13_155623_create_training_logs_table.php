<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained('training_modules')->onDelete('cascade');
            $table->enum('completion_status', ['pending', 'completed'])->default('pending');
            $table->integer('time_spent')->default(0); // in seconds
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Allow only one log per user per module
            $table->unique(['user_id', 'module_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_logs');
    }
};
