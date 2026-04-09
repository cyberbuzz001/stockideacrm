<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Storage;

class ProofGeneratorService
{
    /**
     * Generate a 'Safety Dossier' (Compliance Proof Object)
     */
    public static function generateSafetyDossier(Lead $lead)
    {
        $progress = $lead->getComplianceProgress();
        
        $interactions = $lead->activities()
            ->where('action', 'like', '%Compliance Verified%')
            ->orWhere('action', 'like', '%WhatsApp Proof%')
            ->orderBy('created_at', 'asc')
            ->get(['action', 'details', 'created_at']);

        $proofs = $lead->clientProof;
        
        $dossier = [
            'lead_name' => $lead->name,
            'lead_mobile' => $lead->mobile,
            'status' => $lead->status,
            'compliance_score' => $progress['percentage'] . '%',
            'is_fully_protected' => $progress['is_safe'],
            'milestones' => $progress['steps'],
            'audit_logs' => $interactions->map(function($log) {
                return [
                    'event' => $log->action,
                    'note' => $log->details,
                    'timestamp' => $log->created_at->toDateTimeString()
                ];
            }),
            'raw_proofs' => [
                'has_yes' => $proofs->has_confirmed_service ?? false,
                'yes_timestamp' => $proofs->confirmed_service_at ?? null,
                'has_agree' => $proofs->has_agreed_terms ?? false,
                'agree_timestamp' => $proofs->agreed_terms_at ?? null,
                'screenshots_count' => count($proofs->usage_proof_paths ?? [])
            ]
        ];

        return $dossier;
    }

    /**
     * Conceptual PDF Export
     */
    public static function exportPdf(Lead $lead)
    {
        $data = self::generateSafetyDossier($lead);
        
        // In a production environment, we would use dompdf or snappy
        // For now, we return a JSON representation that can be printed or saved
        return $data;
    }
}
