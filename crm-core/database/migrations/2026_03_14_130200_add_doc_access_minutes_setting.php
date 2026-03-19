<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('system_settings')->where('setting_key', 'doc_access_minutes')->exists();
        if (!$exists) {
            DB::table('system_settings')->insert([
                'setting_key' => 'doc_access_minutes',
                'setting_value' => '1440',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->where('setting_key', 'doc_access_minutes')->delete();
    }
};
