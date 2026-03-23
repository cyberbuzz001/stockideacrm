<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->hasPermission('performance', 'view_company') && !$user->hasPermission('performance', 'view_team') && !$user->hasPermission('performance', 'view_own'))) {
            abort(403);
        }

        // Default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date',   Carbon::now()->endOfMonth()->toDateString());

        // Users: Admin/Manager sees all, SBA sees team, BA sees self
        $users = collect([]);

        if ($user->hasPermission('performance', 'view_company')) {
            $users = User::whereIn('role', ['BA', 'SBA', 'Team Leader'])->orderBy('name')->get();
        } elseif ($user->hasPermission('performance', 'view_team')) {
            $users = User::where('parent_id', $user->id)
                ->orWhere('id', $user->id)
                ->orderBy('name')->get();
        } elseif ($user->hasPermission('performance', 'view_own')) {
            $users = collect([$user]);
        } else {
            abort(403);
        }

        $selectedUserId = $request->input('user_id');

        // ── Per-agent aggregated report data ──────────────────────────────────
        $agentIds = $users->pluck('id');

        $callsByUser = LeadActivity::whereIn('user_id', $agentIds)
            ->where('activity_type', 'Call Started')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $revenueByUser = Payment::query()
            ->join('leads', 'payments.lead_id', '=', 'leads.id')
            ->where('payments.status', 'Verified')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->whereIn('leads.assigned_to', $agentIds)
            ->selectRaw('leads.assigned_to as assigned_to, SUM(payments.amount) as total')
            ->groupBy('leads.assigned_to')
            ->pluck('total', 'assigned_to');

        $conversionsByUser = Payment::query()
            ->join('leads', 'payments.lead_id', '=', 'leads.id')
            ->where('payments.status', 'Verified')
            ->whereBetween('payments.payment_date', [$startDate, $endDate])
            ->whereIn('leads.assigned_to', $agentIds)
            ->selectRaw('leads.assigned_to as assigned_to, COUNT(DISTINCT payments.lead_id) as total')
            ->groupBy('leads.assigned_to')
            ->pluck('total', 'assigned_to');

        $reportData = $users->map(function ($agent) use ($callsByUser, $revenueByUser, $conversionsByUser) {
            $calls = (int) ($callsByUser[$agent->id] ?? 0);
            $revenue = (float) ($revenueByUser[$agent->id] ?? 0);
            $conversions = (int) ($conversionsByUser[$agent->id] ?? 0);

            return (object) [
                'id'          => $agent->id,
                'name'        => $agent->name,
                'role'        => $agent->role,
                'calls'       => $calls,
                'conversions' => $conversions,
                'revenue'     => $revenue,
            ];
        });

        // ── Summary KPIs ───────────────────────────────────────────────────────
        $totalRevenue     = $reportData->sum('revenue');
        $totalConversions = $reportData->sum('conversions');
        $totalCalls       = $reportData->sum('calls');
        $topAgent         = $reportData->sortByDesc('revenue')->first();

        // ── Conversion Funnel ─────────────────────────────────────────────────
        $funnelLeadIds = Lead::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->pluck('id');

        $conversionFunnel = [
            'Total Leads'  => Lead::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'Assigned'     => Lead::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                                ->whereNotNull('assigned_to')->count(),
            'Contacted'    => LeadActivity::whereIn('lead_id', $funnelLeadIds)
                                ->where('activity_type', 'Call Started')
                                ->distinct('lead_id')->count(),
            'Free Trial'   => Lead::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                                ->where('status', 'Free Trial')->count(),
            'Paid Client'  => Lead::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                                ->where('status', 'Paid Client')->count(),
        ];

        // ── Monthly Revenue Trend (last 6 months) ─────────────────────────────
        $monthlyRevenueRaw = Payment::where('status', 'Verified')
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->get();

        $monthlyRevenue = $monthlyRevenueRaw->groupBy(function ($payment) {
            return $payment->payment_date ? $payment->payment_date->format('Y-m') : $payment->created_at->format('Y-m');
        })->map(function ($group, $key) {
            return (object) [
                'month_key' => $key,
                'month_label' => \Carbon\Carbon::parse($key . '-01')->format('M Y'),
                'total' => $group->sum('amount')
            ];
        })->sortBy('month_key')->values();

        // ── Advisory Performance Metrics ──────────────────────────────────────
        $advisoryStats = \App\Models\AdvisoryCall::select(
            'segment',
            DB::raw('count(*) as total'),
            DB::raw('sum(case when outcome = "Target Achieved" then 1 else 0 end) as target_achieved'),
            DB::raw('sum(case when outcome = "SL Hit" then 1 else 0 end) as sl_hit')
        )
            ->groupBy('segment')
            ->get()
            ->map(function ($stat) {
                $stat->hit_ratio = $stat->total > 0 ? round(($stat->target_achieved / $stat->total) * 100, 1) : 0;
                return $stat;
            });

        return view('reports.index', compact(
            'users', 'reportData', 'startDate', 'endDate', 'selectedUserId', 'advisoryStats',
            'totalRevenue', 'totalConversions', 'totalCalls', 'topAgent',
            'conversionFunnel', 'monthlyRevenue'
        ));
    }
}
