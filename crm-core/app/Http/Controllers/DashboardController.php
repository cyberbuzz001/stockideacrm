<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Services\KPIService;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $period = $request->query('period', 'week');
        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            'month' => now()->startOfMonth(),
            'ytd' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };
        $endDate = now()->endOfDay();

        $role = $user->role;
        $data = [];

        if ($role === 'Admin') {
            $data = $this->getAdminDashboard($startDate, $endDate);
        } elseif ($role === 'Manager') {
            $data = $this->getManagerDashboard($user, $startDate, $endDate);
        } elseif ($role === 'SBA') {
            $data = $this->getSBADashboard($user, $startDate, $endDate);
        } else {
            // BA (Business Advisor)
            $data = $this->getBADashboard($user, $startDate, $endDate);
        }

        // Leaderboard Data (Global for all roles to see competition)
        $leaderboard = User::whereIn('role', ['BA', 'SBA'])
            ->withSum([
                'payments' => function ($q) use ($startDate, $endDate) {
                    $q->where('payments.status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate]);
                }
            ], 'amount')
            ->orderByDesc('payments_sum_amount')
            ->take(5)
            ->get();

        $data['leaderboard'] = $leaderboard;

        // Latest Advisory Calls (Global Feed)
        $data['latest_calls'] = \App\Models\AdvisoryCall::with('user')->latest()->take(5)->get();

        return view('dashboard', array_merge($data, ['role' => $role]));
    }

    private function getBADashboard(User $user, $startDate, $endDate)
    {
        // Calculate KPIs
        $kpi = KPIService::calculateScore($user);

        // Fetch pending mandatory training for alert
        $pending_training = \App\Models\TrainingModule::where(function($query) use ($user) {
            $query->where('target_team', 'All')
                  ->orWhere('target_team', $user->role);
        })
        ->whereDoesntHave('logs', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('completion_status', 'completed');
        })
        ->latest()
        ->first();

        // Calculate Target Progress
        $monthYear = now()->format('Y-m');
        $target = \App\Models\UserTarget::where('user_id', $user->id)
            ->where('month_year', $monthYear)
            ->first();

        $revenue = Payment::whereHas('lead', fn($q) => $q->where('assigned_to', $user->id))
            ->where('status', 'Verified')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $targetProgress = [
            'amount' => $target->amount ?? 0,
            'achieved' => $revenue,
            'percentage' => ($target && $target->amount > 0) ? round(($revenue / $target->amount) * 100) : 0,
        ];

        // BA Stats: Assigned (Total/Today), Calls Today, Follow-ups Today, Free Trials Active, Paid
        $leads = Lead::where('assigned_to', $user->id);

        $cacheKey = "ba_stats_{$user->id}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($leads, $user, $startDate, $endDate) {
            return [
                'assigned_total' => (clone $leads)->count(),
                'assigned_today' => (clone $leads)->whereBetween('created_at', [$startDate, $endDate])->count(),
                'calls_today' => \App\Models\LeadActivity::where('user_id', $user->id)
                    ->where('activity_type', 'Call Started')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'followups_due' => (clone $leads)->whereDate('follow_up_date', '<=', now())
                    ->whereIn('status', ['Call Back', 'Follow Up'])
                    ->count(),
                'trials_active' => (clone $leads)->where('status', 'Free Trial')
                    ->whereDate('trial_end_date', '>=', now())
                    ->count(),
                'paid_approved' => (clone $leads)->where('status', 'Paid Client')->count(),
                'revenue' => Payment::whereHas('lead', fn($q) => $q->where('assigned_to', $user->id))
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
            ];
        });

        return [
            'kpi' => $kpi,
            'target_progress' => $targetProgress,
            'stats' => $stats,
        // My Leads Screen
            'my_leads' => (clone $leads)->whereIn('status', ['Fresh', 'Call Back', 'Follow Up', 'Free Trial'])
                ->orderBy('follow_up_date', 'asc')
                ->latest()
                ->take(10)->get(),
            // Upcoming Followups (Specific List)
            'upcoming_followups' => (clone $leads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereNotNull('follow_up_date')
                ->whereDate('follow_up_date', '>=', now())
                ->orderBy('follow_up_date', 'asc')
                ->take(5)
                ->get(),
            'today_followups' => (clone $leads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)->get(),
            'free_trials' => (clone $leads)->where('status', 'Free Trial')
                ->orderBy('updated_at', 'desc')
                ->take(10)->get(),
            'pending_training' => $pending_training,
        ];
    }

    private function getSBADashboard(User $user, $startDate, $endDate)
    {
        // SBA Stats: Team Leads, Team Calls Today, Team Trials, Team Revenue
        $teamIds = $user->getAllTeamIds(); // Includes self
        $teamLeads = Lead::whereIn('assigned_to', $teamIds);
        $myOwnLeads = Lead::where('assigned_to', $user->id);

        // Fetch pending mandatory training for alert
        $pending_training = \App\Models\TrainingModule::where(function($query) use ($user) {
            $query->where('target_team', 'All')
                  ->orWhere('target_team', $user->role);
        })
        ->whereDoesntHave('logs', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('completion_status', 'completed');
        })
        ->latest()
        ->first();

        $cacheKey = "sba_stats_{$user->id}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($teamLeads, $teamIds, $startDate, $endDate) {
            return [
                'team_leads' => (clone $teamLeads)->count(),
                'team_calls_today' => \App\Models\LeadActivity::whereIn('user_id', $teamIds)
                    ->where('activity_type', 'Call Started')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'team_trials' => (clone $teamLeads)->where('status', 'Free Trial')->count(),
                'team_revenue' => Payment::whereHas('lead', fn($q) => $q->whereIn('assigned_to', $teamIds))
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
            ];
        });

        return [
            'stats' => $stats,
            'team_performance' => User::whereIn('id', $teamIds)
                ->withCount(['leadActivities as calls_today' => fn($q) => $q->where('activity_type', 'Call Started')->whereBetween('created_at', [$startDate, $endDate])])
                ->withSum(['payments as revenue' => fn($q) => $q->where('status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])], 'amount')
                ->get(),
            // Own Leads fallback
            'my_leads' => (clone $myOwnLeads)->latest()->take(10)->get(),
            'today_followups' => (clone $myOwnLeads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)->get(),
            'free_trials' => (clone $myOwnLeads)->where('status', 'Free Trial')
                ->orderBy('updated_at', 'desc')
                ->take(10)->get(),
        ];
    }

    private function getManagerDashboard(User $user, $startDate, $endDate)
    {
        // Manager Stats: Total Teams, Daily Revenue, Trials Running, Pending Payments
        $teamIds = $user->getAllTeamIds();

        $cacheKey = "manager_stats_{$user->id}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($teamIds, $startDate, $endDate) {
            return [
                'total_team_members' => count($teamIds),
                'daily_revenue' => Payment::whereHas('lead', fn($q) => $q->whereIn('assigned_to', $teamIds))
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
                'trials_running' => Lead::whereIn('assigned_to', $teamIds)
                    ->where('status', 'Free Trial')
                    ->whereDate('trial_end_date', '>=', now())
                    ->count(),
                'pending_payments' => Payment::whereHas('lead', fn($q) => $q->whereIn('assigned_to', $teamIds))
                    ->where('status', 'Pending')
                    ->count(),
            ];
        });

        return [
            'stats' => $stats,
            'agent_performance' => User::whereIn('id', $teamIds)
                ->where('role', '!=', 'Manager')
                ->withCount(['leads as active_leads'])
                ->withSum(['payments as revenue' => fn($q) => $q->where('payments.status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])], 'amount')
                ->orderBy('revenue', 'desc')
                ->get(),
            'escalations' => Lead::whereIn('assigned_to', $teamIds)
                ->where('is_escalated', true)
                ->with('assignee')
                ->take(10)
                ->get(),
            'today_followups' => Lead::whereIn('assigned_to', $teamIds)
                ->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)
                ->get(),
        ];
    }

    private function getAdminDashboard($startDate, $endDate)
    {
        // Admin Stats: Total Leads, Paid Clients, Monthly Revenue, Pending Approvals
        $cacheKey = "admin_stats_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($startDate, $endDate) {
            return [
                'total_leads' => Lead::count(),
                'paid_clients' => Lead::where('status', 'Paid Client')->count(),
                'monthly_revenue' => Payment::where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
                'pending_approvals' => Payment::where('status', 'Pending')->count(),
                'leads_this_week' => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_clients_week' => Lead::where('status', 'Paid Client')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            ];
        });

        return [
            'stats' => $stats,
            'recent_users' => User::latest()->take(5)->get(),
            'pending_payments' => Payment::where('status', 'Pending')->with(['lead', 'user'])->get(),
            'system_health' => [
                'leads_today' => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
                'active_users' => User::count(),
            ],
            'today_followups' => Lead::whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)
                ->get(),
        ];
    }
    public function leaderboardLive(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $period = $request->query('period', 'today');

        if ($period === 'month') {
            $start = now()->startOfMonth();
            $end   = now()->endOfDay();
            $label = now()->format('F Y');
        } else {
            $start = now()->startOfDay();
            $end   = now()->endOfDay();
            $label = 'Today';
        }

        $leaderboard = User::whereIn('role', ['BA', 'SBA'])
            ->withSum([
                'payments' => fn($q) => $q->where('payments.status', 'Verified')
                                          ->whereBetween('payment_date', [$start, $end])
            ], 'amount')
            ->withCount([
                'activities as calls_today' => fn($q) => $q
                    ->where('activity_type', 'Call Started')
                    ->whereBetween('created_at', [$start, $end])
            ])
            ->orderByDesc('payments_sum_amount')
            ->take(10)
            ->get(['id', 'name', 'role']);

        $max = $leaderboard->max('payments_sum_amount') ?: 1;

        return response()->json([
            'period'     => $period,
            'label'      => $label,
            'updated_at' => now()->format('H:i:s'),
            'leaderboard' => $leaderboard->map(fn($agent, $i) => [
                'rank'     => $i + 1,
                'name'     => $agent->name,
                'initials' => strtoupper(substr($agent->name, 0, 2)),
                'revenue'  => (float) ($agent->payments_sum_amount ?? 0),
                'calls'    => $agent->calls_today ?? 0,
                'pct'      => round((($agent->payments_sum_amount ?? 0) / $max) * 100),
            ])->values(),
        ]);
    }

    public function checkNotifications()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }

        $dueFollowups = \App\Models\Lead::where('assigned_to', $user->id)
            ->where('follow_up_date', '>', now())
            ->where('follow_up_date', '<', now()->addMinutes(5))
            ->get();

        return response()->json([
            'count' => $dueFollowups->count(),
            'notifications' => $dueFollowups->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'title' => 'Upcoming Follow-up!',
                    'body' => "You have a scheduled call with {$lead->name} at " . $lead->follow_up_date->format('H:i'),
                    'url' => route('leads.show', $lead->id)
                ];
            })
        ]);
    }
}
