<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\Lead;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Display smart analysis dashboard with charts
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        // Determine Date Range from Filter Query Parameter
        $filter = $request->input('filter', 'month'); // Default to month
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfDay();

        if ($filter === 'today') {
            $startDate = now()->startOfDay();
        } elseif ($filter === 'week') {
            $startDate = now()->startOfWeek();
        }

        // Get all employees (exclude Admin for cleaner charts)
        $employees = User::where('role', '!=', 'Admin')
            ->with([
                'targets' => function ($q) {
                    $q->where('month_year', now()->format('Y-m'));
                }
            ])
            ->get();

        $employeeIds = $employees->pluck('id');

        $achievementByUser = Payment::query()
            ->join('leads', 'payments.lead_id', '=', 'leads.id')
            ->where('payments.status', 'Verified')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->whereIn('leads.assigned_to', $employeeIds)
            ->selectRaw('leads.assigned_to as assigned_to, SUM(payments.amount) as total')
            ->groupBy('leads.assigned_to')
            ->pluck('total', 'assigned_to');

        $callsByUser = Lead::whereIn('assigned_to', $employeeIds)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $freeTrialsByUser = Lead::whereIn('assigned_to', $employeeIds)
            ->where('status', 'Free Trial')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $activeHoursByUser = \App\Models\Attendance::whereIn('user_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('user_id, SUM(effective_hours) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        // Calculate performance metrics for each employee
        $employeeMetrics = $employees->map(function ($employee) use ($startDate, $endDate, $filter, $callsByUser, $freeTrialsByUser, $activeHoursByUser, $achievementByUser) {
            // Monthly Target is fixed regardless of filter
            $target = $employee->currentTarget()?->amount ?? 0;

            // Achievement based on date filter (computed via relationship for accuracy)
            $achievement = (float) ($achievementByUser[$employee->id] ?? 0);

            // Calls/Leads created or updated in this period
            $totalCalls = (int) ($callsByUser[$employee->id] ?? 0);

            // Free Trials in this period
            $freeTrials = (int) ($freeTrialsByUser[$employee->id] ?? 0);

            // Fetch KPI Score (Section 9/10 formula) - KPI remains monthly/overall
            $kpi = \App\Services\KPIService::calculateScore($employee);

            // Fetch Active Time (Effective Hours) from Attendance
            $activeHoursQuery = (float) ($activeHoursByUser[$employee->id] ?? 0);
                
            // Convert decimal hours (e.g., 2.5) to formatted "2h 30m"
            $hours = floor($activeHoursQuery);
            $minutes = round(($activeHoursQuery - $hours) * 60);
            $formattedActiveTime = "{$hours}h {$minutes}m";

            return [
                'name' => $employee->name,
                'role' => $employee->role,
                'target' => $target,
                'achievement' => $achievement,
                'kpi_score' => $kpi['final_score'],
                'percentage' => $target > 0 ? round(($achievement / $target) * 100, 1) : 0,
                'total_calls' => $totalCalls,
                'free_trials' => $freeTrials,
                'total_sale' => $achievement,
                'active_time' => $formattedActiveTime,
            ];
        });

        // Overall company metrics (Based on filter)
        $companyTarget = $employees->sum(function ($e) {
            return $e->currentTarget()?->amount ?? 0;
        });

        $companyAchievement = Payment::where('status', 'Verified')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // Aggressive Predictive Engine (Focus on last 7 days velocity)
        $sevenDaysAgo = now()->subDays(7)->startOfDay();
        $recentRevenue = Payment::where('status', 'Verified')
            ->whereBetween('payment_date', [$sevenDaysAgo, now()->endOfDay()])
            ->sum('amount');
        
        $dailyVelocity = $recentRevenue / 7;
        $daysRemaining = max(1, now()->diffInDays(now()->endOfMonth()));
        $projectedRevenue = $companyAchievement + ($dailyVelocity * $daysRemaining);

        return view('analytics.index', compact(
            'employeeMetrics', 
            'companyTarget', 
            'companyAchievement', 
            'projectedRevenue',
            'dailyVelocity',
            'filter'
        ));
    }

    /**
     * AI Endpoint: Run Ecosystem Audit
     */
    public function runAudit(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Gather critical data for Gemini
        $stats = [
            'total_achievement' => Payment::where('status', 'Verified')->whereMonth('payment_date', now()->month)->sum('amount'),
            'total_target' => User::where('role', '!=', 'Admin')->get()->sum(fn($u) => $u->currentTarget()?->amount ?? 0),
            'top_agents' => Lead::where('status', 'Paid Client')->whereMonth('updated_at', now()->month)->selectRaw('assigned_to, count(*) as count')->groupBy('assigned_to')->orderByDesc('count')->limit(3)->with('assignee:id,name')->get(),
            'bottlenecks' => Lead::where('status', 'Interested')->where('updated_at', '<', now()->subDays(3))->count(),
        ];

        $gemini = new \App\Services\GeminiService();
        $prompt = "Act as a Macro Sales Auditor. Analyze these CRM stats for the current month: " . json_encode($stats) . ". 
        Identify the 2 biggest operational bottlenecks and give 1 'Aggressive' strategic advice to hit the target. 
        Keep it professional, high-fidelity, and concise.";

        // We'll use a generic method since we don't have a specific 'audit' method yet
        $advice = $gemini->getObjectionAdvice(auth()->user(), $prompt);

        return response()->json(['audit' => $advice]);
    }
}
