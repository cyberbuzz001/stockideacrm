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

        // Backfill user_id based on the lead's assigned_to. Written as a
        // portable subquery UPDATE (not `UPDATE ... INNER JOIN ... SET`,
        // which is MySQL-only and breaks under the sqlite driver the test
        // suite uses) so it runs the same way on both.
        DB::statement(
            'UPDATE payments SET user_id = (SELECT assigned_to FROM leads WHERE leads.id = payments.lead_id) '
            . 'WHERE EXISTS (SELECT 1 FROM leads WHERE leads.id = payments.lead_id)'
        );
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
