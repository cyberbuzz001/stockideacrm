<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('login_ip', $request->ip()); // Anti-Hijack Lock
        $request->session()->put('login_ua', hash('sha256', (string) $request->userAgent()));

        // Security Log (Phase 5)
        $user = auth()->user();
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Attendance Log (Phase 10)
        $today = now()->toDateString();
        $attendance = \App\Models\Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            \App\Models\Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'login_at' => now(),
                'ip_address' => $request->ip(),
                'status' => 'Active',
                'last_activity_at' => now()
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Attendance Log (Phase 10)
        $user = auth()->user();
        if ($user) {
            $attendance = \App\Models\Attendance::where('user_id', $user->id)
                ->where('date', now()->toDateString())
                ->first();

            if ($attendance && !$attendance->logout_at) {
                $loginAt = \Carbon\Carbon::parse($attendance->login_at);
                $logoutAt = now();
                $minutes = $logoutAt->diffInMinutes($loginAt);

                $attendance->update([
                    'logout_at' => $logoutAt,
                    'total_minutes' => $minutes
                ]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
