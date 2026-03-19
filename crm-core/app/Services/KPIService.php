<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Models\Lead;
use App\Models\LeadActivity;

class KPIService
{
    public static function calculateScore(User $user)
    {
        // Target settings (could be dynamic later)
        $targetCalls = 50; // Daily target
        $targetRevenue = 50000; // Monthly target example

        // 1. Call Score (30%)
        // Formula: (Calls Completed / Target Calls) * 100
        $callsToday = LeadActivity::where('user_id', $user->id)
            ->where('activity_type', 'Call Started')
            ->whereDate('created_at', now())
            ->count();

        $callScore = ($callsToday / $targetCalls) * 100;
        $callScore = min($callScore, 100); // Section 8 doesn't say cap, but standard logic implies 100 max or maybe over performance? Let's keep it raw for now but maybe capped for "Score" usually implies 0-100.
        // Re-reading formula: "Final Score = (0.3 * Call Score) + ..." implies weighted average.

        // 2. Conversion Data
        // Formula: (Paid Clients / Trials Given) * 100
        $trialsGiven = Lead::where('assigned_to', $user->id)
            ->where('status', 'Free Trial') // Or leads that HAVE been trial? 
            // "Trials Given" implies total trials ever or this month? 
            // Let's assume current month for KPI.
            ->whereMonth('created_at', now()->month)
            ->count();

        $paidClients = Lead::where('assigned_to', $user->id)
            ->where('status', 'Paid Client')
            ->whereMonth('created_at', now()->month)
            ->count();

        $conversionScore = $trialsGiven > 0 ? ($paidClients / $trialsGiven) * 100 : 0;

        // 3. Revenue Score (30%)
        // Formula: (Approved Revenue / Target Revenue) * 100
        $approvedRevenue = Payment::whereHas('lead', fn($q) => $q->where('assigned_to', $user->id))
            ->where('status', 'Verified')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $revenueScore = ($approvedRevenue / $targetRevenue) * 100;

        // 4. Final Score
        // Final Score = (0.3 * Call Score) + (0.4 * Conversion Score) + (0.3 * Revenue Score)
        $finalScore = (0.3 * $callScore) + (0.4 * $conversionScore) + (0.3 * $revenueScore);

        return [
            'call_score' => round($callScore, 1),
            'conversion_score' => round($conversionScore, 1),
            'revenue_score' => round($revenueScore, 1),
            'final_score' => round($finalScore, 1),
            'raw' => [
                'calls' => $callsToday,
                'trials' => $trialsGiven,
                'paid' => $paidClients,
                'revenue' => $approvedRevenue
            ]
        ];
    }
}
