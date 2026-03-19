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
        $tables = ['leads', 'leads_2025', 'leads_2026'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (!Schema::hasColumn($tableName, 'status_changed_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->timestamp('status_changed_at')->nullable()->after('status');
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['leads', 'leads_2025', 'leads_2026'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                if (Schema::hasColumn($tableName, 'status_changed_at')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropColumn('status_changed_at');
                    });
                }
            }
        }
    }
};
