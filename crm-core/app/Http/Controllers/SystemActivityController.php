<?php

namespace App\Http\Controllers;

use App\Models\LeadActivity;
use App\Models\SystemActivity;
use Illuminate\Http\Request;

class SystemActivityController extends Controller
{
    /**
     * Display activity logs.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Admin', 'Manager'])) {
            abort(403);
        }

        // Lead Activities (Business Logic Logs)
        $leadActivities = LeadActivity::with(['user', 'lead'])
            ->latest()
            ->paginate(15); // Adjust pagination as needed

        // System Activities (Auth/Admin Logs) - Admin Only?
        // Let's allow Managers too, or just Admin.
        // For now restricting System Logs to Admin/Manager.
        $systemActivities = collect([]);
        $systemActivities = SystemActivity::with('user')
            ->latest()
            ->paginate(15);

        return view('system_activities.index', compact('leadActivities', 'systemActivities'));
    }
}
