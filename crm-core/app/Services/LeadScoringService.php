<?php

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    /**
     * Calculate and update the score for a specific lead.
     */
    public function updateScore(Lead $lead)
    {
        $score = $this->calculateHeuristicScore($lead);

        // TODO: Future integration with Python ML Microservice
        // $score += $this->callExternalMLModel($lead);

        $lead->lead_score = min(100, max(0, $score));

        // Determine Win Probability (simple mapping for now)
        $lead->win_probability = $lead->lead_score / 100;

        $lead->save();
        return $lead->lead_score;
    }

    /**
     * Rule-based scoring (V1 AI).
     */
    private function calculateHeuristicScore(Lead $lead)
    {
        $points = 0;

        // 1. Data Completeness (Max 35)
        if ($lead->email)
            $points += 10;
        if ($lead->mobile)
            $points += 10;
        if ($lead->location)
            $points += 15;

        // 2. Engagement Intensity (Max 30)
        $activityCount = $lead->activities()->count();
        $points += min(30, $activityCount * 5); // 5 points per call/action, cap at 30

        // 3. Positive Signals (Sentiment/Status)
        $statusPoints = match ($lead->status) {
            'Interested' => 40,
            'Free Trial' => 30,
            'Call Back' => 15,
            'Follow Up' => 20,
            'Cold Lead' => 5,
            default => 0
        };
        $points += $statusPoints;

        // 4. Negative Signals
        if ($lead->status === 'Not Interested' || $lead->status === 'DND') {
            $points = 0; // Immediate disqualification
        }

        return $points;
    }
}
