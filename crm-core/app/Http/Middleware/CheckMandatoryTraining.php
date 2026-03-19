<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMandatoryTraining
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user) {
            return $next($request);
        }

        // Admins and Managers don't need to be blocked
        if (in_array($user->role, ['Admin', 'Manager'])) {
            return $next($request);
        }

        // Check for pending modules assigned to this user role or 'All'
        $hasPending = \App\Models\TrainingModule::where(function($query) use ($user) {
            $query->where('target_team', 'All')
                  ->orWhere('target_team', $user->role);
        })
        ->whereDoesntHave('logs', function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('completion_status', 'completed');
        })
        ->exists();

        $routeName = $request->route()?->getName();
        $isLeadsRoute = $routeName ? str_starts_with($routeName, 'leads.') : str_starts_with($request->path(), 'leads');

        // Only block access to leads when training is pending
        if ($hasPending && $isLeadsRoute) {
            // Set alert for when they reach the learning center
            return redirect()->route('agent.learning.index')->with('error', 'You have pending mandatory training. Access to leads is locked until completed.');
        }

        return $next($request);
    }
}
