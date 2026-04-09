<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MessageTemplate;

class ComplianceTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'WP_PAYMENT_CONF_AUTO',
                'type' => 'whatsapp',
                'body' => "Hello Sir/Ma’am 👋\nWe have received your payment of ₹{{amount}}.\n\n📌 Service: {{package}}\nDuration: {{days}} Days\n\nYour service will be activated shortly. Kindly confirm.",
            ],
            [
                'name' => 'WP_SERVICE_ACT_AUTO',
                'type' => 'whatsapp',
                'body' => "Your {{package}} service is now activated ✅\n\nYou will receive trading guidance via WhatsApp & calls.\nPlease confirm: “YES, SERVICE STARTED”",
            ],
            [
                'name' => 'WP_TERMS_ACCEPT_AUTO',
                'type' => 'whatsapp',
                'body' => "Before we proceed, please confirm:\n\n✔ Advisory service\n✔ No guaranteed returns\n✔ Market risk involved\n✔ No refund after activation\n\nType: “AGREE”",
            ],
            [
                'name' => 'WP_TRADE_ALERT_AUTO',
                'type' => 'whatsapp',
                'body' => "📊 TRADE ALERT\nStock: {{stock}}\nEntry: {{entry}}\nTarget: {{target}}\nStoploss: {{sl}}\n\nTrade as per risk management.\nPlease confirm once taken.",
            ],
            [
                'name' => 'WP_USAGE_PROOF_AUTO',
                'type' => 'whatsapp',
                'body' => "Sir, please share your trade screenshot for record 📸",
            ],
            [
                'name' => 'WP_REGULAR_FOLLOWUP_AUTO',
                'type' => 'whatsapp',
                'body' => "Hope you are following the trades 👍\nLet us know if you need any support.",
            ],
            [
                'name' => 'WP_SERVICE_CONTINUITY_AUTO',
                'type' => 'whatsapp',
                'body' => "Your service is ongoing and active.\nYou are receiving regular updates from our side.",
            ],
            [
                'name' => 'WP_SERVICE_END_AUTO',
                'type' => 'whatsapp',
                'body' => "Hello Sir 👋\nYour service duration has been completed successfully.\n\nPlease confirm: “SERVICE COMPLETED”",
            ],
            [
                'name' => 'WP_ANTI_DISPUTE_BONUS',
                'type' => 'whatsapp',
                'body' => "We have provided full service as per agreement.\nAll communication and service records are maintained.\nFor any concern, please contact support.",
            ],
        ];

        foreach ($templates as $tmpl) {
            MessageTemplate::updateOrCreate(
                ['name' => $tmpl['name']],
                [
                    'type' => $tmpl['type'],
                    'body' => $tmpl['body'],
                    'created_by' => 1 // System/Admin
                ]
            );
        }
    }
}
