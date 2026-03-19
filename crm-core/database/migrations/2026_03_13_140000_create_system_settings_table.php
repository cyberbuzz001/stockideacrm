<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        // Seed defaults
        $defaults = [
            'heartbeat_idle_minutes' => '10',
            'heartbeat_inactive_minutes' => '15',
            'auto_checkout_enabled' => '1',
            'shift_end_time' => '17:30',
            'lunch_start' => '13:00',
            'lunch_end' => '13:30',
            'assignment_mode' => 'manual',
            'npc_recycle_limit' => '5',
            'trading_segments' => 'Stock Cash,Nifty Option,MCX,Equity',
            'min_registration_fee' => '0',
            'kyc_mandatory' => '1',
            'data_masking_enabled' => '0',
            'ticker_message' => '',
            'ticker_urgency' => 'normal',
        ];

        foreach ($defaults as $key => $value) {
            \DB::table('system_settings')->insert([
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
