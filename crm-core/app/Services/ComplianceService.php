<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadCompliance;
use App\Models\LeadComplianceStep;

class ComplianceService
{
    private const DEFAULT_STEPS = [
        'kyc_document',
        'kyc_address',
        'rpm_form',
    ];

    public function ensureCompliance(Lead $lead): LeadCompliance
    {
        $compliance = LeadCompliance::firstOrCreate(
            ['lead_id' => $lead->id],
            ['kyc_status' => 'pending', 'rpm_status' => 'pending']
        );

        foreach (self::DEFAULT_STEPS as $step) {
            LeadComplianceStep::firstOrCreate(
                ['lead_id' => $lead->id, 'step_key' => $step],
                ['status' => 'pending']
            );
        }

        return $compliance;
    }

    public function completeStep(Lead $lead, string $stepKey, int $userId): void
    {
        $this->ensureCompliance($lead);

        $step = LeadComplianceStep::where('lead_id', $lead->id)
            ->where('step_key', $stepKey)
            ->firstOrFail();

        $step->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId,
        ]);

        $this->recalculate($lead);
    }

    public function setExpiry(Lead $lead, ?string $kycExpiry, ?string $rpmExpiry): void
    {
        $compliance = $this->ensureCompliance($lead);
        $compliance->update([
            'kyc_expires_at' => $kycExpiry,
            'rpm_expires_at' => $rpmExpiry,
        ]);

        $this->recalculate($lead);
    }

    public function recalculate(Lead $lead): void
    {
        $compliance = $this->ensureCompliance($lead);

        $steps = LeadComplianceStep::where('lead_id', $lead->id)->get();
        $kycComplete = $steps->whereIn('step_key', ['kyc_document', 'kyc_address'])
            ->every(fn($s) => $s->status === 'completed');
        $rpmComplete = $steps->where('step_key', 'rpm_form')
            ->every(fn($s) => $s->status === 'completed');

        $kycExpired = $compliance->kyc_expires_at && $compliance->kyc_expires_at->isPast();
        $rpmExpired = $compliance->rpm_expires_at && $compliance->rpm_expires_at->isPast();

        $compliance->kyc_status = $kycExpired ? 'expired' : ($kycComplete ? 'completed' : 'pending');
        $compliance->rpm_status = $rpmExpired ? 'expired' : ($rpmComplete ? 'completed' : 'pending');
        if ($compliance->kyc_status === 'completed' && !$compliance->kyc_verified_at) {
            $compliance->kyc_verified_at = now();
        }
        if ($compliance->rpm_status === 'completed' && !$compliance->rpm_verified_at) {
            $compliance->rpm_verified_at = now();
        }
        $compliance->save();

        $lead->update([
            'is_kyc_completed' => $compliance->kyc_status === 'completed',
            'is_rpm_completed' => $compliance->rpm_status === 'completed',
        ]);
    }
}
