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
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('ip_address');
            $table->decimal('effective_hours', 8, 2)->default(0)->after('total_minutes'); // Tracked work time
            $table->integer('idle_duration')->default(0)->after('effective_hours'); // Minutes idle
            $table->integer('total_dials')->default(0)->after('idle_duration');
            $table->decimal('productivity_score', 5, 2)->default(0)->after('total_dials'); // %
            $table->string('status')->default('Active')->after('productivity_score'); // Active, Idle, Inactive, Off-Duty
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
