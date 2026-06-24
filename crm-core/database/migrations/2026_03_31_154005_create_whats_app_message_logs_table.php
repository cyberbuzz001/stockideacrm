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
        Schema::create('whats_app_message_logs', function (Blueprint $table) {
            $table->id();
            $table->string('direction')->index(); // 'inbound', 'outbound'
            $table->string('from_number')->index();
            $table->string('to_number')->index();
            $table->string('message_id')->unique()->nullable(); // Webhook message ID
            $table->string('template_name')->nullable();
            $table->text('content')->nullable(); // Text content or URL
            $table->string('media_type')->nullable(); // 'text', 'image', 'document'
            $table->string('status')->default('sent'); // sent, delivered, read, failed, received
            $table->foreignId('lead_id')->nullable();
            $table->foreignId('client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_message_logs');
    }
};
