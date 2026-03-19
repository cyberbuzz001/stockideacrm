<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For local development SQLite, we'll use raw SQL to duplicate the table structure
        // In MySQL, it would be 'CREATE TABLE leads_2025 LIKE leads'
        
        $driver = DB::connection()->getDriverName();
        $years = [2025, 2026];

        foreach ($years as $year) {
            $tableName = "leads_{$year}";
            
            if (!Schema::hasTable($tableName)) {
                if ($driver === 'sqlite') {
                    // SQLite doesn't support 'CREATE TABLE LIKE'
                    // We generate the schema dump and recreate it
                    $sql = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='leads'")[0]->sql;
                    $sql = str_replace('CREATE TABLE "leads"', 'CREATE TABLE "' . $tableName . '"', $sql);
                    DB::statement($sql);
                } else {
                    // MySQL/PostgreSQL
                    DB::statement("CREATE TABLE {$tableName} LIKE leads");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads_2025');
        Schema::dropIfExists('leads_2026');
    }
};
