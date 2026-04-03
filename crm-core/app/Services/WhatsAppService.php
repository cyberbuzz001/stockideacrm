<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a template message via Meta Cloud API.
     *
     * @param string $to Phone number with country code (e.g. 91...)
     * @param string $templateName Name of the approved Meta WhatsApp template
     * @param string $languageCode Language code for the template (default: en)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function sendTemplate(string $to, string $templateName, string $languageCode = 'en')
    {
        $provider = SystemSetting::get('whatsapp_provider', 'none');

        if ($provider !== 'meta') {
            return [
                'success' => false,
                'message' => 'WhatsApp provider is not set to Meta Cloud API in Admin Control Center.',
            ];
        }

        $token = SystemSetting::get('whatsapp_api_key');
        $phoneId = SystemSetting::get('whatsapp_api_phone_id');

        if (!$token || !$phoneId) {
            return [
                'success' => false,
                'message' => 'WhatsApp Meta API credentials (Token or Phone ID) are missing.',
            ];
        }

        // Clean digits and ensure 91 prefix for India
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

            Log::error('WhatsApp Meta API Error', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            $errorMsg = $response->json('error.message', 'Unknown API error');
            
            return [
                'success' => false,
                'message' => "Meta API Error: " . $errorMsg,
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp Meta API Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred while communicating with Meta API: ' . $e->getMessage(),
            ];
        }
    }
}
