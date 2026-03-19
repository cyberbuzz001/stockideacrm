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
        Schema::create('internal_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->text('body');
            $table->string('category'); // 'Compliance Alert', 'Target Updates', 'Market Holidays'
            $table->json('recipient_ids'); // Array of user IDs or ['all']
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('sender_id');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_mails');
    }
};
