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

        $period = $request->query('period', 'month');
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
        $data['live_market_call'] = \App\Models\AdvisoryCall::with('user')->latest()->first();

        // Calculate Target Progress (Role-Specific)
        $monthYear = now()->format('Y-m');
        if ($role === 'Admin' || $role === 'Manager') {
            $targetAmount = 2500000; 
            $achieved = Payment::where('status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $target_progress = [
                'amount' => $targetAmount,
                'achieved' => $achieved,
                'percentage' => round(($achieved / $targetAmount) * 100),
            ];
        } else {
            $target = \App\Models\UserTarget::where('user_id', $user->id)->where('month_year', $monthYear)->first();
            $achieved = Payment::where('user_id', $user->id)
                ->where('status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
            $target_progress = [
                'amount' => $target->amount ?? 100000, 
                'achieved' => $achieved,
                'percentage' => ($target && $target->amount > 0) ? round(($achieved / $target->amount) * 100) : 0,
            ];
        }

        return view('dashboard', array_merge($data, [
            'role' => $role,
            'leaderboard' => $leaderboard,
            'target_progress' => $target_progress,
            'latest_calls' => $data['latest_calls'] ?? [],
        ]));
    }

    private function getBADashboard(User $user, $startDate, $endDate)
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

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

        $revenue = Payment::where('user_id', $user->id)
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

        $cacheKey = "ba_stats_v2_{$user->id}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($leads, $user, $startDate, $endDate, $today, $yesterday, $yesterdayEnd) {
            
            $callsToday = \App\Models\LeadActivity::where('user_id', $user->id)
                ->where('activity_type', 'Call Started')
                ->whereDate('created_at', $today)
                ->count();

            // Approximate duration: count * 3 mins (since we don't have end_call specifically timed in all records)
            $callSecondsToday = $callsToday * 180; 

            // Yesterday comparison
            $callsYesterday = \App\Models\LeadActivity::where('user_id', $user->id)
                ->where('activity_type', 'Call Started')
                ->whereBetween('created_at', [$yesterday, $yesterdayEnd])
                ->count();

            return [
                'assigned_total' => (clone $leads)->count(),
                'assigned_today' => (clone $leads)->whereBetween('created_at', [$startDate, $endDate])->count(),
                'calls_today'    => $callsToday,
                'calls_yesterday' => $callsYesterday,
                'call_duration_seconds' => $callSecondsToday,
                'followups_due'  => (clone $leads)->whereDate('follow_up_date', '<=', now())
                    ->whereIn('status', ['Call Back', 'Follow Up'])
                    ->count(),
                'expiring_soon'  => Lead::where('assigned_to', $user->id)
                    ->where('status', 'Paid Client')
                    ->whereNotNull('renewal_date')
                    ->whereBetween('renewal_date', [now(), now()->addDays(3)])
                    ->orderBy('renewal_date')
                    ->get(),
                'trials_active'  => (clone $leads)->where('status', 'Free Trial')
                    ->whereDate('trial_end_date', '>=', now())
                    ->count(),
                'paid_approved'  => (clone $leads)->where('status', 'Paid Client')->count(),
                'revenue'        => Payment::where('user_id', $user->id)
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
            ];
        });

        // ─── Chart Data: Call Volume (7 days) ───
        $callVolume = Cache::remember("ba_chart_calls_{$user->id}", now()->addMinutes(3), function () use ($user) {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $dayStart = now()->subDays($i)->startOfDay();
                $dayEnd   = now()->subDays($i)->endOfDay();
                $days[] = [
                    'date'  => now()->subDays($i)->format('d M'),
                    'count' => \App\Models\LeadActivity::where('user_id', $user->id)
                        ->where('activity_type', 'Call Started')
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->count(),
                ];
            }
            return $days;
        });

        // ─── Chart Data: Personal Conversion Funnel ───
        $conversionFunnel = Cache::remember("ba_chart_funnel_{$user->id}", now()->addMinutes(3), function () use ($user) {
            return Lead::where('assigned_to', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();
        });

        // Running Paid Clients List
        $paidClientsList = (clone $leads)->where('status', 'Paid Client')
            ->with(['payments' => fn($q) => $q->where('status', 'Verified')])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return [
            'kpi'              => $kpi,
            'target_progress'  => $targetProgress,
            'stats'            => $stats,
            'call_volume'      => $callVolume,
            'conversion_funnel'=> $conversionFunnel,
            'paid_clients_list'=> $paidClientsList,
            // My Priority Queue
            'my_leads'         => clone $leads
                ->whereNotIn('status', ['Paid Client', 'Lost', 'Junk', 'Not Interested'])
                ->orderByRaw("
                    CASE 
                        WHEN status IN ('Call Back', 'Follow Up') AND follow_up_date <= CURRENT_TIMESTAMP THEN 1
                        WHEN status IN ('Fresh', 'Cold Lead') AND created_at >= CURDATE() THEN 0
                        WHEN status IN ('Fresh', 'Cold Lead') THEN 2
                        WHEN status = 'Free Trial' THEN 3
                        ELSE 4
                    END ASC
                ")
                ->orderByRaw('COALESCE(lead_score, 0) DESC')
                ->orderBy('created_at', 'desc')
                ->take(15)->get(),
            'upcoming_followups'=> (clone $leads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereNotNull('follow_up_date')
                ->whereDate('follow_up_date', '>=', now())
                ->orderBy('follow_up_date', 'asc')
                ->take(5)
                ->get(),
            'today_followups'  => (clone $leads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)->get(),
            'free_trials'      => (clone $leads)->where('status', 'Free Trial')
                ->orderBy('updated_at', 'desc')
                ->take(10)->get(),
            'pending_training' => $pending_training,
        ];
    }

    private function getSBADashboard(User $user, $startDate, $endDate)
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

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

        $cacheKey = "sba_stats_v2_{$user->id}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($teamLeads, $teamIds, $startDate, $endDate, $today, $yesterday, $yesterdayEnd) {
            
            $teamCallsToday = \App\Models\LeadActivity::whereIn('user_id', $teamIds)
                ->where('activity_type', 'Call Started')
                ->whereDate('created_at', $today)
                ->count();

            $teamCallsYesterday = \App\Models\LeadActivity::whereIn('user_id', $teamIds)
                ->where('activity_type', 'Call Started')
                ->whereBetween('created_at', [$yesterday, $yesterdayEnd])
                ->count();

            return [
                'team_leads'           => (clone $teamLeads)->count(),
                'team_calls_today'     => $teamCallsToday,
                'team_calls_yesterday' => $teamCallsYesterday,
                'team_trials'          => (clone $teamLeads)->where('status', 'Free Trial')->count(),
                'team_revenue'         => Payment::whereIn('user_id', $teamIds)
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
            ];
        });

        // ─── Chart Data: Team Calling Activity (7 days) ───
        $teamCallVolume = Cache::remember("sba_chart_team_calls_{$user->id}", now()->addMinutes(10), function () use ($teamIds) {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $dayStart = now()->subDays($i)->startOfDay();
                $dayEnd   = now()->subDays($i)->endOfDay();
                $days[] = [
                    'date'  => now()->subDays($i)->format('d M'),
                    'count' => \App\Models\LeadActivity::whereIn('user_id', $teamIds)
                        ->where('activity_type', 'Call Started')
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->count(),
                ];
            }
            return $days;
        });

        // ─── Chart Data: Team Revenue Trend (last 30 days) ───
        $teamRevenueTrend = Cache::remember("sba_chart_team_revenue_{$user->id}", now()->addMinutes(10), function () use ($teamIds) {
            return Payment::whereIn('user_id', $teamIds)
                ->where('status', 'Verified')
                ->whereDate('payment_date', '>=', now()->subDays(30))
                ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
        });

        return [
            'stats'            => $stats,
            'team_calls_volume'=> $teamCallVolume,
            'team_revenue_trend'=> $teamRevenueTrend,
            'team_performance' => User::whereIn('id', $teamIds)
                ->withCount(['activities as calls_today' => fn($q) => $q->where('activity_type', 'Call Started')->whereBetween('created_at', [$startDate, $endDate])])
                ->withSum(['payments as revenue' => fn($q) => $q->where('payments.status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])], 'amount')
                ->get(),
            // My Priority Queue
            'my_leads'         => clone $myOwnLeads
                ->whereNotIn('status', ['Paid Client', 'Lost', 'Junk', 'Not Interested'])
                ->orderByRaw("
                    CASE 
                        WHEN status IN ('Call Back', 'Follow Up') AND follow_up_date <= CURRENT_TIMESTAMP THEN 1
                        WHEN status IN ('Fresh', 'Cold Lead') AND created_at >= CURDATE() THEN 0
                        WHEN status IN ('Fresh', 'Cold Lead') THEN 2
                        WHEN status = 'Free Trial' THEN 3
                        ELSE 4
                    END ASC
                ")
                ->orderByRaw('COALESCE(lead_score, 0) DESC')
                ->orderBy('created_at', 'desc')
                ->take(15)->get(),
            'today_followups'  => (clone $myOwnLeads)->whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)->get(),
            'free_trials'      => (clone $myOwnLeads)->where('status', 'Free Trial')
                ->orderBy('updated_at', 'desc')
                ->take(10)->get(),
            'pending_training' => $pending_training,
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
                'daily_revenue' => Payment::whereIn('user_id', $teamIds)
                    ->where('status', 'Verified')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
                'trials_running' => Lead::whereIn('assigned_to', $teamIds)
                    ->where('status', 'Free Trial')
                    ->whereDate('trial_end_date', '>=', now())
                    ->count(),
                'pending_payments' => Payment::whereIn('user_id', $teamIds)
                    ->where('status', 'Pending')
                    ->count(),
            ];
        });

        return [
            'stats' => $stats,
            'pending_payments' => Payment::whereIn('user_id', $teamIds)
                ->where('status', 'Pending')
                ->with(['lead', 'user'])
                ->get(),
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
            'my_leads' => collect(),
        ];
    }

    private function getAdminDashboard($startDate, $endDate)
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        // ─── Core Stats (cached 5 min) ───
        $cacheKey = "admin_stats_v2_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($startDate, $endDate, $today, $yesterday, $yesterdayEnd) {

            // Today's metrics
            $todayTrials = Lead::where('status', 'Free Trial')->whereDate('updated_at', $today)->count();
            $todayCalls  = \App\Models\LeadActivity::where('activity_type', 'Call Started')->whereDate('created_at', $today)->count();
            $todayRevenue = Payment::where('status', 'Verified')->whereDate('payment_date', $today)->sum('amount');
            $activeAgents = \App\Models\Attendance::whereDate('date', $today)
                ->where('last_activity_at', '>=', now()->subMinutes(5))
                ->distinct('user_id')->count('user_id');
            $paidClients = Lead::where('status', 'Paid Client')->count();

            // Yesterday's metrics (for trend arrows)
            $yesterdayTrials  = Lead::where('status', 'Free Trial')->whereDate('updated_at', $yesterday)->count();
            $yesterdayCalls   = \App\Models\LeadActivity::where('activity_type', 'Call Started')->whereBetween('created_at', [$yesterday, $yesterdayEnd])->count();
            $yesterdayRevenue = Payment::where('status', 'Verified')->whereDate('payment_date', $yesterday)->sum('amount');

            return [
                'total_leads'        => Lead::count(),
                'paid_clients'       => $paidClients,
                'monthly_revenue'    => Payment::where('status', 'Verified')->whereBetween('payment_date', [$startDate, $endDate])->sum('amount'),
                'pending_approvals'  => Payment::where('status', 'Pending')->count(),
                'leads_this_week'    => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_clients_week'   => Lead::where('status', 'Paid Client')->whereBetween('updated_at', [$startDate, $endDate])->count(),
                // ── NEW: Today's KPI metrics ──
                'today_trials'       => $todayTrials,
                'today_calls'        => $todayCalls,
                'today_revenue'      => $todayRevenue,
                'active_agents'      => $activeAgents,
                // ── NEW: Yesterday comparisons ──
                'yesterday_trials'   => $yesterdayTrials,
                'yesterday_calls'    => $yesterdayCalls,
                'yesterday_revenue'  => $yesterdayRevenue,
            ];
        });

        // ─── Chart Data: Revenue Trend (30 days) ───
        $revenueTrend = Cache::remember('admin_chart_revenue_30d', now()->addMinutes(5), function () {
            return Payment::where('status', 'Verified')
                ->whereDate('payment_date', '>=', now()->subDays(30))
                ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();
        });

        // ─── Chart Data: Calls vs Conversions (7 days) ───
        $callsVsConversions = Cache::remember('admin_chart_calls_conv_7d', now()->addMinutes(5), function () {
            $days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayStart = now()->subDays($i)->startOfDay();
                $dayEnd   = now()->subDays($i)->endOfDay();
                $days[] = [
                    'date'        => now()->subDays($i)->format('d M'),
                    'calls'       => \App\Models\LeadActivity::where('activity_type', 'Call Started')->whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                    'conversions' => Lead::where('status', 'Paid Client')->whereBetween('updated_at', [$dayStart, $dayEnd])->count(),
                ];
            }
            return $days;
        });

        // ─── Chart Data: Lead Status Distribution ───
        $leadDistribution = Cache::remember('admin_chart_lead_dist', now()->addMinutes(5), function () {
            return Lead::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->orderByDesc('total')
                ->pluck('total', 'status')
                ->toArray();
        });

        // ─── Table: Today's New Free Trial Leads ───
        $todayTrialLeads = Lead::where('status', 'Free Trial')
            ->whereDate('updated_at', $today)
            ->with('assignee:id,name')
            ->orderByDesc('updated_at')
            ->take(15)
            ->get(['id', 'name', 'mobile', 'status', 'assigned_to', 'updated_at']);

        // ─── Table: Top Revenue Agents Today ───
        $topRevenueAgents = User::whereIn('role', ['BA', 'SBA'])
            ->withSum(['payments as revenue_today' => fn($q) => $q->where('payments.status', 'Verified')->whereDate('payment_date', $today)], 'amount')
            ->withCount(['activities as calls_today' => fn($q) => $q->where('activity_type', 'Call Started')->whereDate('created_at', $today)])
            ->having('revenue_today', '>', 0)
            ->orderByDesc('revenue_today')
            ->take(10)
            ->get(['id', 'name', 'role']);

        // ─── Table: Active Agents Real-Time Status ───
        $agentStatus = User::whereIn('role', ['BA', 'SBA'])
            ->where('is_active', true)
            ->withCount(['activities as calls_today' => fn($q) => $q->where('activity_type', 'Call Started')->whereDate('created_at', $today)])
            ->with(['leads' => fn($q) => $q->select('id', 'assigned_to')->whereNotIn('status', ['Not Interested', 'DND', 'Junk'])])
            ->get(['id', 'name', 'role']);

        // Add last activity from attendance
        $lastActivities = \App\Models\Attendance::whereDate('date', $today)
            ->whereIn('user_id', $agentStatus->pluck('id'))
            ->pluck('last_activity_at', 'user_id');

        foreach ($agentStatus as $agent) {
            $agent->last_active = $lastActivities[$agent->id] ?? null;
            $agent->active_leads_count = $agent->leads->count();
        }

        return [
            'stats'              => $stats,
            'recent_users'       => User::latest()->take(5)->get(),
            'pending_payments'   => Payment::where('status', 'Pending')->with(['lead', 'user'])->get(),
            'system_health'      => [
                'leads_today'  => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
                'active_users' => User::count(),
            ],
            'today_followups'    => Lead::whereIn('status', ['Call Back', 'Follow Up'])
                ->whereDate('follow_up_date', '<=', now())
                ->orderBy('follow_up_date', 'desc')
                ->take(10)
                ->get(),
            'my_leads'           => collect(),
            // ── NEW data sets ──
            'revenue_trend'      => $revenueTrend,
            'calls_vs_conv'      => $callsVsConversions,
            'lead_distribution'  => $leadDistribution,
            'today_trial_leads'  => $todayTrialLeads,
            'top_revenue_agents' => $topRevenueAgents,
            'agent_status'       => $agentStatus,
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
