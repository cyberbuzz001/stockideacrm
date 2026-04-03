<?php

// Load Laravel's autoload and app
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // 1. Create table if not exists
    if (!Schema::hasTable('objection_scripts')) {
        Schema::create('objection_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->unique();
            $table->string('title');
            $table->text('script');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        echo "Table 'objection_scripts' created.\n";

        // Seed core "Battle Cards"
        $scripts = [
            [
                'tag' => 'Market-High',
                'title' => 'Market Is High',
                'script' => "Agent: 'I completely understand your concern about the market high. However, in our experience, the best time to invest was yesterday, and the second best time is today. High markets often suggest strong momentum, and we have specific strategies for 'Trend Following' that protect your downside while capturing the next move.'",
            ],
            [
                'tag' => 'Spouse-Check',
                'title' => 'Check with Spouse',
                'script' => "Agent: 'That’s absolutely fair. Financial decisions should be a family matter. However, usually when clients say this, it's because they have a specific doubt. If I may ask, what part of the plan do you think your spouse would be most concerned about? Is it the risk or the return?'",
            ],
            [
                'tag' => 'Already-Broker',
                'title' => 'Already Have Broker',
                'script' => "Agent: 'That's great! It means you already understand the value of professional advice. We aren't here to replace your existing broker, but to provide an 'Expert Overlay'. Think of us as the specialist surgeon while your broker is the GP.'",
            ],
            [
                'tag' => 'No-Funds',
                'title' => 'No Funds Right Now',
                'script' => "Agent: 'I hear you. Capital allocation is a priority game. But let me ask you—if I could show you a way to generate a 5-8% return on your idle capital in the next 30 days, would you 'find' the funds?'",
            ],
        ];

        foreach ($scripts as $s) {
            DB::table('objection_scripts')->updateOrInsert(
                ['tag' => $s['tag']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
            echo "Seeded: {$s['tag']}\n";
        }
    } else {
        echo "Table 'objection_scripts' already exists.\n";
    }

    // 2. Update message_templates table for WhatsApp
    if (Schema::hasTable('message_templates')) {
        if (!Schema::hasColumn('message_templates', 'whatsapp_template_name')) {
            Schema::table('message_templates', function (Blueprint $table) {
                $table->string('whatsapp_template_name')->nullable()->after('type');
                $table->string('whatsapp_language')->nullable()->default('en')->after('whatsapp_template_name');
            });
            echo "Updated 'message_templates' for WhatsApp.\n";
        }

        // Add 'whatsapp' to the type enum (if it was an enum, Laravel migrations for change() are tricky, so we'll just check)
        // If it's a string, we're fine. If it's an enum, we might need a raw query.
        DB::statement("ALTER TABLE message_templates MODIFY COLUMN type ENUM('sms', 'email', 'whatsapp') DEFAULT 'sms'");
        echo "Updated 'message_templates' type enum.\n";
    }

    echo "\nSuccess! Migration logic completed.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
