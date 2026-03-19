<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (Auth::check()) {
            $sessionIp = $request->session()->get('login_ip');
            $sessionUa = $request->session()->get('login_ua');

            // If session IP is not set (e.g. old session), verify against DB or set it now
            if (!$sessionIp) {
                // Backward compatibility for active sessions before this feature
                // Strict mode: Logout. Relaxed mode: Set it.
                // Let's use Relaxed check: if missing, set it to current.
                // But the requirement is "Force logout if IP changes".
                // If we don't have it, we trust the current one as the start.
                $request->session()->put('login_ip', $request->ip());
            } elseif ($sessionIp !== $request->ip()) {
                // IP Mismatch Detected! Possible Hijack or Network Switch.
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Security Alert: Your IP address changed. Please login again.'
                ]);
            }

            // Device fingerprint check (user agent hash)
            $currentUa = hash('sha256', (string) $request->userAgent());
            if ($sessionUa && !hash_equals($sessionUa, $currentUa)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Security Alert: Your device fingerprint changed. Please login again.'
                ]);
            }
        }

        return $next($request);
    }
}
