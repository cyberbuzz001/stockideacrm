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

        $verifyToken = SystemSetting::get('whatsapp_webhook_verify_token', 'shreesvarn_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming messages from Meta Cloud API.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('WhatsApp Webhook Incoming', $payload);

        // Extract message data
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return response('OK');

        $change = $entry['changes'][0] ?? null;
        if (!$change || ($change['value']['messaging_product'] ?? '') !== 'whatsapp') return response('OK');

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
        $proof = $lead->clientProof;

        if (!$proof && $lead->status === 'Paid Client') {
             // Create proof record if missing for paid clients
             // (Ideally we create this at the moment of payment approval)
        }

        if ($proof) {
            if (str_contains($text, 'YES')) {
                $proof->update([
                    'has_confirmed_service' => true,
                    'confirmed_service_at' => now(),
                ]);
                $lead->activities()->create([
                    'action' => 'WhatsApp Proof: YES',
                    'details' => 'Client confirmed Service Started via WhatsApp.',
                ]);
            }

            if (str_contains($text, 'AGREE')) {
                $proof->update([
                    'has_agreed_terms' => true,
                    'agreed_terms_at' => now(),
                ]);
                $lead->activities()->create([
                    'action' => 'WhatsApp Proof: AGREE',
                    'details' => 'Client accepted Terms & Conditions via WhatsApp.',
                ]);
            }
        }

        // Logic for Images (Trade Proof)
        if ($message['type'] === 'image' && $proof) {
            $currentPaths = $proof->usage_proof_paths ?? [];
            $currentPaths[] = [
                'id' => $message['image']['id'],
                'type' => 'trade_screenshot',
                'received_at' => now()->toDateTimeString(),
            ];
            $proof->update(['usage_proof_paths' => $currentPaths]);
            
            $lead->activities()->create([
                'action' => 'WhatsApp Proof: Screenshot',
                'details' => 'Received trade screenshot from client via WhatsApp.',
            ]);
        }
    }
}
