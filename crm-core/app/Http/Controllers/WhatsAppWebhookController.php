<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\ClientProof;
use App\Models\WhatsAppMessageLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle verification from Meta.
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = SystemSetting::get('whatsapp_webhook_verify_token');

        if (!$verifyToken) {
            Log::warning('WhatsApp webhook verify rejected: whatsapp_webhook_verify_token is not configured.');
            return response('Forbidden', 403);
        }

        if ($mode === 'subscribe' && hash_equals($verifyToken, (string) $token)) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming messages from Meta Cloud API or Evolution API.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp Webhook Incoming', $payload);

        // Detect Evolution API webhook format
        if (isset($payload['event']) && isset($payload['data'])) {
            if (!$this->verifyEvolutionRequest($request, $payload)) {
                Log::warning('WhatsApp webhook rejected: invalid or missing Evolution API key.');
                return response('Forbidden', 403);
            }

            $event = strtolower($payload['event']);
            if ($event !== 'messages.upsert' && $event !== 'messages_upsert') {
                return response('OK');
            }

            $data = $payload['data'];
            $remoteJid = $data['key']['remoteJid'] ?? '';
            $from = explode('@', $remoteJid)[0];
            if (!$from) return response('OK');

            $messageId = $data['key']['id'] ?? uniqid();
            
            // Extract text message content
            $text = $data['message']['conversation'] ?? '';
            if (empty($text) && isset($data['message']['extendedTextMessage']['text'])) {
                $text = $data['message']['extendedTextMessage']['text'];
            }

            $isImage = isset($data['message']['imageMessage']);
            $mediaType = $data['messageType'] ?? 'text';
            if ($isImage) {
                $mediaType = 'image';
            }

            // Find Lead
            $lead = $this->findLeadByNumber($from);

            // Log inbound message
            WhatsAppMessageLog::create([
                'direction' => 'inbound',
                'from_number' => $from,
                'to_number' => $payload['sender'] ?? 'N/A', // Instance sender number
                'message_id' => $messageId,
                'content' => $text ?: ($isImage ? 'Media: Image' : 'Unknown Content'),
                'media_type' => $mediaType,
                'status' => 'received',
                'lead_id' => $lead ? $lead->id : null,
            ]);

            if ($lead) {
                $this->processKeywordLogic($lead, $text, $data['message']);
            }

            return response('OK');
        }

        // Fallback to Meta Cloud API webhook format
        if (!$this->verifyMetaSignature($request)) {
            Log::warning('WhatsApp webhook rejected: invalid or missing Meta X-Hub-Signature-256.');
            return response('Forbidden', 403);
        }

        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return response('OK');

        $change = $entry['changes'][0] ?? null;
        if (!$change || ($change['value']['messaging_product'] ?? '') !== 'whatsapp') return response('OK');

        // Check for Status Updates (sent, delivered, read, failed)
        $statusUpdate = $change['value']['statuses'][0] ?? null;
        if ($statusUpdate) {
            $msgId = $statusUpdate['id'];
            $status = $statusUpdate['status']; // e.g. 'sent', 'delivered', 'read', 'failed'
            
            // Try to find the outbound log and update its status
            // Note: Our outbound log doesn't store the Meta message ID yet, but we can match by to_number and recent time if we wanted to.
            // Wait, we don't have message_id saved in outbound. It's okay, we'll update based on the status field we have if we add messageId handling later.
            Log::info("WhatsApp Status Update: {$msgId} - {$status}");
            return response('OK');
        }
        $message = $change['value']['messages'][0] ?? null;
        if (!$message) return response('OK');

        $from = $message['from']; // 91XXXXXXXXXX
        $text = $message['text']['body'] ?? '';
        $media = $message['image'] ?? $message['document'] ?? null;
        $messageId = $message['id'];

        // Find Lead
        $lead = $this->findLeadByNumber($from);
        
        // Log inbound message
        WhatsAppMessageLog::create([
            'direction' => 'inbound',
            'from_number' => $from,
            'to_number' => $change['value']['metadata']['display_phone_number'] ?? 'N/A',
            'message_id' => $messageId,
            'content' => $text ?: ($media ? 'Media: ' . ($media['id'] ?? 'Unknown') : 'Unknown Content'),
            'media_type' => $message['type'] ?? 'text',
            'status' => 'received',
            'lead_id' => $lead ? $lead->id : null,
        ]);

        if ($lead) {
            $this->processKeywordLogic($lead, $text, $message);
        }

        return response('OK');
    }

    /**
     * Evolution API includes the instance token as `apikey` in the webhook
     * payload body (and optionally as an `apikey` header). Verify it against
     * the same key configured for outbound sends (whatsapp_api_key) before
     * trusting anything in the payload.
     */
    private function verifyEvolutionRequest(Request $request, array $payload): bool
    {
        $expected = SystemSetting::get('whatsapp_api_key');
        if (!$expected) {
            return false;
        }

        $provided = $payload['apikey'] ?? $request->header('apikey');

        return is_string($provided) && hash_equals($expected, $provided);
    }

    /**
     * Meta signs every webhook POST body with HMAC-SHA256 using the app
     * secret, sent as `X-Hub-Signature-256: sha256=<hex>`.
     * https://developers.facebook.com/docs/graph-api/webhooks/getting-started#validate-payloads
     */
    private function verifyMetaSignature(Request $request): bool
    {
        $appSecret = SystemSetting::get('whatsapp_meta_app_secret');
        if (!$appSecret) {
            return false;
        }

        $signatureHeader = (string) $request->header('X-Hub-Signature-256', '');
        if (!str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        return hash_equals($expected, $signatureHeader);
    }

    private function findLeadByNumber($number)
    {
        // Clean number digits
        $clean = preg_replace('/[^0-9]/', '', $number);
        
        // Try looking for 10-digit match at the end
        if (strlen($clean) >= 10) {
            $short = substr($clean, -10);
            return Lead::where('mobile', 'like', "%$short%")->orderBy('id', 'desc')->first();
        }
        
        return Lead::where('mobile', $clean)->first();
    }

    private function processKeywordLogic(Lead $lead, $text, $message)
    {
        $text = strtoupper(trim($text));
        
        // Use existing ClientProof if available, but pivot to LeadComplianceSteps for Step tracking
        $proof = $lead->clientProof;

        // Step 2: SERVICE ACTIVATION (YES)
        if (str_contains($text, 'YES SERVICE STARTED') || ($text === 'YES')) {
            $this->completeStep($lead, 'step_2_activation');
            if ($proof) {
                $proof->update(['has_confirmed_service' => true, 'confirmed_service_at' => now()]);
            }
        }

        // Step 3: TERMS ACCEPTANCE (AGREE)
        if (str_contains($text, 'AGREE')) {
            $this->completeStep($lead, 'step_3_terms');
            if ($proof) {
                $proof->update(['has_agreed_terms' => true, 'agreed_terms_at' => now()]);
            }
        }

        // Step 8: SERVICE COMPLETION (SERVICE COMPLETED)
        if (str_contains($text, 'SERVICE COMPLETED')) {
            $this->completeStep($lead, 'step_8_completion');
        }

        // Logic for Images (Step 5: TRADE PROOF)
        $isImage = (isset($message['type']) && $message['type'] === 'image') || isset($message['imageMessage']);
        if ($isImage) {
            $this->completeStep($lead, 'step_5_usage');
            
            if ($proof) {
                $currentPaths = $proof->usage_proof_paths ?? [];
                $imageId = $message['image']['id'] ?? $message['imageMessage']['key']['id'] ?? uniqid();
                $currentPaths[] = [
                    'id' => $imageId,
                    'type' => 'trade_screenshot',
                    'received_at' => now()->toDateTimeString(),
                ];
                $proof->update(['usage_proof_paths' => $currentPaths]);
            }
        }
    }

    private function completeStep(Lead $lead, string $stepKey)
    {
        $lead->complianceSteps()->updateOrCreate(
            ['step_key' => $stepKey],
            [
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => null, // Automated
            ]
        );

        $lead->activities()->create([
            'action' => 'Compliance Verified: ' . $stepKey,
            'details' => "System automatically verified compliance step [$stepKey] via WhatsApp callback.",
        ]);
    }
}
