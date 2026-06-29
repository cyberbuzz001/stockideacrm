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
        Schema::table('lead_messages', function (Blueprint $table) {
            $table->string('sentiment')->nullable()->after('message'); // positive, neutral, negative
            $table->string('urgency')->nullable()->after('sentiment'); // low, medium, high
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_messages', function (Blueprint $table) {
            $table->dropColumn(['sentiment', 'urgency']);
        });
    }
};
