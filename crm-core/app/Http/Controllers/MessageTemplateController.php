<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource (Admin View).
     */
    public function index()
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $templates = MessageTemplate::latest()->get();
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['created_by'] = auth()->id();

        MessageTemplate::create($validated);

        return back()->with('success', 'Template created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|required_if:type,email|string|max:255',
            'body' => 'required|string',
        ]);

        $messageTemplate->update($validated);

        return back()->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MessageTemplate $messageTemplate)
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $messageTemplate->delete();

        return back()->with('success', 'Template deleted successfully.');
    }

    /**
     * JSON list for agents to use in Lead Detail.
     */
    public function list(Request $request)
    {
        $type = $request->query('type', 'sms');
        $templates = MessageTemplate::where('type', $type)
            ->latest()
            ->get(['id', 'name', 'subject', 'body', 'language', 'meta_id']);

        return response()->json($templates);
    }

    /**
     * Sync WhatsApp templates from Meta API.
     */
    public function syncMetaTemplates()
    {
        if (!auth()->user() || auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $wabaId = \App\Models\SystemSetting::get('whatsapp_business_account_id');
        $token = \App\Models\SystemSetting::get('whatsapp_api_key');

        if (!$wabaId || !$token) {
            return response()->json(['success' => false, 'message' => 'WABA ID or Meta Token not configured in settings.']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://graph.facebook.com/v20.0/{$wabaId}/message_templates");

            if ($response->failed()) {
                return response()->json(['success' => false, 'message' => 'Meta API Error: ' . $response->json('error.message', 'Unknown error')]);
            }

            $templates = $response->json('data', []);
            $count = 0;

            foreach ($templates as $tpl) {
                if (($tpl['status'] ?? '') !== 'APPROVED') continue;

                $bodyText = '';
                foreach ($tpl['components'] ?? [] as $component) {
                    if ($component['type'] === 'BODY') {
                        $bodyText = $component['text'] ?? '';
                        break;
                    }
                }

                if (empty($bodyText)) continue;

                MessageTemplate::updateOrCreate(
                    [
                        'name' => $tpl['name'],
                        'language' => $tpl['language'],
                    ],
                    [
                        'type' => 'whatsapp',
                        'meta_id' => $tpl['id'],
                        'category' => $tpl['category'] ?? null,
                        'body' => $bodyText,
                        'created_by' => auth()->id(),
                    ]
                );
                $count++;
            }

            return response()->json(['success' => true, 'count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
