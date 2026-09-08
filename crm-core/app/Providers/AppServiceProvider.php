<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\LogSystemActivity::class);

        try {
            // Register Dynamic Permissions Matrix Gates
            $matrix = json_decode(\App\Models\SystemSetting::get('role_access_matrix', '{}'), true);
            if ($matrix) {
                foreach ($matrix as $module => $actions) {
                    foreach ($actions as $action => $allowedRoles) {
                        \Illuminate\Support\Facades\Gate::define("rbac.{$module}.{$action}", function ($user) use ($module, $action) {
                            return $user->hasPermission($module, $action);
                        });
                    }
                }
            }

            // Share Company Branding Globally
            \Illuminate\Support\Facades\View::share('company_name', \App\Models\SystemSetting::get('company_name', 'StockIdea'));
            \Illuminate\Support\Facades\View::share('company_logo', \App\Models\SystemSetting::get('company_logo'));
        } catch (\Exception $e) {
            // system_settings may not exist yet (initial migrations) or the
            // DB may be briefly unavailable — fall back to safe defaults so
            // every view using $company_name/$company_logo still resolves
            // instead of throwing "Undefined variable".
            \Illuminate\Support\Facades\View::share('company_name', 'StockIdea');
            \Illuminate\Support\Facades\View::share('company_logo', null);
        }
    }
}
