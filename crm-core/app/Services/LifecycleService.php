<?php

namespace App\Services;

use App\Models\Lead;

class LifecycleService
{
    const STAGE_NEW_LEAD = 1;         // Call & Qualify
    const STAGE_FREE_TRIAL = 2;       // Send Demo Calls
    const STAGE_REGISTERED = 3;       // Collect Initial Fee
    const STAGE_KYC_RPM_PENDING = 4;  // Book Expert Slot
    const STAGE_SERVICE_ACTIVE = 5;   // Collect Installments

    /**
     * Map lead status/data to one of the 5 workflow stages.
     */
    public static function getLeadStage(Lead $lead): int
    {
        $status = $lead->status;

        // Step 5: Service Active (Trading)
        if ($status === 'Trading') {
            return self::STAGE_SERVICE_ACTIVE;
        }

        // Step 4: KYC/RPM Pending (Paid but compliance incomplete)
        if ($status === 'Paid Client' || ($lead->payments_count > 0 && (!$lead->is_kyc_completed || !$lead->is_rpm_completed))) {
            return self::STAGE_KYC_RPM_PENDING;
        }

        // Step 3: Registered (Payment process started or high intent)
        if (in_array($status, ['Interested', 'Expected Payment', 'Make Payment', 'Paid Client'])) {
            // If Paid Client but we reached here, it means they might be transition to Step 4 soon
            return self::STAGE_REGISTERED;
        }

        // Step 2: Free Trial
        if ($status === 'Free Trial') {
            return self::STAGE_FREE_TRIAL;
        }

        // Step 1: New Lead (Default)
        return self::STAGE_NEW_LEAD;
    }

    /**
     * Get the countdown timer for Step 2: Free Trial.
     * Returns remaining hours or null.
     */
    public static function getTrialCountdown(Lead $lead): ?int
    {
        if ($lead->status !== 'Free Trial' || !$lead->status_changed_at) {
            return null;
        }

        $expiry = $lead->status_changed_at->addHours(48);
        $diff = now()->diffInHours($expiry, false);

        return $diff > 0 ? (int)$diff : 0;
    }

    /**
     * Get all stages metadata.
     */
    public static function getStages(): array
    {
        return [
            self::STAGE_NEW_LEAD => [
                'name' => 'New Lead',
                'action' => 'Call & Qualify',
                'description' => 'Raw prospect data assigned to agent.',
                'trigger' => 'Assigned to Agent_ID'
            ],
            self::STAGE_FREE_TRIAL => [
                'name' => 'Free Trial',
                'action' => 'Send Demo Calls',
                'description' => '1-2 day trial providing live market exposure.',
                'trigger' => '48-Hour Countdown Timer Starts'
            ],
            self::STAGE_REGISTERED => [
                'name' => 'Registered',
                'action' => 'Collect Initial Fee',
                'description' => 'Prospect pays the initial Registration Fee.',
                'trigger' => 'Update Client_Ledger with Registration Amount'
            ],
            self::STAGE_KYC_RPM_PENDING => [
                'name' => 'KYC/RPM Pending',
                'action' => 'Book Expert Slot',
                'description' => 'Document collection and risk profiling.',
                'trigger' => 'Document Upload & Risk Score Generation'
            ],
            self::STAGE_SERVICE_ACTIVE => [
                'name' => 'Service Active',
                'action' => 'Collect Installments',
                'description' => 'Active client receiving segmented calls.',
                'trigger' => 'Unlock market calls + Trigger Upsell Alerts'
            ],
        ];
    }
}
