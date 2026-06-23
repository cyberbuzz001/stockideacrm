<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Phpml\Regression\LeastSquares;
use Phpml\ModelManager;
use App\Services\LeadScoringService;

class TrainLeadScoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:train-lead-scoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train the machine learning lead scoring model on historical data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Lead Scoring ML Model Training...');

        // 1. Gather all tables (base + shards)
        $tables = ['leads'];
        $currentYear = now()->year;
        for ($y = 2025; $y <= $currentYear; $y++) {
            $tables[] = "leads_{$y}";
        }

        $allLeads = collect();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("Fetching leads from table: {$table}");
                $leads = DB::table($table)->get();
                $allLeads = $allLeads->merge($leads);
            }
        }

        if ($allLeads->isEmpty()) {
            $this->error('No leads found in the database. Training aborted.');
            return 1;
        }

        $this->info('Total leads fetched: ' . $allLeads->count());

        // 2. Fetch all activity counts in bulk to prevent N+1 query performance issues
        $activityCounts = DB::table('lead_activities')
            ->select('lead_id', DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->pluck('count', 'lead_id')
            ->all();

        $samples = [];
        $targets = [];

        $convertedStatuses = ['Paid Client'];
        $lostStatuses = ['Not Interested', 'Junk', 'DND', 'NPC', 'Not Reachable', 'Service Expired'];

        foreach ($allLeads as $lead) {
            $status = $lead->status;
            
            // We only train on leads with a finalized conversion outcome (positive or negative)
            $isConverted = in_array($status, $convertedStatuses);
            $isLost = in_array($status, $lostStatuses);

            if (!$isConverted && !$isLost) {
                continue;
            }

            $activityCount = $activityCounts[$lead->id] ?? 0;
            $samples[] = LeadScoringService::extractFeatures($lead, $activityCount);
            $targets[] = $isConverted ? 1.0 : 0.0;
        }

        if (count($samples) < 10) {
            $this->warn('Not enough historical leads with definitive outcomes (Paid/Lost) to train ML model. Need at least 10 samples. Current count: ' . count($samples));
            $this->warn('Scoring service will continue using the rule-based heuristic fallback.');
            return 0;
        }

        $this->info('Training model on ' . count($samples) . ' historical samples...');

        try {
            // 3. Train LeastSquares model
            $regression = new LeastSquares();
            $regression->train($samples, $targets);

            // 4. Save model to storage
            $dir = storage_path('app/ai');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $modelPath = $dir . '/lead_scoring_model.txt';
            $modelManager = new ModelManager();
            $modelManager->saveToFile($regression, $modelPath);

            $this->info("Model successfully trained and saved to: {$modelPath}");
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to train or save model: ' . $e->getMessage());
            return 1;
        }
    }
}
