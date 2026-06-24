<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a free-form text message via configured WhatsApp provider.
     *
     * @param string $to Phone number with country code (e.g. 91...)
     * @param string $text Message content
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function sendText(string $to, string $text)
    {
        $provider = SystemSetting::get('whatsapp_provider', 'none');

        if ($provider === 'meta') {
            $user = auth()->user();
            if ($user && method_exists($user, 'getWhatsAppCredentials')) {
                $credentials = $user->getWhatsAppCredentials();
                $token = $credentials['token'];
                $phoneId = $credentials['phone_id'];
            } else {
                $token = SystemSetting::get('whatsapp_api_key');
                $phoneId = SystemSetting::get('whatsapp_api_phone_id');
            }

            if (!$token || !$phoneId) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Meta API credentials (Token or Phone ID) are missing.',
                ];
            }

            $to = preg_replace('/[^0-9]/', '', $to);
            if (strlen($to) === 10) {
                $to = '91' . $to;
            }

            $url = "https://graph.facebook.com/v17.0/{$phoneId}/messages";
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $text]
            ];

            try {
                $response = Http::withToken($token)->post($url, $payload);
                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'message' => 'WhatsApp message sent successfully via Meta API.',
                    ];
                }
                Log::error('WhatsApp Meta API Error', ['status' => $response->status(), 'body' => $response->json()]);
                return ['success' => false, 'message' => "Meta API Error: " . $response->json('error.message', 'Unknown error')];
            } catch (\Exception $e) {
                Log::error('WhatsApp Meta API Exception', ['message' => $e->getMessage()]);
                return ['success' => false, 'message' => 'Meta API Exception: ' . $e->getMessage()];
            }
        }

        if ($provider === 'evolution') {
            $token = SystemSetting::get('whatsapp_api_key');
            $urlBase = SystemSetting::get('whatsapp_evolution_url', 'http://localhost:8080');
            $instance = SystemSetting::get('whatsapp_evolution_instance', 'shreesvarn');

            if (!$token || !$instance) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Evolution API credentials (API Key or Instance Name) are missing.',
                ];
            }

            $to = preg_replace('/[^0-9]/', '', $to);
            if (strlen($to) === 10) {
                $to = '91' . $to;
            }

            $url = rtrim($urlBase, '/') . "/message/sendText/{$instance}";
            $payload = [
                'number' => $to,
                'text' => $text
            ];

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'apikey' => $token
                ])->post($url, $payload);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'message' => 'WhatsApp message sent successfully via Evolution API.',
                    ];
                }
                Log::error('WhatsApp Evolution API Error', ['status' => $response->status(), 'body' => $response->body()]);
                return ['success' => false, 'message' => 'Evolution API Error: ' . ($response->json('message') ?? $response->body())];
            } catch (\Exception $e) {
                Log::error('WhatsApp Evolution API Exception', ['message' => $e->getMessage()]);
                return ['success' => false, 'message' => 'Evolution API Exception: ' . $e->getMessage()];
            }
        }

        return [
            'success' => false,
            'message' => 'WhatsApp provider is set to None or unsupported in Admin Control Center.',
        ];
    }

    /**
     * Send a template message via Meta Cloud API or Evolution API.
     */
    public static function sendTemplate(string $to, string $templateName, string $languageCode = 'en')
    {
        $provider = SystemSetting::get('whatsapp_provider', 'none');

        if ($provider === 'meta') {
            $user = auth()->user();
            if ($user && method_exists($user, 'getWhatsAppCredentials')) {
                $credentials = $user->getWhatsAppCredentials();
                $token = $credentials['token'];
                $phoneId = $credentials['phone_id'];
            } else {
                $token = SystemSetting::get('whatsapp_api_key');
                $phoneId = SystemSetting::get('whatsapp_api_phone_id');
            }

            if (!$token || !$phoneId) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp Meta API credentials (Token or Phone ID) are missing.',
                ];
            }

            $to = preg_replace('/[^0-9]/', '', $to);
            if (strlen($to) === 10) {
                $to = '91' . $to;
            }

            $url = "https://graph.facebook.com/v17.0/{$phoneId}/messages";
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode
                    ]
                ]
            ];

            try {
                $response = Http::withToken($token)->post($url, $payload);
                if ($response->successful()) {
                    return [
                        'success' => true,
                        'data' => $response->json(),
                        'message' => 'WhatsApp message sent successfully via Meta API.',
                    ];
                }
                Log::error('WhatsApp Meta API Error', ['status' => $response->status(), 'body' => $response->json()]);
                return ['success' => false, 'message' => "Meta API Error: " . $response->json('error.message', 'Unknown error')];
            } catch (\Exception $e) {
                Log::error('WhatsApp Meta API Exception', ['message' => $e->getMessage()]);
                return ['success' => false, 'message' => 'Meta API Exception: ' . $e->getMessage()];
            }
        }

        if ($provider === 'evolution') {
            // For Evolution (which doesn't require pre-approved templates), we can translate templates to standard text.
            $friendlyText = "Hello! This is an automated notification regarding your account. [Template: {$templateName}]";
            return self::sendText($to, $friendlyText);
        }

        return [
            'success' => false,
            'message' => 'WhatsApp provider is not configured for template dispatch.',
        ];
    }
}
