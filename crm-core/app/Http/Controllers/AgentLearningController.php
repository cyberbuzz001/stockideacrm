<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgentLearningController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['BA', 'SBA', 'Admin', 'Manager'])) {
            abort(403);
        }
        
        // Modules assigned to this user
        $modules = \App\Models\TrainingModule::where(function($query) use ($user) {
            $query->where('target_team', 'All')
                  ->orWhere('target_team', $user->role);
        })->latest()->get();

        // Attach completion status
        $logs = \App\Models\TrainingLog::where('user_id', $user->id)
            ->whereIn('module_id', $modules->pluck('id'))
            ->get()
            ->keyBy('module_id');

        foreach ($modules as $module) {
            $log = $logs->get($module->id);
            $module->is_completed = $log && $log->completion_status === 'completed';
            $module->completed_at = $log ? $log->completed_at : null;
        }

        return view('agent.learning.index', compact('modules'));
    }

    public function show(\App\Models\TrainingModule $module)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['BA', 'SBA', 'Admin', 'Manager'])) {
            abort(403);
        }

        // Check if assigned
        if (!in_array($user->role, ['Admin', 'Manager']) && $module->target_team !== 'All' && $module->target_team !== $user->role) {
            abort(403, 'Unauthorized action.');
        }
        
        // Log that they opened it
        \App\Models\TrainingLog::firstOrCreate(
            ['user_id' => $user->id, 'module_id' => $module->id],
            ['completion_status' => 'pending']
        );

        return view('agent.learning.show', compact('module'));
    }

    public function complete(\App\Models\TrainingModule $module, Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['BA', 'SBA', 'Admin', 'Manager'])) {
            abort(403);
        }

        // Check if assigned
        if (!in_array($user->role, ['Admin', 'Manager']) && $module->target_team !== 'All' && $module->target_team !== $user->role) {
            abort(403, 'Unauthorized action.');
        }

        $log = \App\Models\TrainingLog::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();
            
        $validated = $request->validate([
            'time_spent' => 'nullable|integer|min:1|max:600',
        ]);

        if ($log && $log->completion_status !== 'completed') {
            $log->update([
                'completion_status' => 'completed',
                'completed_at' => now(),
                'time_spent' => $validated['time_spent'] ?? 60
            ]);
        }

        return redirect()->route('agent.learning.index')->with('success', 'Training module marked as completed.');
    }
}
