<?php

namespace App\Services;

use App\Models\Lead;
use Phpml\ModelManager;
use Illuminate\Support\Facades\Log;
use Exception;

class LeadScoringService
{
    /**
     * Calculate and update the score for a specific lead.
     */
    public function updateScore(Lead $lead)
    {
        $modelPath = storage_path('app/ai/lead_scoring_model.txt');
        $score = null;

        if (file_exists($modelPath)) {
            try {
                $modelManager = new ModelManager();
                $model = $modelManager->restoreFromFile($modelPath);
                
                $activityCount = $lead->activities()->count();
                $features = self::extractFeatures($lead, $activityCount);
                
                $prediction = $model->predict($features);
                
                // Map the regression output (typically 0 to 1) to 0-100% score
                $score = min(100, max(0, intval($prediction * 100)));
                Log::info("Lead Scoring: Lead ID {$lead->id} scored via ML model. Score: {$score}");
            } catch (Exception $e) {
                Log::warning("Lead Scoring: ML scoring failed (falling back to heuristic): " . $e->getMessage());
            }
        }

        // Fallback to heuristic scoring if ML model not trained or failed
        if ($score === null) {
            $score = $this->calculateHeuristicScore($lead);
        }

        $lead->lead_score = min(100, max(0, $score));

        // Determine Win Probability
        $lead->win_probability = $lead->lead_score / 100;

        $lead->save();
        return $lead->lead_score;
    }

    /**
     * Extract features from a lead record (supports both Eloquent Model and StdClass raw DB objects).
     */
    public static function extractFeatures($lead, int $activityCount): array
    {
        $investmentCaps = [
            'under 1l' => 1, '1-3l' => 2, '3-5l' => 3, '5-10l' => 4, 'above 10l' => 5
        ];
        $expLevels = [
            'beginner' => 1, 'intermediate' => 2, 'professional' => 3
        ];
        
        $source = strtolower($lead->source ?? '');
        $sourceVal = 0;
        if (str_contains($source, 'website')) $sourceVal = 1;
        elseif (str_contains($source, 'ads')) $sourceVal = 2;
        elseif (str_contains($source, 'csv')) $sourceVal = 3;
        elseif (str_contains($source, 'text')) $sourceVal = 4;
        elseif (str_contains($source, 'ref')) $sourceVal = 5;

        $cap = strtolower($lead->investment_cap ?? '');
        $capVal = 0;
        foreach ($investmentCaps as $k => $v) {
            if (str_contains($cap, $k)) {
                $capVal = $v;
                break;
            }
        }

        $exp = strtolower($lead->experience_level ?? '');
        $expVal = 0;
        foreach ($expLevels as $k => $v) {
            if (str_contains($exp, $k)) {
                $expVal = $v;
                break;
            }
        }

        return [
            $sourceVal,
            !empty($lead->city) ? 1 : 0,
            !empty($lead->demat_status) && strtolower($lead->demat_status) !== 'no' ? 1 : 0,
            !empty($lead->is_trading) && $lead->is_trading ? 1 : 0,
            $capVal,
            $expVal,
            !empty($lead->email) ? 1 : 0,
            !empty($lead->mobile) ? 1 : 0,
            $activityCount,
        ];
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
