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
        // Drop foreign keys from lead_compliances
        Schema::table('lead_compliances', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });

        // Drop foreign keys from lead_compliance_steps
        Schema::table('lead_compliance_steps', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_compliances', function (Blueprint $table) {
            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
        });

        Schema::table('lead_compliance_steps', function (Blueprint $table) {
            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
        });
    }
};
