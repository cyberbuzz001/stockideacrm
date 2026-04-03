<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $keys = [
            'calling_provider' => 'none',
            'calling_api_key' => '',
            'calling_api_secret' => '',
            'sms_provider' => 'none',
            'sms_api_key' => '',
            'sms_api_secret' => '',
            'whatsapp_provider' => 'none',
            'whatsapp_api_key' => '',
            'whatsapp_api_phone_id' => '',
            'ai_provider' => 'openai',
            'ai_api_key' => '',
        ];

        foreach ($keys as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // No need to delete on down as it's safe to keep these keys.
    }
};
