<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('lead_id')->comment('The agent credited for this payment');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Backfill user_id based on the lead's assigned_to
        DB::statement('UPDATE payments INNER JOIN leads ON payments.lead_id = leads.id SET payments.user_id = leads.assigned_to');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
