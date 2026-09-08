<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\WhatsAppMessageLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    // -- GET /api/whatsapp/webhook (Meta subscribe handshake) --

    public function test_verify_rejects_when_no_token_configured(): void
    {
        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=anything&hub_challenge=xyz');

        $response->assertStatus(403);
    }

    public function test_verify_rejects_wrong_token(): void
    {
        SystemSetting::set('whatsapp_webhook_verify_token', 'the-real-token');

        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=guessed-wrong&hub_challenge=xyz');

        $response->assertStatus(403);
    }

    public function test_verify_succeeds_and_echoes_challenge(): void
    {
        SystemSetting::set('whatsapp_webhook_verify_token', 'the-real-token');

        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=the-real-token&hub_challenge=xyz-123');

        $response->assertStatus(200);
        $response->assertSee('xyz-123');
    }

    // -- POST /api/whatsapp/webhook, Evolution API payload format --

    public function test_evolution_webhook_rejects_without_configured_api_key(): void
    {
        $response = $this->postJson('/api/whatsapp/webhook', [
            'event' => 'messages.upsert',
            'apikey' => 'anything',
            'data' => ['key' => ['remoteJid' => '919999999999@s.whatsapp.net']],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('whats_app_message_logs', 0);
    }

    public function test_evolution_webhook_rejects_wrong_api_key(): void
    {
        SystemSetting::set('whatsapp_api_key', 'correct-instance-key');

        $response = $this->postJson('/api/whatsapp/webhook', [
            'event' => 'messages.upsert',
            'apikey' => 'guessed-wrong-key',
            'data' => ['key' => ['remoteJid' => '919999999999@s.whatsapp.net']],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('whats_app_message_logs', 0);
    }

    public function test_evolution_webhook_accepts_correct_api_key_and_logs_unmatched_message(): void
    {
        SystemSetting::set('whatsapp_api_key', 'correct-instance-key');

        $response = $this->postJson('/api/whatsapp/webhook', [
            'event' => 'messages.upsert',
            'apikey' => 'correct-instance-key',
            'sender' => '910000000000',
            'data' => [
                'key' => ['remoteJid' => '919999999999@s.whatsapp.net', 'id' => 'msg-1'],
                'message' => ['conversation' => 'Hello there'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('whats_app_message_logs', [
            'from_number' => '919999999999',
            'content' => 'Hello there',
            'lead_id' => null,
        ]);
    }

    // -- POST /api/whatsapp/webhook, Meta Cloud API payload format --

    public function test_meta_webhook_rejects_without_configured_app_secret(): void
    {
        $payload = json_encode(['entry' => []]);

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertStatus(403);
    }

    public function test_meta_webhook_rejects_invalid_signature(): void
    {
        SystemSetting::set('whatsapp_meta_app_secret', 'the-app-secret');

        $payload = json_encode(['entry' => []]);

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=' . hash_hmac('sha256', $payload, 'the-wrong-secret'),
        ], $payload);

        $response->assertStatus(403);
    }

    public function test_meta_webhook_accepts_valid_signature(): void
    {
        SystemSetting::set('whatsapp_meta_app_secret', 'the-app-secret');

        $payload = json_encode(['entry' => []]);
        $signature = 'sha256=' . hash_hmac('sha256', $payload, 'the-app-secret');

        $response = $this->call('POST', '/api/whatsapp/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ], $payload);

        $response->assertStatus(200);
    }
}
