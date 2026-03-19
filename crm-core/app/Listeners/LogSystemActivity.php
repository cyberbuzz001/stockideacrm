<?php

namespace App\Listeners;

use App\Models\SystemActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LogSystemActivity
{
    /**
     * Handle user login.
     */
    public function handleLogin(Login $event)
    {
        SystemActivity::create([
            'user_id' => $event->user->id,
            'activity_type' => 'Login',
            'description' => 'User logged in',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle user logout.
     */
    public function handleLogout(Logout $event)
    {
        if ($event->user) {
            SystemActivity::create([
                'user_id' => $event->user->id,
                'activity_type' => 'Logout',
                'description' => 'User logged out',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
        ];
    }
}
