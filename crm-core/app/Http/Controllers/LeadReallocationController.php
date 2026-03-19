<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadReallocationController extends Controller
{
    private const LEAD_STATUSES = [
        'All',
        'Cold Lead',
        'Follow Up',
        'Call Back',
        'New',
        'Trial',
        'Free Trial',
        'Interested',
        'Paid Client',
        'Service Expired',
        'NPC',
        'Not Picked',
        'Not Reachable',
        'Switch Off',
        'Not Interested',
        'DND',
    ];

    /**
     * Display the bulk reallocation tool for Admins
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        
        // Only Admin or Managers/SBAs can access the reallocation tool
        if (!in_array($user->role, ['Admin', 'SBA', 'Manager'])) {
            abort(403, 'Unauthorized. Only Admins or Managers can access the Re-allocation Tool.');
        }

        // Fetch agents that currently have leads assigned to them
        $agentsWithLeads = User::whereHas('leads', function($query) {
            $query->whereIn('status', ['Cold Lead', 'Follow Up', 'Call Back', 'New', 'Trial']);
        });

        // If SBA/Manager, they can only see agents in their team
        if ($user->role !== 'Admin') {
            $teamIds = $user->getAllTeamIds();
            $agentsWithLeads->whereIn('id', $teamIds);
        }

        $agentsWithLeads = $agentsWithLeads->withCount(['leads' => function($query) {
            $query->whereIn('status', ['Cold Lead', 'Follow Up', 'Call Back', 'New', 'Trial']);
        }])->get();

        // Fetch all active agents (targets for reallocation)
        $activeAgents = User::where('is_active', true);
        if ($user->role !== 'Admin') {
            $activeAgents->whereIn('id', $teamIds);
        }
        $activeAgents = $activeAgents->orderBy('name')->get();

        return view('leads.reallocate', compact('agentsWithLeads', 'activeAgents'));
    }

    /**
     * Process bulk lead transfer from one agent to another/others
     */
    public function transfer(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }
        
        if (!in_array($user->role, ['Admin', 'SBA', 'Manager'])) {
            abort(403);
        }

        $request->validate([
            'from_agent_id' => 'required|exists:users,id',
            'to_agent_id' => 'required|exists:users,id',
            'lead_status' => ['required', 'string', Rule::in(self::LEAD_STATUSES)],
        ]);

        // Security check
        if ($user->role !== 'Admin') {
            $teamIds = $user->getAllTeamIds();
            if (!in_array($request->from_agent_id, $teamIds) || !in_array($request->to_agent_id, $teamIds)) {
                return back()->with('error', 'You can only transfer leads between your own team members.');
            }
        }

        $toAgent = User::where('id', $request->to_agent_id)->where('is_active', true)->first();
        if (!$toAgent) {
            return back()->with('error', 'Target agent is not active.');
        }

        $query = Lead::where('assigned_to', $request->from_agent_id);

        if ($request->lead_status !== 'All') {
            $query->where('status', $request->lead_status);
        } else {
            // "All Active" usually excludes disposed/expired leads to prevent cluttering the new agent
            $query->whereNotIn('status', ['Not Picked', 'Switch Off', 'Not Interested', 'DND', 'NPC', 'Not Reachable', 'Service Expired']);
        }

        $leadsCount = $query->count();
        $leadsToTransfer = $query->get();

        if ($leadsCount === 0) {
            return back()->with('error', 'No leads found matching the criteria for this agent.');
        }

        // Perform Transfer
        $query->update([
            'assigned_to' => $request->to_agent_id,
            'assigned_by' => $user->id
        ]);

        // Log Activity for each transferred lead
        foreach ($leadsToTransfer as $lead) {
            $lead->activities()->create([
                'user_id' => $user->id,
                'activity_type' => 'Bulk Re-allocated',
                'notes' => "Lead bulk transferred to " . $toAgent->name . " due to agent inactive/reassignment logic.",
            ]);
        }

        return back()->with('success', "Successfully re-allocated $leadsCount leads to $toAgent->name.");
    }
}
