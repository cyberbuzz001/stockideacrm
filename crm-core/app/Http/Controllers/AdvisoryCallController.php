<?php

namespace App\Http\Controllers;

use App\Models\AdvisoryCall;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdvisoryCallController extends Controller
{
    private const SEGMENTS = [
        'Stock Cash',
        'Stock Future',
        'Nifty Option',
        'Index Option',
        'Stock Option',
        'MCX',
        'All Equity'
    ];

    /**
     * Display a listing of advisory calls with segment filtering
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $segment = $request->get('segment', 'All Equity');

        $calls = AdvisoryCall::with('user')
            ->when($segment !== 'All Equity', function ($q) use ($segment) {
                $q->where('segment', $segment);
            })
            ->latest('call_date')
            ->latest('call_time')
            ->paginate(20);
            
        // Calculate Hit Ratio for the current segment
        $statsQuery = AdvisoryCall::query();
        if ($segment !== 'All Equity') {
            $statsQuery->where('segment', $segment);
        }
        
        $totalSegmentCalls = $statsQuery->count();
        $targetHits = (clone $statsQuery)->where('outcome', 'Target Achieved')->count();
        $slHits = (clone $statsQuery)->where('outcome', 'SL Hit')->count();
        $hitRatio = $totalSegmentCalls > 0 ? round(($targetHits / $totalSegmentCalls) * 100, 1) : 0;
        
        $performanceStats = (object)[
            'total' => $totalSegmentCalls,
            'hits' => $targetHits,
            'sl' => $slHits,
            'ratio' => $hitRatio
        ];

        $segments = self::SEGMENTS;

        return view('advisory-calls.index', compact('calls', 'segments', 'segment', 'performanceStats'));
    }

    /**
     * Show the form for creating a new advisory call
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('market_calls', 'publish')) {
            abort(403);
        }

        $segments = self::SEGMENTS;

        return view('advisory-calls.create', compact('segments'));
    }

    /**
     * Store a newly created advisory call
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('market_calls', 'publish')) {
            abort(403);
        }

        $validated = $request->validate([
            'segment' => ['required', 'string', Rule::in(self::SEGMENTS)],
            'call_text' => 'required|string|max:2000',
            'call_date' => 'required|date',
            'call_time' => 'required|date_format:H:i',
        ]);

        $validated['user_id'] = $user->id;
        $validated['call_text'] = $this->sanitizeText($validated['call_text']);

        AdvisoryCall::create($validated);

        return redirect()->route('advisory-calls.index')
            ->with('success', 'Market call created successfully!');
    }

    /**
     * Display the specified advisory call
     */
    public function show(AdvisoryCall $advisoryCall)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $advisoryCall->load('user');

        // Get clients who are interested in this segment
        $targetClients = collect();
        if ($advisoryCall->segment !== 'All Equity') {
            $targetClients = Lead::where('status', 'Paid Client')
                ->whereJsonContains('interest_segments', $advisoryCall->segment)
                ->with('assignee')
                ->get();
        }

        return view('advisory-calls.show', compact('advisoryCall', 'targetClients'));
    }

    /**
     * Broadcast advisory call to clients in specific segment
     */
    public function broadcast(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->hasPermission('market_calls', 'publish')) {
            abort(403);
        }

        $validated = $request->validate([
            'call_id' => 'required|exists:advisory_calls,id',
            'client_ids' => 'required|array',
            'client_ids.*' => 'exists:leads,id',
        ]);

        $call = AdvisoryCall::findOrFail($validated['call_id']);
        if (!in_array($call->segment, self::SEGMENTS, true)) {
            return back()->with('error', 'Invalid call segment.');
        }

        $clientIds = array_values(array_unique($validated['client_ids']));
        $leads = Lead::whereIn('id', $clientIds)
            ->select('id', 'assigned_to', 'name', 'status', 'interest_segments')
            ->get()
            ->keyBy('id');

        // 1. Log Activity for each Lead
        // 2. Group clients by Assigned Agent to send a single summary mail or individual mails
        DB::transaction(function () use ($clientIds, $leads, $call, $currentUser) {
            foreach ($clientIds as $clientId) {
                $lead = $leads->get($clientId);
                if (!$lead) {
                    continue;
                }

                // Only allow paid clients for the matching segment
                if ($lead->status !== 'Paid Client') {
                    continue;
                }
                if ($call->segment !== 'All Equity') {
                    $segments = is_array($lead->interest_segments) ? $lead->interest_segments : json_decode($lead->interest_segments ?? '[]', true);
                    if (!is_array($segments) || !in_array($call->segment, $segments, true)) {
                        continue;
                    }
                }

                // Log activity for the lead
                \App\Models\LeadActivity::create([
                    'lead_id' => $clientId,
                    'user_id' => $currentUser->id,
                    'activity_type' => 'Market Call Sent',
                    'notes' => "Segment: {$call->segment} | Content: " . substr($call->call_text, 0, 100),
                ]);

                // Notify Assigned Agent via Internal Mail
                if ($lead->assigned_to && $lead->assigned_to !== $currentUser->id) {
                    \App\Models\InternalMail::create([
                        'sender_id' => $currentUser->id,
                        'subject' => "Market Call Broadcast: {$call->segment}",
                        'body' => "You have a new market call to share with your client {$lead->name}.\n\n" .
                            "Call Details:\n{$call->call_text}",
                        'category' => 'Important',
                        'recipient_ids' => [(string) $lead->assigned_to],
                    ]);
                }
            }
        });

        return back()->with('success', 'Market call broadcasted to ' . count($clientIds) . ' clients and their agents!');
    }

    /**
     * Update the outcome of the advisory call
     */
    public function updateOutcome(Request $request, AdvisoryCall $advisoryCall)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('market_calls', 'publish')) {
            abort(403);
        }

        $validated = $request->validate([
            'outcome' => 'required|string|in:Target Achieved,SL Hit,Exit,Partial Exit,Partial Book,Trailing SL',
        ]);

        $advisoryCall->update($validated);

        return back()->with('success', 'Call outcome updated successfully!');
    }

    /**
     * Remove the specified advisory call
     */
    public function destroy(AdvisoryCall $advisoryCall)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission('market_calls', 'edit_delete')) {
            abort(403);
        }

        $advisoryCall->delete();

        return redirect()->route('advisory-calls.index')
            ->with('success', 'Advisory call deleted successfully!');
    }

    private function sanitizeText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        // Basic XSS hardening before storage
        return strip_tags($value);
    }
}
