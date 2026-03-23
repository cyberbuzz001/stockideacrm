<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $query = Lead::where('status', 'Paid Client');

        // Role Based Visibility
        // ═══════════════════════════════════════════
        // RBAC FETCHING LOGIC
        // ═══════════════════════════════════════════
        if ($user->hasPermission('clients', 'view_all')) {
            // No filter
        } elseif ($user->hasPermission('clients', 'view_team')) {
            $teamIds = $user->getAllTeamIds();
            $query->whereIn('assigned_to', $teamIds);
        } elseif ($user->hasPermission('clients', 'view_own')) {
            $query->where('assigned_to', $user->id);
        } else {
            $query->where('assigned_to', $user->id);
        }

        // Eager load payments to show the active subscription plan on the index page
        $query->with(['assignee', 'payments' => function ($q) {
            $q->where('status', 'Verified')->latest('payment_date');
        }]);

        // Filtering for renewals (Section 12)
        if ($request->filter === 'expiring') {
            $query->where('renewal_date', '<=', now()->addDays(7))
                ->where('renewal_date', '>=', now());
        }

        $clients = $query->latest('service_start_date')->paginate(15);

        return view('clients.index', compact('clients'));
    }

    public function retention(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $query = Lead::where('status', 'Paid Client');
        if ($user->hasPermission('clients', 'view_all')) {
            // No filter
        } elseif ($user->hasPermission('clients', 'view_team')) {
            $teamIds = $user->getAllTeamIds();
            $query->whereIn('assigned_to', $teamIds);
        } elseif ($user->hasPermission('clients', 'view_own')) {
            $query->where('assigned_to', $user->id);
        } else {
            $query->where('assigned_to', $user->id);
        }

        $totalActive = (clone $query)->where('renewal_date', '>=', now())->count();
        $expired = (clone $query)->where('renewal_date', '<', now())->count();
        
        $expiringIn30Days = (clone $query)
            ->withCount('activities')
            ->whereBetween('renewal_date', [now(), now()->addDays(30)])
            ->orderBy('renewal_date', 'asc')
            ->get()
            ->map(function ($client) {
                // AI Churn Prediction: Less activities = Higher Churn Risk. Base risk 85%
                $risk = max(12, min(98, 85 - ($client->activities_count * 8)));
                $client->churn_risk_score = $risk;
                
                if ($risk >= 70) $client->churn_risk_level = 'High Risk';
                elseif ($risk >= 40) $client->churn_risk_level = 'Medium Risk';
                else $client->churn_risk_level = 'Low Risk';
                
                return $client;
            });
            
        $recentlyExpired = (clone $query)
            ->whereBetween('renewal_date', [now()->subDays(60), now()->subSeconds(1)])
            ->orderBy('renewal_date', 'desc')
            ->get();
            
        $renewalRate = ($totalActive + $expired) > 0 
            ? round(($totalActive / ($totalActive + $expired)) * 100, 1) 
            : 0;

        return view('clients.retention', compact(
            'totalActive', 'expired', 'expiringIn30Days', 'recentlyExpired', 'renewalRate'
        ));
    }

    public function show(Lead $client)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        $this->authorizeClient($client, $user);

        $client->load([
            'activities.user',
            'payments' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);

        $ltv = $client->payments()->where('status', 'Verified')->sum('amount');

        return view('clients.show', compact('client', 'ltv'));
    }

    public function updateRenewal(Request $request, Lead $client)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        $this->authorizeClient($client, $user);

        $request->validate([
            'renewal_date' => 'required|date'
        ]);

        $client->update([
            'renewal_date' => $request->renewal_date
        ]);

        $client->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'Renewal Updated',
            'notes' => 'Agent updated the renewal date to ' . $request->renewal_date
        ]);

        return redirect()->back()->with('success', 'Renewal date updated.');
    }

    public function storePayment(Request $request, Lead $client)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        $this->authorizeClient($client, $user);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required|string|max:50',
            'subscription_plan' => 'required|string|max:100',
        ]);

        $client->payments()->create([
            'amount' => $request->amount,
            'payment_date' => now(),
            'payment_mode' => $this->sanitizeText($request->payment_mode),
            'subscription_plan' => $this->sanitizeText($request->subscription_plan),
            'status' => 'Pending',
            'entered_by' => $user->id,
        ]);

        $client->activities()->create([
            'user_id' => $user->id,
            'activity_type' => 'New Payment Logged',
            'notes' => 'Agent logged a new payment of INR ' . number_format((float) $request->amount, 2) . ' (' . $this->sanitizeText($request->subscription_plan) . ')',
        ]);

        return redirect()->back()->with('success', 'New payment logged and awaiting approval.');
    }

    public function edit(Lead $client)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        $this->authorizeClient($client, $user);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Lead $client)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        $this->authorizeClient($client, $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'investment_cap' => 'nullable|string',
            'experience_level' => 'nullable|string',
            'demat_id' => 'nullable|string',
            'pan_number' => 'nullable|string',
        ]);

        $client->update($this->sanitizeArray($validated));

        return redirect()->route('clients.show', $client)->with('success', 'Client profile updated.');
    }

    private function authorizeClient(Lead $client, $user): void
    {
        if ($user->role === 'BA' && (int) $client->assigned_to !== (int) $user->id) {
            abort(403);
        }
        if (in_array($user->role, ['SBA', 'Manager'], true)) {
            $teamIds = $user->getAllTeamIds();
            if (!in_array((int) $client->assigned_to, $teamIds, true)) {
                abort(403);
            }
        }
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
