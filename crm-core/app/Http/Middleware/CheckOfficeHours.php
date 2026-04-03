<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
 
class CheckOfficeHours
{
    /**
     * Handle an incoming request.
     *
     * Monday to Saturday: 9am - 5pm
     * Sunday: Blocked
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // 1. Check if Account is Active (Manual Enable/Block)
            if (isset($user->is_active) && !$user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
 
                return redirect()->route('login')->withErrors([
                    'email' => "Your account has been deactivated. Please contact your administrator."
                ]);
            }
 
            // 2. Check Office Hours for agents (BA and SBA)
            // Admins and Managers are exempt from office hours
            if (in_array($user->role, ['Admin', 'Manager'], true)) {
                return $next($request);
            }
 
            // Get current time in India/Kolkata (or system time)
            // Use the same timezone as the rest of the application
            $now = now();
            $dayOfWeek = $now->dayOfWeek; // 0 (Sun) - 6 (Sat)
            $hour = intval($now->format('H'));
            $minute = intval($now->format('i'));
 
            $isSunday = ($dayOfWeek === Carbon::SUNDAY);
            $isOutsideHours = ($hour < 9 || $hour >= 17);
 
            if ($isSunday || $isOutsideHours) {
                // Force Logout
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
 
                $reason = $isSunday ? "Office is closed on Sundays." : "Office hours are from 9:00 AM to 5:00 PM.";
                
                return redirect()->route('login')->withErrors([
                    'email' => "Access Restricted: {$reason} Your session has been terminated."
                ]);
            }
        }
 
        return $next($request);
    }
}
