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
        Schema::table('leads', function (Blueprint $table) {
            $table->json('status_history')->nullable()->after('status');
            $table->text('status_change_reason')->nullable()->after('status_history');
            $table->integer('npc_attempt_count')->default(0)->after('status_change_reason');
            $table->timestamp('last_npc_attempt')->nullable()->after('npc_attempt_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['status_history', 'status_change_reason', 'npc_attempt_count', 'last_npc_attempt']);
        });
    }
};
