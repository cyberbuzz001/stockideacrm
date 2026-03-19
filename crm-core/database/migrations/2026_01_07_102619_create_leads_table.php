<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->unique();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('status')->default('New'); // New, Contacted, Qualified, Closed

            // AI & Scoring Fields
            $table->integer('lead_score')->default(0);
            $table->float('win_probability')->default(0.0);
            $table->json('enrichment_data')->nullable(); // For data from Clearbit/LinkedIn
            $table->string('sentiment_label')->nullable(); // Positive, Neutral, Negative

            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
