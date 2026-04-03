<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objection_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->unique();
            $table->string('title');
            $table->text('script');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed core "Battle Cards"
        $scripts = [
            [
                'tag' => 'Market-High',
                'title' => 'Market Is High',
                'script' => "Agent: 'I completely understand your concern about the market high. However, in our experience, the best time to invest was yesterday, and the second best time is today. High markets often suggest strong momentum, and we have specific strategies for 'Trend Following' that protect your downside while capturing the next move. Would you like to see how we managed the 2021 market highs for our clients?'",
            ],
            [
                'tag' => 'Spouse-Check',
                'title' => 'Check with Spouse',
                'script' => "Agent: 'That’s absolutely fair. Financial decisions should be a family matter. However, usually when clients say this, it's because they have a specific doubt. If I may ask, what part of the plan do you think your spouse would be most concerned about? Is it the risk or the return? Let's address that now so you have the full picture to present to them.'",
            ],
            [
                'tag' => 'Already-Broker',
                'title' => 'Already Have Broker',
                'script' => "Agent: 'That's great! It means you already understand the value of professional advice. We aren't here to replace your existing broker, but to provide an 'Expert Overlay'. Think of us as the specialist surgeon while your broker is the GP. We focus exclusively on High-Alpha research that most retail brokers miss. Why not try our trial for 2 days alongside your current portfolio and compare the results?'",
            ],
            [
                'tag' => 'No-Funds',
                'title' => 'No Funds Right Now',
                'script' => "Agent: 'I hear you. Capital allocation is a priority game. But let me ask you—if I could show you a way to generate a 5-8% return on your idle capital in the next 30 days, would you 'find' the funds? Most of our clients started small; you don't need a fortune to start, you just need to start to make a fortune.'",
            ],
        ];

        foreach ($scripts as $s) {
            DB::table('objection_scripts')->insert(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('objection_scripts');
    }
};
