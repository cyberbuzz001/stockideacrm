<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\Payment;
use App\Notifications\ComplianceAlertNotification;

class ComplianceCheckCommand extends Command
{
    protected $signature = 'compliance:check';
    protected $description = 'Scan for leads missing WhatsApp confirmations after payment';

    public function handle()
    {
        $this->info('Starting Compliance Scan...');

        // Find payments verified > 30 mins ago where lead is 'Paid Client'
        $threshold = now()->subMinutes(30);

        $pendingLeads = Lead::where('status', 'Paid Client')
            ->whereHas('payments', function($q) use ($threshold) {
                $q->where('status', 'Verified')->where('updated_at', '<', $threshold);
            })
            ->get();

        $alertCount = 0;
        foreach ($pendingLeads as $lead) {
            $progress = $lead->getComplianceProgress();
            
            // Check Step 2 (Activation) and Step 3 (Terms)
            $step2 = collect($progress['steps'])->where('key', 'step_2_activation')->first();
            $step3 = collect($progress['steps'])->where('key', 'step_3_terms')->first();

            if (($step2 && !$step2['is_completed']) || ($step3 && !$step3['is_completed'])) {
                if ($lead->assigned_to && $lead->assignee) {
                    $type = !$step3['is_completed'] ? 'LEGAL RISK' : 'FOLLOW-UP REQUIRED';
                    $lead->assignee->notify(new ComplianceAlertNotification($lead, $type));
                    $alertCount++;
                }
            }
        }

        $this->info("Compliance check complete. Alerts sent: {$alertCount}");
    }
}
