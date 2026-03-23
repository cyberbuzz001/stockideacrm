<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Services\DataAccessLogger;

class KycRpmController extends Controller
{
    public function show(Lead $lead)
    {
        if ($lead->status !== 'Paid Client') {
            abort(404, 'KYC/RPM is only available for Paid Clients.');
        }

        $user = auth()->user();
        if (!$user->hasPermission('compliance', 'view_kyc') && (int) $lead->assigned_to !== (int) $user->id) {
             abort(403);
        }

        $lead->load(['documents', 'consents', 'compliance', 'complianceSteps']);
        
        // Log access to sensitive fields for audit (standard practice in this CRM)
        if (!empty($lead->pan_number)) {
            DataAccessLogger::log($user, $lead, 'pan_number', 'view', request());
        }
        if (!empty($lead->aadhaar_number)) {
            DataAccessLogger::log($user, $lead, 'aadhaar_number', 'view', request());
        }

        return view('kyc-rpm.show', compact('lead'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::query();

        // Narrow down to only Paid Clients for KYC/RPM
        $query->where('status', 'Paid Client');

        // For non-admin/manager users, only show their assigned leads
        if (!$user->hasPermission('compliance', 'view_kyc')) {
            $query->where('assigned_to', $user->id);
        }
        
        if ($request->status === 'pending') {
            $query->where(function($q) {
                $q->where('is_kyc_completed', false)->orWhere('is_rpm_completed', false);
            });
        }

        $leads = $query->paginate(20);

        // Calculate Compliance Score dynamically
        $leads->getCollection()->transform(function ($lead) {
            $score = 0;
            if ($lead->is_kyc_completed) $score += 30;
            if ($lead->is_rpm_completed) $score += 30;
            if (!empty($lead->pan_number)) $score += 15;
            if (!empty($lead->aadhaar_number)) $score += 15;
            if (!empty($lead->demat_id)) $score += 10;
            $lead->compliance_score = $score;
            return $lead;
        });

        return view('kyc-rpm.index', compact('leads'));
    }
}
