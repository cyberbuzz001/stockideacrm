<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->boolean('has_confirmed_service')->default(false);
            $table->boolean('has_agreed_terms')->default(false);
            $table->timestamp('confirmed_service_at')->nullable();
            $table->timestamp('agreed_terms_at')->nullable();
            $table->json('usage_proof_paths')->nullable(); // Store image paths/URLs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_proofs');
    }
};
