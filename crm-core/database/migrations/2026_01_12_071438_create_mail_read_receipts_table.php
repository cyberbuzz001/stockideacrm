<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_read_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained('internal_mails')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at');
            $table->timestamps();

            // Unique constraint: one read receipt per user per mail
            $table->unique(['mail_id', 'user_id']);

            // Indexes
            $table->index('mail_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_read_receipts');
    }
};
