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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('location')->nullable();
            $table->string('demat_status')->nullable();
            $table->string('source')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            
            // For Call Back / Trial logic
            $table->dateTime('follow_up_date')->nullable();
            $table->dateTime('trial_start_date')->nullable();
            $table->dateTime('trial_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
};
