<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\SystemSetting;
use App\Services\LeadScoringService;
use App\Services\LeadStatusService;
use App\Services\DataAccessLogger;
use App\Services\ComplianceService;
use App\Models\ClientConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    protected $scorer;
    protected $compliance;

    public function __construct(LeadScoringService $scorer, ComplianceService $compliance)
    {
        $this->scorer = $scorer;
        $this->compliance = $compliance;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $query = Lead::query();

        $userRole = $user->role;
        if ($userRole === 'Admin') {
            // Admin sees all leads
        } elseif ($userRole === 'SBA' || $userRole === 'Manager') {
            $teamIds = $user->getAllTeamIds();
            $query->whereIn('assigned_to', $teamIds);
        } else {
            $query->where('assigned_to', $user->id);
        }

        $activeTab = $request->query('tab', 'new');
        $manualStatus = $request->query('status');
        $isSearching = $request->filled('search') || $request->filled('search_query');

        if (!$isSearching) {
            if ($activeTab === 'new' && !$manualStatus) {
                $query->where('status', 'Cold Lead');
                if ($userRole === 'Admin') {
                    $query->whereNull('assigned_to');
                }
            } elseif ($activeTab === 'followup' && !$manualStatus) {
                $query->whereIn('status', ['Call Back', 'Follow Up', 'Interested']);
            } elseif ($activeTab === 'trial' && !$manualStatus) {
                $query->where('status', 'Free Trial')->where(function ($q) {
                    $q->whereNull('trial_end_date')->orWhere('trial_end_date', '>', now());
                });
            } elseif ($activeTab === 'paid' && !$manualStatus) {
                $query->where('status', 'Paid Client')->where(function ($q) {
                    $q->whereNull('service_expired_at')->orWhere('service_expired_at', '>', now());
                });
            } elseif ($activeTab === 'expired' && !$manualStatus) {
                $query->where(function ($q) {
                    $q->where('status', 'Service Expired')
                        ->orWhere('service_expired_at', '<=', now())
                        ->orWhere('trial_end_date', '<=', now());
                });
            } elseif ($activeTab === 'assigned') {
                $query->whereNotNull('assigned_to');
            } elseif ($activeTab === 'unassigned') {
                $query->whereNull('assigned_to');
            } elseif ($activeTab === 'disposed' && !$manualStatus) {
                $query->whereIn('status', ['Not Picked', 'Switch Off', 'Not Interested', 'DND', 'NPC', 'Not Reachable']);
            }
        }

        if ($request->filter === 'overdue') {
            $query->where('follow_up_date', '<', now())->whereIn('status', ['Call Back', 'Follow Up']);
        }

        if ($manualStatus) {
            $query->where('status', $manualStatus);
        }

        if ($request->source) {
            $query->where('source', $request->source);
        }

        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->search_query) {
            $search = trim($request->search_query);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->has('ai_sort')) {
            $sortDirection = $request->ai_sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('lead_score', $sortDirection);
        } else {
            if ($activeTab === 'new' && !$isSearching && !$manualStatus) {
                $query->orderBy('lead_score', 'desc');
            } else {
                $query->orderBy('updated_at', 'desc');
            }
        }

        $assignedCount = 0;
        $unassignedCount = 0;
        if ($userRole === 'Admin') {
            $assignedCount = Lead::whereNotNull('assigned_to')->count();
            $unassignedCount = Lead::whereNull('assigned_to')->count();
        }

        $leads = $query->with('assignee')->paginate(15)->withQueryString();
        return view('leads.index', compact('leads', 'activeTab', 'assignedCount', 'unassignedCount'));
    }

    public function fetchLeads()
    {
        $user = auth()->user();
        if (!$user || $user->role === 'Admin') {
            return back()->with('error', 'Admins do not need to fetch leads.');
        }

        $weight = max(1, intval($user->lead_weight ?? 1));
        $limit = 5 * $weight;

        $leadIds = Lead::where('status', 'Cold Lead')
            ->whereNull('assigned_to')
            ->orderBy('lead_score', 'desc')
            ->limit($limit)
            ->pluck('id')
            ->all();

        if (empty($leadIds)) {
            return back()->with('error', 'No new leads available to fetch.');
        }

        DB::transaction(function () use ($leadIds, $user) {
            Lead::whereIn('id', $leadIds)
                ->whereNull('assigned_to')
                ->update([
                    'assigned_to' => $user->id,
                ]);
        });

        $assignedLeads = Lead::whereIn('id', $leadIds)
            ->where('assigned_to', $user->id)
            ->get();

        foreach ($assignedLeads as $lead) {
            $lead->addStatusHistory('Unassigned', 'Cold Lead', 'Auto-fetched by agent');
        }

        return redirect()->route('leads.index', ['tab' => 'new'])
            ->with('success', count($assignedLeads) . ' new leads assigned to you successfully!');
    }

    public function updateRichDetails(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'follow_up_notes' => 'nullable|string',
            'conversion_notes' => 'nullable|string',
            'service_expired_at' => 'nullable|date',
        ]);

        $lead->update($this->sanitizeArray($validated));

        return back()->with('success', 'Lead details updated successfully.');
    }

    public function show(Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $lead->load(['activities.user', 'payments', 'assignee', 'messages.user', 'documents', 'consents', 'compliance', 'complianceSteps']);
        $this->compliance->ensureCompliance($lead);

        // Log access to sensitive fields (PAN/Aadhaar) for audit
        if (!empty($lead->pan_number)) {
            DataAccessLogger::log($user, $lead, 'pan_number', 'view', request());
        }
        if (!empty($lead->aadhaar_number)) {
            DataAccessLogger::log($user, $lead, 'aadhaar_number', 'view', request());
        }

        return view('leads.show', compact('lead'));
    }

    public function completeComplianceStep(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);
        if (!in_array($user->role, ['Admin', 'Manager'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'step_key' => 'required|string|max:50',
        ]);

        $this->compliance->completeStep($lead, $validated['step_key'], $user->id);
        return back()->with('success', 'Compliance step marked completed.');
    }

    public function updateComplianceExpiry(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);
        if (!in_array($user->role, ['Admin', 'Manager'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'kyc_expires_at' => 'nullable|date',
            'rpm_expires_at' => 'nullable|date',
        ]);

        $this->compliance->setExpiry($lead, $validated['kyc_expires_at'] ?? null, $validated['rpm_expires_at'] ?? null);
        return back()->with('success', 'Compliance expiry updated.');
    }

    public function grantConsent(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);
        if (!in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'channel' => 'required|string|max:32',
            'purpose' => 'required|string|max:255',
            'purpose_note' => 'nullable|string|max:255',
        ]);

        ClientConsent::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'channel' => $validated['channel'],
            'purpose' => $validated['purpose'],
            'status' => 'granted',
            'consented_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'metadata' => $validated['purpose_note'] ? ['note' => $validated['purpose_note']] : null,
        ]);

        return back()->with('success', 'Consent recorded.');
    }

    public function revokeConsent(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);
        if (!in_array($user->role, ['Admin', 'Manager'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'consent_id' => 'required|integer|exists:client_consents,id',
        ]);

        $consent = ClientConsent::where('lead_id', $lead->id)->findOrFail($validated['consent_id']);
        $consent->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        return back()->with('success', 'Consent revoked.');
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'demat_status' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $validated = $this->sanitizeArray($validated);

        $existingLead = Lead::where('mobile', $validated['mobile'])->first();
        if ($existingLead) {
            $existingLead->update(['last_seen_at' => now()]);
            $existingLead->activities()->create([
                'user_id' => $user->id,
                'activity_type' => 'Duplicate Attempt',
                'notes' => 'Duplicate entry detected for mobile ' . $validated['mobile'] . '. Existing lead updated instead of creating new entry.',
            ]);

            return redirect()->route('leads.show', $existingLead)
                ->with('warning', 'Duplicate detected. Lead with mobile ' . $validated['mobile'] . ' already exists (ID: ' . $existingLead->id . ').');
        }

        $lead = Lead::create(array_merge($validated, [
            'status' => 'Cold Lead',
            'assigned_by' => $user->id
        ]));

        $this->scorer->updateScore($lead);

        return redirect()->route('leads.index')->with('success', 'Lead created and AI scored!');
    }

    public function assign(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'SBA'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'agent_id' => 'required|exists:users,id'
        ]);

        if ($user->role === 'SBA') {
            $teamIds = $user->getAllTeamIds();
            if (!in_array($request->agent_id, $teamIds, true)) {
                return back()->with('error', 'You can only assign leads to your team members.');
            }
            $unauthorizedCount = Lead::whereIn('id', $request->lead_ids)
                ->whereNotNull('assigned_to')
                ->whereNotIn('assigned_to', $teamIds)
                ->count();
            if ($unauthorizedCount > 0) {
                return back()->with('error', 'You can only assign leads within your team or unassigned leads.');
            }
        }

        Lead::whereIn('id', $request->lead_ids)->update([
            'assigned_to' => $request->agent_id,
            'assigned_by' => $user->id
        ]);

        $agent = User::find($request->agent_id);

        foreach ($request->lead_ids as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $lead->activities()->create([
                    'user_id' => $user->id,
                    'activity_type' => 'Lead Assigned',
                    'notes' => 'Lead assigned to agent: ' . ($agent->name ?? 'Agent')
                ]);
            }
        }

        return redirect()->back()->with('success', count($request->lead_ids) . ' leads assigned to ' . $agent->name);
    }

    public function bulkStatus(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'SBA'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'bulk_status' => ['required', 'string', Rule::in(array_keys(LeadStatusService::getStatusDefinitions()))]
        ]);

        $status = $request->bulk_status;

        if ($user->role === 'SBA') {
            $teamIds = $user->getAllTeamIds();
            $unauthorizedCount = Lead::whereIn('id', $request->lead_ids)
                ->whereNotIn('assigned_to', $teamIds)
                ->count();
            if ($unauthorizedCount > 0) {
                return back()->with('error', 'You can only change status for leads assigned to your team.');
            }
        }

        if ($status === 'Paid Client') {
            $kycMandatory = SystemSetting::get('kyc_mandatory', '0');
            if ($kycMandatory === '1') {
                $missingCount = Lead::whereIn('id', $request->lead_ids)
                    ->where(function ($q) {
                        $q->where('is_kyc_completed', false)->orWhere('is_rpm_completed', false);
                    })
                    ->count();
                if ($missingCount > 0) {
                    return back()->with('error', 'KYC and RPM must be completed before marking as Paid Client.');
                }
            }
        }

        Lead::whereIn('id', $request->lead_ids)->update([
            'status' => $status
        ]);

        foreach ($request->lead_ids as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $lead->activities()->create([
                    'user_id' => $user->id,
                    'activity_type' => 'Status Updated',
                    'notes' => 'Agent performed a bulk update changing status to: ' . $status
                ]);
            }
        }

        return redirect()->back()->with('success', count($request->lead_ids) . ' leads status updated to ' . $status);
    }

    public function startCall(Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $lead->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'Call Started',
            'notes' => 'Agent started a manual call dialing.'
        ]);
        return response()->json(['success' => true]);
    }

    public function quickLog(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
            'follow_up_date' => 'nullable|date',
        ]);

        if (!empty($validated['follow_up_date'])) {
            $lead->update(['follow_up_date' => $validated['follow_up_date']]);
        }

        $lead->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'Quick Note',
            'notes' => $this->sanitizeText($validated['notes']),
        ]);

        return back()->with('success', 'Quick call note added.');
    }

    public function storeActivity(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(LeadStatusService::getStatusDefinitions()))],
            'notes' => 'nullable|string|max:2000',
            'follow_up_date' => 'nullable|date',
            'trial_end_date' => 'nullable|date',
            'amount' => 'nullable|numeric',
            'payment_mode' => 'nullable|string|max:50',
            'follow_up_notes' => 'nullable|string|max:2000',
            'conversion_notes' => 'nullable|string|max:2000',
            'name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'alt_mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'pan_number' => 'nullable|string|max:20',
            'aadhaar_number' => 'nullable|string|max:20',
            'demat_id' => 'nullable|string|max:50',
            'investment_cap' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
        ]);

        if ($validated['status'] === 'Paid Client') {
            $kycMandatory = SystemSetting::get('kyc_mandatory', '0');
            if ($kycMandatory === '1' && (!$lead->is_kyc_completed || !$lead->is_rpm_completed)) {
                return back()->with('error', 'KYC and RPM must be completed before marking as Paid Client.')->withInput();
            }

            if (empty($lead->pan_number) && empty($validated['pan_number'])) {
                return back()->with('error', 'PAN Number is mandatory for Paid Onboarding. Please fill KYC details.')->withInput();
            }
            if (empty($lead->demat_id) && empty($validated['demat_id'])) {
                return back()->with('error', 'Demat ID is mandatory for Paid Onboarding. Please fill KYC details.')->withInput();
            }
        }

        $lead->update($this->sanitizeArray([
            'status' => $validated['status'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'trial_end_date' => $validated['trial_end_date'] ?? null,
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
            'conversion_notes' => $validated['conversion_notes'] ?? null,
            'name' => $validated['name'] ?? $lead->name,
            'city' => $validated['city'] ?? $lead->city,
            'alt_mobile' => $validated['alt_mobile'] ?? $lead->alt_mobile,
            'address' => $validated['address'] ?? $lead->address,
            'pan_number' => $validated['pan_number'] ?? $lead->pan_number,
            'aadhaar_number' => $validated['aadhaar_number'] ?? $lead->aadhaar_number,
            'demat_id' => $validated['demat_id'] ?? $lead->demat_id,
            'investment_cap' => $validated['investment_cap'] ?? $lead->investment_cap,
            'experience_level' => $validated['experience_level'] ?? $lead->experience_level,
        ]));

        if ($validated['status'] === 'NPC') {
            $lead->increment('npc_count');
            $lead->update(['last_npc_at' => now()]);

            if (empty($validated['follow_up_date'])) {
                $lead->update(['follow_up_date' => now()->addDay()]);
            }

            $npcLimit = (int) SystemSetting::get('npc_recycle_limit', 5);
            if ($lead->npc_count >= $npcLimit) {
                $lead->update([
                    'assigned_to' => null,
                    'status' => 'Cold Lead',
                    'npc_count' => 0,
                ]);
                $lead->activities()->create([
                    'user_id' => $user->id,
                    'activity_type' => 'Auto-Recycled',
                    'notes' => 'Lead returned to Fresh Pool after consecutive NPC attempts.',
                ]);
            }
        }

        $lead->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'Call Outcome: ' . $validated['status'],
            'notes' => $this->sanitizeText($validated['notes'] ?? ''),
        ]);

        if ($validated['status'] === 'Make Payment' || $validated['status'] === 'Paid Client') {
            if ($validated['status'] === 'Make Payment') {
                $lead->payments()->create([
                    'amount' => $validated['amount'] ?? 0,
                    'payment_date' => now(),
                    'payment_mode' => $this->sanitizeText($validated['payment_mode'] ?? 'Other'),
                    'status' => 'Pending',
                    'entered_by' => $user->id,
                    'remarks' => $this->sanitizeText($validated['notes'] ?? ''),
                ]);
            }

            session()->flash('deal_won', true);
        }

        $this->scorer->updateScore($lead);

        return redirect()->route('leads.index', ['tab' => $request->active_tab ?? 'new'])
            ->with('success', 'Response logged successfully.');
    }

    public function edit(Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'demat_status' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $lead->update($this->sanitizeArray($validated));

        return redirect()->route('leads.show', $lead)->with('success', 'Lead information updated.');
    }

    public function import(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA'])) {
            abort(403);
        }

        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'txt'], true)) {
                        $fail('The uploaded file must be a CSV or TXT file.');
                    }
                }
            ],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) {
                continue;
            }

            Lead::create([
                'name' => $this->sanitizeText($row[0]),
                'city' => isset($row[3]) ? $this->sanitizeText($row[3]) : null,
                'status' => 'Cold Lead',
                'lead_score' => 10,
                'source' => 'Bulk Import'
            ]);
        }

        fclose($handle);
        return redirect()->route('leads.index')->with('success', 'Leads imported successfully.');
    }

    public function export(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        $query = Lead::query();
        $user = auth()->user();
        $maskEnabled = (bool) SystemSetting::get('data_masking_enabled', '0');
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->source) {
            $query->where('source', $request->source);
        }
        if ($request->search) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $leads = $query->get();
        $fileName = 'leads_export_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Mobile', 'Email', 'Status', 'Location', 'Source', 'Demat Status', 'AI Score', 'Created At'];

        $callback = function () use ($leads, $columns, $user, $maskEnabled) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($leads as $lead) {
                if (!empty($lead->pan_number)) {
                    DataAccessLogger::log($user, $lead, 'pan_number', 'export', request(), ['export' => 'leads']);
                }
                if (!empty($lead->aadhaar_number)) {
                    DataAccessLogger::log($user, $lead, 'aadhaar_number', 'export', request(), ['export' => 'leads']);
                }
                $mobile = $lead->mobile;
                $email = $lead->email;
                if ($maskEnabled && $user->role !== 'Admin') {
                    $mobile = $mobile ? ('XXXXXX' . substr($mobile, -4)) : null;
                    $email = $email ? preg_replace('/^(.).*(.@.*)$/', '$1*****$2', $email) : null;
                }
                fputcsv($file, [
                    $lead->name,
                    $mobile,
                    $email,
                    $lead->status,
                    $lead->location,
                    $lead->source,
                    $lead->demat_status,
                    $lead->lead_score,
                    $lead->created_at
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeMessage(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $lead->messages()->create([
            'user_id' => $user->id,
            'message' => $this->sanitizeText($validated['message']),
        ]);

        return back()->with('success', 'Message posted.');
    }

    public function bulkReassign(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'SBA'])) {
            abort(403);
        }

        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($user->role === 'SBA') {
            $teamIds = $user->getAllTeamIds();
            if (!in_array($request->user_id, $teamIds)) {
                return back()->with('error', 'You can only reassign leads to your team members.');
            }
            $unauthorizedCount = Lead::whereIn('id', $request->lead_ids)
                ->whereNotIn('assigned_to', $teamIds)
                ->count();
            if ($unauthorizedCount > 0) {
                return back()->with('error', 'You can only reassign leads within your team.');
            }
        }

        Lead::whereIn('id', $request->lead_ids)->update([
            'assigned_to' => $request->user_id,
            'assigned_by' => $user->id
        ]);

        return back()->with('success', count($request->lead_ids) . ' leads reassigned successfully.');
    }

    public function autoDistribute(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'SBA'])) {
            abort(403);
        }

        $request->validate([
            'lead_ids' => 'required|array',
            'lead_ids.*' => 'exists:leads,id',
            'agent_ids' => 'required|array',
            'agent_ids.*' => 'exists:users,id',
        ]);

        if ($user->role === 'SBA') {
            $teamIds = $user->getAllTeamIds();
            foreach ($request->agent_ids as $agentId) {
                if (!in_array($agentId, $teamIds)) {
                    return back()->with('error', 'You can only distribute leads to your team members.');
                }
            }
            $unauthorizedCount = Lead::whereIn('id', $request->lead_ids)
                ->whereNotIn('assigned_to', $teamIds)
                ->count();
            if ($unauthorizedCount > 0) {
                return back()->with('error', 'You can only distribute leads within your team.');
            }
        }

        $leadIds = $request->lead_ids;
        $agentIds = $request->agent_ids;

        $agents = User::whereIn('id', $agentIds)->get(['id', 'name', 'lead_weight'])->keyBy('id');

        $weightedQueue = [];
        foreach ($agentIds as $agentId) {
            $agent = $agents->get($agentId);
            $weight = max(1, intval($agent->lead_weight ?? 1));
            for ($i = 0; $i < $weight; $i++) {
                $weightedQueue[] = $agentId;
            }
        }

        $numSlots = count($weightedQueue);
        if ($numSlots === 0) {
            return back()->with('error', 'No agents available for distribution.');
        }

        foreach ($leadIds as $index => $leadId) {
            $agentId = $weightedQueue[$index % $numSlots];

            Lead::where('id', $leadId)->update([
                'assigned_to' => $agentId,
                'assigned_by' => $user->id
            ]);

            $lead = Lead::find($leadId);
            if ($lead) {
                $lead->activities()->create([
                    'user_id' => $user->id,
                    'activity_type' => 'Auto-Distributed',
                    'notes' => 'Lead auto-assigned via weighted round-robin to ' . ($agents[$agentId]->name ?? 'Agent'),
                ]);
            }
        }

        return back()->with('success', count($leadIds) . ' leads distributed among ' . count($agentIds) . ' agents (weighted round-robin).');
    }

    public function bulkTextImport(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA'])) {
            abort(403);
        }

        $validated = $request->validate([
            'bulk_text' => 'required|string',
            'format' => 'required|in:name_mobile,mobile_only,name_mobile_email',
        ]);

        $lines = explode("\n", $validated['bulk_text']);
        $imported = 0;
        $duplicates = 0;
        $errors = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                $parts = preg_split('/[,\t|]/', $line);
                $parts = array_map('trim', $parts);

                $leadData = [
                    'source' => 'Bulk Text Import',
                    'assigned_by' => $user->id,
                    'status' => 'Cold Lead',
                ];

                if ($validated['format'] === 'mobile_only') {
                    $mobile = $parts[0] ?? null;
                    $leadData['name'] = 'Lead ' . substr((string) $mobile, -4);
                    $leadData['mobile'] = $mobile;
                } elseif ($validated['format'] === 'name_mobile') {
                    $leadData['name'] = $parts[0] ?? 'N/A';
                    $leadData['mobile'] = $parts[1] ?? null;
                } elseif ($validated['format'] === 'name_mobile_email') {
                    $leadData['name'] = $parts[0] ?? 'N/A';
                    $leadData['mobile'] = $parts[1] ?? null;
                    $leadData['email'] = $parts[2] ?? null;
                }

                if (empty($leadData['mobile'])) {
                    $errors[] = 'Line ' . ($lineNumber + 1) . ': Missing mobile number';
                    continue;
                }

                if (Lead::where('mobile', $leadData['mobile'])->exists()) {
                    $duplicates++;
                    continue;
                }

                $lead = Lead::create($this->sanitizeArray($leadData));
                $this->scorer->updateScore($lead);
                $imported++;

            } catch (\Exception $e) {
                $errors[] = 'Line ' . ($lineNumber + 1) . ': ' . $e->getMessage();
            }
        }

        $message = 'Import complete. ' . $imported . ' leads added';
        if ($duplicates > 0) {
            $message .= ', ' . $duplicates . ' duplicates skipped';
        }
        if (count($errors) > 0) {
            $message .= ', ' . count($errors) . ' errors';
        }

        return redirect()->route('leads.index')->with('success', $message);
    }

    public function saveNotes(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $validated = $request->validate([
            'internal_notes' => 'nullable|string|max:10000',
        ]);

        $lead->update([
            'internal_notes' => $this->sanitizeText($validated['internal_notes'] ?? '')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes saved successfully'
        ]);
    }

    public function escalate(Lead $lead)
    {
        $user = auth()->user();
        $this->authorizeLead($lead, $user);

        $isEscalated = !$lead->is_escalated;

        $lead->update([
            'is_escalated' => $isEscalated,
            'escalated_at' => $isEscalated ? now() : null,
            'sentiment_label' => $isEscalated ? 'Critical Attention' : $lead->sentiment_label
        ]);

        $lead->activities()->create([
            'user_id' => $user->id,
            'activity_type' => $isEscalated ? 'Manual Escalation' : 'De-escalated',
            'notes' => $isEscalated
                ? 'Lead manually flagged for critical attention by ' . $user->name
                : 'Lead de-escalated by ' . $user->name
        ]);

        return back()->with('success', $isEscalated ? 'Lead escalated successfully.' : 'Lead de-escalated.');
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'integer|exists:leads,id',
        ]);

        $leads = Lead::whereIn('id', $request->lead_ids)->get();
        $deletedCount = 0;

        foreach ($leads as $lead) {
            DB::table('system_activities')->insert([
                'user_id' => $user->id,
                'activity_type' => 'Bulk Delete',
                'description' => 'Permanently deleted lead: ' . $lead->name . ' (Mobile: ' . $lead->mobile . ', Status: ' . $lead->status . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $lead->delete();
            $deletedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' lead(s) permanently deleted.',
            'deleted_count' => $deletedCount,
        ]);
    }

    private function authorizeLead(Lead $lead, $user): void
    {
        if (!$user) {
            abort(403);
        }
        if ($user->role === 'Admin') {
            return;
        }
        if ($user->role === 'BA') {
            if ((int) $lead->assigned_to !== (int) $user->id) {
                abort(403);
            }
            return;
        }
        if (in_array($user->role, ['SBA', 'Manager'], true)) {
            $teamIds = $user->getAllTeamIds();
            if (!in_array((int) $lead->assigned_to, $teamIds, true)) {
                abort(403);
            }
            return;
        }
        abort(403);
    }

    private function sanitizeText(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        return strip_tags($value);
    }

    private function sanitizeArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = $this->sanitizeText($value);
            }
        }
        return $input;
    }
}
