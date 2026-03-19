<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\SystemSetting;
use App\Services\LeadStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadStatusController extends Controller
{
    /**
     * Update lead status with validation and history tracking
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager', 'SBA', 'BA'])) {
            abort(403);
        }
        if (in_array($user->role, ['SBA', 'BA']) && (int) $lead->assigned_to !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(LeadStatusService::getStatusDefinitions()))],
            'reason' => 'nullable|string|max:500',
            'checklist' => 'nullable|array',
        ]);

        $oldStatus = $lead->status;
        $newStatus = $validated['status'];

        // Check if status requires checklist
        if (LeadStatusService::requiresChecklist($newStatus)) {
            $requiredChecklist = LeadStatusService::getChecklist($newStatus);
            $completedChecklist = $validated['checklist'] ?? [];

            // Validate all checklist items are completed
            $missing = array_diff($requiredChecklist, $completedChecklist);
            if (!empty($missing)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete all checklist items before changing status.',
                    'required_checklist' => $requiredChecklist,
                ], 422);
            }
        }

        // Enforce KYC/RPM lock for Paid Client
        if ($newStatus === 'Paid Client') {
            $kycMandatory = SystemSetting::get('kyc_mandatory', '0');
            if ($kycMandatory === '1' && (!$lead->is_kyc_completed || !$lead->is_rpm_completed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC and RPM must be completed before marking as Paid Client.',
                ], 422);
            }
        }

        // Handle NPC status - increment attempt count
        if ($newStatus === 'NPC' || $newStatus === 'Not Reachable' || $newStatus === 'Switch Off') {
            $lead->npc_attempt_count++;
            $lead->last_npc_attempt = now();

            // Auto-move to Dead Lead after 7 attempts
            if ($lead->npc_attempt_count >= 7) {
                $newStatus = 'Dead Lead';
                $validated['reason'] = ($validated['reason'] ?? '') . ' (Auto-moved after 7 NPC attempts)';
            }
        }

        $safeReason = $this->sanitizeText($validated['reason'] ?? null);

        DB::transaction(function () use ($lead, $oldStatus, $newStatus, $safeReason, $user) {
            // Update status
            $lead->status = $newStatus;
            $lead->save();

            // Add to history
            $lead->addStatusHistory($oldStatus, $newStatus, $safeReason);

            // Create activity log
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'activity_type' => 'Status Changed',
                'notes' => "Status changed from {$oldStatus} to {$newStatus}. Reason: " . ($safeReason ?? 'N/A'),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'lead' => $lead->fresh(),
        ]);
    }

    /**
     * Get status definition
     */
    public function getStatusDefinition($status)
    {
        $definition = LeadStatusService::getStatusDefinition($status);

        if (!$definition) {
            return response()->json(['error' => 'Status not found'], 404);
        }

        return response()->json($definition);
    }

    /**
     * Get all status definitions
     */
    public function getAllDefinitions()
    {
        return response()->json(LeadStatusService::getStatusDefinitions());
    }

    private function sanitizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        // Basic XSS hardening before storage
        $value = strip_tags($value);
        return $value;
    }
}
