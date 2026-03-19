<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Models\DataAccessLogger;

class KycRpmController extends Controller
{
    public function show(Lead $lead)
    {
        $user = auth()->user();
        if ($user->role !== 'Admin' && $user->role !== 'Manager' && $lead->assigned_to !== $user->id) {
             // Basic check, LeadController has authorizeLead but let's be safe
             // Or better yet, reuse authorizeLead if accessible.
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

        // For non-admin users, only show their assigned leads
        if (!in_array($user->role, ['Admin', 'Manager', 'Compliance'])) {
            $query->where('assigned_to', $user->id);
        }
        
        if ($request->status === 'pending') {
            $query->where(function($q) {
                $q->where('is_kyc_completed', false)->orWhere('is_rpm_completed', false);
            });
        }

        $leads = $query->paginate(20);
        return view('kyc-rpm.index', compact('leads'));
    }
}
