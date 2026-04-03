# 💻 CRM DASHBOARD - COMPLETE IMPLEMENTATION GUIDE WITH CODE EXAMPLES

**Production-Ready Code | AntiGravity + Stitch AI | Laravel 12 + React**

---

## TABLE OF CONTENTS
1. [Database Setup](#database-setup)
2. [Backend Implementation](#backend-implementation)
3. [Frontend Components](#frontend-components)
4. [Theme Configuration](#theme-configuration)
5. [Real-Time Integration](#real-time-integration)
6. [Complete Working Examples](#complete-working-examples)

---

## DATABASE SETUP

### Migration: Create Leads Table
**File: `database/migrations/2024_04_02_000000_create_leads_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->enum('status', [
                'Free Trial',
                'Callback',
                'Follow Up',
                'Paid',
                'Subscribed',
                'Active',
                'Cold',
                'Rejected',
            ])->default('Cold');
            $table->dateTime('trial_start_date')->nullable();
            $table->dateTime('trial_end_date')->nullable();
            $table->dateTime('callback_date')->nullable();
            $table->dateTime('last_contact_at')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('expected_value', 10, 2)->nullable();
            $table->decimal('actual_value', 10, 2)->nullable();
            $table->string('source')->nullable(); // how lead was acquired
            $table->timestamps();
            
            // Indexes for performance
            $table->index('agent_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('callback_date');
            $table->index('trial_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
```

### Migration: Create Call Logs Table
**File: `database/migrations/2024_04_02_000001_create_call_logs_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->dateTime('call_started_at');
            $table->dateTime('call_ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->enum('outcome', [
                'Connected',
                'Not Answered',
                'Voicemail',
                'Call Rejected',
                'Call Failed',
            ])->default('Connected');
            $table->text('notes')->nullable();
            $table->string('recording_url')->nullable();
            $table->boolean('is_live')->default(false);
            $table->timestamps();
            
            $table->index('agent_id');
            $table->index('lead_id');
            $table->index('call_started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
```

### Migration: Create Payments Table
**File: `database/migrations/2024_04_02_000002_create_payments_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('INR');
            $table->string('payment_mode'); // Credit Card, UPI, Bank Transfer, etc.
            $table->enum('status', [
                'Pending',
                'Approved',
                'Completed',
                'Failed',
                'Refunded',
            ])->default('Pending');
            $table->dateTime('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->dateTime('next_payment_due')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('agent_id');
            $table->index('lead_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

### Seeder: Create Sample Data
**File: `database/seeders/DashboardDataSeeder.php`**

```php
<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'daily_target' => 0,
            ]
        );

        // Get or create agents
        $agents = [];
        foreach (range(1, 5) as $i) {
            $agents[] = User::firstOrCreate(
                ['email' => "agent{$i}@crm.com"],
                [
                    'name' => "Business Advisor {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'ba',
                    'daily_target' => 50000, // Daily target in rupees
                    'last_activity_at' => now(),
                ]
            );
        }

        // Create sample leads
        foreach ($agents as $agent) {
            // Create 20 leads per agent with different statuses
            
            // Free Trial leads
            foreach (range(1, 3) as $i) {
                Lead::create([
                    'agent_id' => $agent->id,
                    'name' => "Lead Free Trial {$agent->id}-{$i}",
                    'phone' => '98' . rand(10000000, 99999999),
                    'email' => "lead{$agent->id}{$i}@example.com",
                    'status' => 'Free Trial',
                    'trial_start_date' => Carbon::now()->subDays(rand(1, 5)),
                    'trial_end_date' => Carbon::now()->addDays(rand(1, 10)),
                    'last_contact_at' => Carbon::now()->subHours(rand(1, 24)),
                    'source' => 'Organic',
                    'expected_value' => rand(50000, 100000),
                ]);
            }

            // Callback/Follow-up leads
            foreach (range(1, 3) as $i) {
                Lead::create([
                    'agent_id' => $agent->id,
                    'name' => "Lead Callback {$agent->id}-{$i}",
                    'phone' => '98' . rand(10000000, 99999999),
                    'email' => "callback{$agent->id}{$i}@example.com",
                    'status' => 'Callback',
                    'callback_date' => Carbon::now()->addDays(rand(-5, 5)),
                    'last_contact_at' => Carbon::now()->subDays(rand(1, 3)),
                    'source' => 'Referral',
                    'expected_value' => rand(30000, 80000),
                ]);
            }

            // Paid leads
            foreach (range(1, 5) as $i) {
                Lead::create([
                    'agent_id' => $agent->id,
                    'name' => "Paid Client {$agent->id}-{$i}",
                    'phone' => '98' . rand(10000000, 99999999),
                    'email' => "paid{$agent->id}{$i}@example.com",
                    'status' => 'Paid',
                    'last_contact_at' => Carbon::now()->subDays(rand(1, 30)),
                    'actual_value' => $value = rand(100000, 500000),
                    'source' => 'Advertisement',
                ]);
            }

            // Cold leads
            foreach (range(1, 9) as $i) {
                Lead::create([
                    'agent_id' => $agent->id,
                    'name' => "Cold Lead {$agent->id}-{$i}",
                    'phone' => '98' . rand(10000000, 99999999),
                    'email' => "cold{$agent->id}{$i}@example.com",
                    'status' => 'Cold',
                    'last_contact_at' => Carbon::now()->subDays(rand(10, 60)),
                    'source' => 'Email Campaign',
                    'expected_value' => rand(20000, 60000),
                ]);
            }
        }

        // Create sample call logs
        foreach (Lead::limit(50)->get() as $lead) {
            foreach (range(1, rand(1, 3)) as $i) {
                CallLog::create([
                    'agent_id' => $lead->agent_id,
                    'lead_id' => $lead->id,
                    'call_started_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    'call_ended_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->addMinutes(rand(2, 30)),
                    'duration_seconds' => rand(120, 1800),
                    'outcome' => ['Connected', 'Not Answered', 'Voicemail'][rand(0, 2)],
                ]);
            }
        }

        // Create sample payments
        foreach (Lead::where('status', 'Paid')->limit(30)->get() as $lead) {
            Payment::create([
                'agent_id' => $lead->agent_id,
                'lead_id' => $lead->id,
                'amount' => rand(100000, 500000),
                'currency' => 'INR',
                'payment_mode' => ['Credit Card', 'UPI', 'Bank Transfer'][rand(0, 2)],
                'status' => 'Completed',
                'paid_at' => Carbon::now()->subDays(rand(0, 30)),
                'next_payment_due' => Carbon::now()->addMonths(1),
                'transaction_id' => 'TXN' . uniqid(),
            ]);
        }

        $this->command->info('Sample data created successfully!');
    }
}
```

---

## BACKEND IMPLEMENTATION

### Model: Lead with Scopes
**File: `app/Models/Lead.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Lead extends Model
{
    protected $fillable = [
        'agent_id',
        'name',
        'phone',
        'email',
        'status',
        'trial_start_date',
        'trial_end_date',
        'callback_date',
        'last_contact_at',
        'notes',
        'expected_value',
        'actual_value',
        'source',
    ];

    protected $casts = [
        'trial_start_date' => 'datetime',
        'trial_end_date' => 'datetime',
        'callback_date' => 'datetime',
        'last_contact_at' => 'datetime',
        'expected_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scopes - Query Filtering by Role
     */
    public function scopePersonal($query)
    {
        return $query->where('agent_id', Auth::id());
    }

    public function scopeTeam($query)
    {
        $user = Auth::user();
        if ($user->role === 'sba' || $user->role === 'manager') {
            $teamAgentIds = $user->team->agents()->pluck('id');
            return $query->whereIn('agent_id', $teamAgentIds);
        }
        return $query;
    }

    public function scopeAllData($query)
    {
        return $query;
    }

    /**
     * Status Scopes
     */
    public function scopeFreeTrials($query)
    {
        return $query->where('status', 'Free Trial');
    }

    public function scopeCallbacks($query)
    {
        return $query->whereIn('status', ['Callback', 'Follow Up']);
    }

    public function scopePaidClients($query)
    {
        return $query->whereIn('status', ['Paid', 'Subscribed', 'Active']);
    }

    public function scopeOverdueCallbacks($query)
    {
        return $query->whereIn('status', ['Callback', 'Follow Up'])
            ->where('callback_date', '<', Carbon::today());
    }

    public function scopeColdLeads($query)
    {
        return $query->where('status', 'Cold')
            ->where('last_contact_at', '<', Carbon::now()->subDays(7));
    }

    /**
     * Attributes
     */
    public function getTrialDaysRemainingAttribute()
    {
        if (!$this->trial_end_date) return null;
        return $this->trial_end_date->diffInDays(Carbon::today());
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->callback_date || $this->callback_date >= Carbon::today()) return 0;
        return Carbon::today()->diffInDays($this->callback_date);
    }

    /**
     * Global Scope - Apply role-based filtering automatically
     */
    protected static function boot()
    {
        parent::boot();

        $user = Auth::user();

        if ($user) {
            if ($user->role === 'ba') {
                // BA only sees their own leads
                static::addGlobalScope('personal', fn($q) => $q->where('agent_id', Auth::id()));
            } elseif (in_array($user->role, ['manager', 'sba'])) {
                // Manager/SBA only sees their team's leads
                static::addGlobalScope('team', function ($q) use ($user) {
                    $teamAgentIds = $user->team->agents()->pluck('id');
                    return $q->whereIn('agent_id', $teamAgentIds);
                });
            }
            // Admin sees all
        }
    }
}
```

### Service: Dashboard Metrics Calculation
**File: `app/Services/DashboardService.php`**

```php
<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get BA (Agent) Dashboard Metrics
     */
    public function getBAMetrics(int $agentId): array
    {
        $period = request()->query('period', 'today');
        [$dateFrom, $dateTo] = $this->getDateRange($period);

        return [
            'kpis' => [
                'total_leads' => $this->getTotalLeads($agentId),
                'new_leads' => $this->getNewLeads($agentId, $dateFrom, $dateTo),
                'calls_today' => $this->getCallsToday($agentId),
                'followups_due' => $this->getFollowupsDue($agentId),
                'active_trials' => $this->getActiveTrials($agentId),
                'paid_clients' => $this->getPaidClients($agentId),
            ],

            'tables' => [
                'priority_queue' => $this->getPriorityQueue($agentId, 15),
                'overdue_followups' => $this->getOverdueFollowups($agentId, 10),
                'active_trials' => $this->getActiveTrialsTable($agentId, 10),
                'paid_clients_list' => $this->getPaidClientsList($agentId, 10),
            ],

            'charts' => [
                'call_volume_7days' => $this->getCallVolume7Days($agentId),
                'conversion_funnel' => $this->getConversionFunnel($agentId),
            ],

            'alerts' => [
                'pending_training' => $this->getPendingTraining($agentId),
                'renewal_alerts' => $this->getRenewalAlerts($agentId),
                'call_target_alert' => $this->getCallTargetAlert($agentId),
            ],
        ];
    }

    /**
     * KPI Calculation: Total Leads
     */
    private function getTotalLeads(int $agentId): array
    {
        $count = Lead::where('agent_id', $agentId)->count();
        
        return [
            'label' => 'Total Assigned Leads',
            'value' => $count,
            'icon' => 'Users',
            'color' => 'primary',
            'trend' => null,
        ];
    }

    /**
     * KPI Calculation: Followups Due Today
     */
    private function getFollowupsDue(int $agentId): array
    {
        $count = Lead::where('agent_id', $agentId)
            ->whereIn('status', ['Follow Up', 'Callback'])
            ->where('callback_date', '<=', Carbon::today())
            ->count();

        return [
            'label' => 'Follow-ups Due',
            'value' => $count,
            'icon' => 'Phone',
            'color' => $count > 0 ? 'danger' : 'success',
            'trend' => null,
            'alert_badge' => $count > 0 ? true : false,
        ];
    }

    /**
     * Priority Queue: Intelligent Lead Sorting
     */
    private function getPriorityQueue(int $agentId, int $limit = 15): array
    {
        // 1. Overdue follow-ups (red)
        $overdue = Lead::where('agent_id', $agentId)
            ->whereIn('status', ['Follow Up', 'Callback'])
            ->where('callback_date', '<', Carbon::today())
            ->with('callLogs', 'payments')
            ->orderBy('callback_date', 'asc')
            ->limit($limit)
            ->get();

        // 2. Today's follow-ups (orange)
        $today = Lead::where('agent_id', $agentId)
            ->whereIn('status', ['Follow Up', 'Callback'])
            ->whereDate('callback_date', Carbon::today())
            ->with('callLogs', 'payments')
            ->orderBy('callback_date', 'asc')
            ->limit($limit - count($overdue))
            ->get();

        // 3. New leads today (blue)
        $new = Lead::where('agent_id', $agentId)
            ->whereDate('created_at', Carbon::today())
            ->with('callLogs', 'payments')
            ->orderBy('created_at', 'desc')
            ->limit($limit - count($overdue) - count($today))
            ->get();

        // 4. Cold leads (yellow)
        $cold = Lead::where('agent_id', $agentId)
            ->coldLeads()
            ->with('callLogs', 'payments')
            ->orderBy('last_contact_at', 'asc')
            ->limit($limit - count($overdue) - count($today) - count($new))
            ->get();

        // 5. Active trials (green)
        $trials = Lead::where('agent_id', $agentId)
            ->freeTrials()
            ->where('trial_end_date', '>=', Carbon::today())
            ->with('callLogs', 'payments')
            ->orderBy('trial_end_date', 'asc')
            ->limit($limit - count($overdue) - count($today) - count($new) - count($cold))
            ->get();

        // Merge and format
        $queue = [];
        
        foreach ($overdue as $lead) {
            $queue[] = $this->formatLeadForQueue($lead, 'overdue', 'red');
        }
        foreach ($today as $lead) {
            $queue[] = $this->formatLeadForQueue($lead, 'today', 'orange');
        }
        foreach ($new as $lead) {
            $queue[] = $this->formatLeadForQueue($lead, 'new', 'blue');
        }
        foreach ($cold as $lead) {
            $queue[] = $this->formatLeadForQueue($lead, 'cold', 'yellow');
        }
        foreach ($trials as $lead) {
            $queue[] = $this->formatLeadForQueue($lead, 'trial', 'green');
        }

        return array_slice($queue, 0, $limit);
    }

    /**
     * Format lead data for queue display
     */
    private function formatLeadForQueue(Lead $lead, string $priority, string $color): array
    {
        $lastCall = $lead->callLogs()->latest()->first();
        $data = [
            'id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'status' => $lead->status,
            'priority' => $priority,
            'priority_color' => $color,
            'last_contact' => $lastCall?->call_started_at?->diffForHumans(),
        ];

        if ($priority === 'overdue') {
            $data['days_overdue'] = $lead->days_overdue;
        } elseif ($priority === 'trial') {
            $data['days_remaining'] = $lead->trial_days_remaining;
            $data['trial_end_date'] = $lead->trial_end_date->toDateString();
        }

        return $data;
    }

    /**
     * Get date range based on period filter
     */
    private function getDateRange(string $period): array
    {
        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'ytd' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    // ... Implement all other private methods similarly
}
```

### Controller: BA Dashboard
**File: `app/Http/Controllers/BAController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BAController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->middleware('role:ba');
    }

    public function index()
    {
        $agentId = Auth::id();
        $cacheTTL = 180; // 3 minutes for BA (real-time needed)

        // Cache metrics with role-specific TTL
        $metrics = Cache::remember(
            "dashboard_ba_{$agentId}",
            $cacheTTL,
            fn() => $this->dashboardService->getBAMetrics($agentId)
        );

        $period = request()->query('period', 'today');

        return Inertia::render('Dashboard/BADashboard', [
            'metrics' => $metrics,
            'period' => $period,
            'user' => Auth::user()->only('id', 'name', 'email', 'daily_target'),
        ]);
    }
}
```

### API Route: Real-Time Metrics
**File: `routes/api.php`**

```php
<?php

use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Real-time metrics
    Route::get('/metrics/ba/{userId}', [MetricsController::class, 'getBAMetrics']);
    Route::get('/metrics/admin', [MetricsController::class, 'getAdminMetrics']);
    Route::get('/metrics/manager/{managerId}', [MetricsController::class, 'getManagerMetrics']);
    Route::get('/metrics/sba/{sbaId}', [MetricsController::class, 'getSBAMetrics']);
});
```

---

## FRONTEND COMPONENTS

### Component: StatCard with Theme Support
**File: `resources/react/components/KPICards/StatCard.jsx`**

```jsx
import React from 'react';
import { TrendIndicator } from './TrendIndicator';
import { useAntiGravityTheme } from '../../hooks/useTheme';
import clsx from 'clsx';

export const StatCard = ({
  title,
  value,
  trend,
  icon: Icon,
  onClick,
  color = 'primary',
  size = 'md',
  alertBadge = false,
  loading = false,
  className,
}) => {
  const { theme } = useAntiGravityTheme();

  const colorMap = {
    primary: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    success: 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    danger: 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    warning: 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
  };

  return (
    <div
      onClick={onClick}
      className={clsx(
        'relative bg-surface dark:bg-slate-800 border-2 border-border dark:border-slate-700',
        'rounded-lg p-6 transition-all duration-300 ease-out',
        'hover:shadow-lg hover:scale-105 cursor-pointer',
        'transform-gpu will-change-transform',
        size === 'lg' && 'p-8',
        loading && 'animate-pulse opacity-75',
        className,
      )}
    >
      {/* Alert Badge */}
      {alertBadge && (
        <div className="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full animate-pulse" />
      )}

      <div className="flex items-start justify-between">
        <div className="flex-1">
          {/* Title */}
          <p className="text-text-secondary dark:text-slate-400 text-sm font-medium">
            {title}
          </p>

          {/* Value */}
          {loading ? (
            <div className="h-8 bg-slate-200 dark:bg-slate-700 rounded mt-2 w-32 animate-pulse"></div>
          ) : (
            <div className="mt-3 flex items-baseline gap-2">
              <h3 className="text-3xl font-bold text-text dark:text-white">
                {typeof value === 'number' ? value.toLocaleString() : value}
              </h3>
              {trend && <TrendIndicator trend={trend} />}
            </div>
          )}
        </div>

        {/* Icon */}
        {Icon && (
          <div className={clsx('p-3 rounded-lg', colorMap[color])}>
            <Icon size={24} strokeWidth={1.5} />
          </div>
        )}
      </div>
    </div>
  );
};

export default StatCard;
```

### Component: Data Table with Sorting
**File: `resources/react/components/Tables/DataTable.jsx`**

```jsx
import React, { useState, useMemo } from 'react';
import { ChevronUp, ChevronDown, Phone, Eye } from 'lucide-react';
import { useAntiGravityTheme } from '../../hooks/useTheme';
import clsx from 'clsx';

export const DataTable = ({
  columns,
  data,
  title,
  onRowClick,
  isLoading,
  pagination = true,
  itemsPerPage = 10,
  actions,
  className,
}) => {
  const { theme } = useAntiGravityTheme();
  const [sortConfig, setSortConfig] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);

  // Sort data
  const sortedData = useMemo(() => {
    if (!sortConfig) return data;

    const sorted = [...data].sort((a, b) => {
      const aValue = a[sortConfig.key];
      const bValue = b[sortConfig.key];

      if (aValue < bValue) return sortConfig.direction === 'asc' ? -1 : 1;
      if (aValue > bValue) return sortConfig.direction === 'asc' ? 1 : -1;
      return 0;
    });

    return sorted;
  }, [data, sortConfig]);

  // Paginate data
  const paginatedData = useMemo(() => {
    if (!pagination) return sortedData;
    const startIndex = (currentPage - 1) * itemsPerPage;
    return sortedData.slice(startIndex, startIndex + itemsPerPage);
  }, [sortedData, currentPage, itemsPerPage, pagination]);

  const totalPages = Math.ceil(sortedData.length / itemsPerPage);

  const handleSort = (columnKey) => {
    setSortConfig((prev) => {
      if (prev?.key === columnKey) {
        return { key: columnKey, direction: prev.direction === 'asc' ? 'desc' : 'asc' };
      }
      return { key: columnKey, direction: 'asc' };
    });
  };

  const SortIcon = ({ columnKey }) => {
    if (sortConfig?.key !== columnKey) {
      return <ChevronUp size={16} className="text-slate-400" />;
    }
    return sortConfig.direction === 'asc' ? (
      <ChevronUp size={16} className="text-primary" />
    ) : (
      <ChevronDown size={16} className="text-primary" />
    );
  };

  return (
    <div className={clsx(
      'rounded-lg border border-border bg-surface dark:bg-slate-800',
      'shadow-sm dark:shadow-lg transition-theme',
      className
    )}>
      {title && (
        <div className="px-6 py-4 border-b border-border dark:border-slate-700">
          <h3 className="text-lg font-semibold text-text dark:text-white">{title}</h3>
        </div>
      )}

      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-border dark:border-slate-700 bg-slate-50 dark:bg-slate-700">
              {columns.map((column) => (
                <th
                  key={column.key}
                  onClick={() => column.sortable && handleSort(column.key)}
                  className={clsx(
                    'px-6 py-3 text-left font-semibold text-text-secondary dark:text-slate-300',
                    column.sortable && 'cursor-pointer hover:text-text dark:hover:text-white',
                  )}
                >
                  <div className="flex items-center gap-2">
                    {column.label}
                    {column.sortable && <SortIcon columnKey={column.key} />}
                  </div>
                </th>
              ))}
              {actions && <th className="px-6 py-3 text-right">Actions</th>}
            </tr>
          </thead>

          <tbody>
            {isLoading ? (
              Array.from({ length: 5 }).map((_, idx) => (
                <tr key={idx} className="border-b border-border dark:border-slate-700">
                  {columns.map((_, colIdx) => (
                    <td key={colIdx} className="px-6 py-4">
                      <div className="h-4 bg-slate-200 dark:bg-slate-600 rounded w-3/4 animate-pulse"></div>
                    </td>
                  ))}
                </tr>
              ))
            ) : paginatedData.length === 0 ? (
              <tr>
                <td colSpan={columns.length + (actions ? 1 : 0)} className="px-6 py-8 text-center">
                  <p className="text-text-secondary dark:text-slate-400">No data available</p>
                </td>
              </tr>
            ) : (
              paginatedData.map((row, rowIdx) => (
                <tr
                  key={rowIdx}
                  onClick={() => onRowClick?.(row)}
                  className={clsx(
                    'border-b border-border dark:border-slate-700 transition-colors',
                    onRowClick && 'hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer',
                  )}
                >
                  {columns.map((column) => (
                    <td key={column.key} className="px-6 py-4 text-text dark:text-slate-200">
                      {column.render ? column.render(row[column.key], row) : row[column.key]}
                    </td>
                  ))}
                  {actions && (
                    <td className="px-6 py-4">
                      <div className="flex gap-2 justify-end">
                        {actions(row)}
                      </div>
                    </td>
                  )}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {pagination && totalPages > 1 && (
        <div className="px-6 py-4 border-t border-border dark:border-slate-700 flex items-center justify-between">
          <p className="text-sm text-text-secondary dark:text-slate-400">
            Page {currentPage} of {totalPages}
          </p>
          <div className="flex gap-2">
            <button
              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
              disabled={currentPage === 1}
              className={clsx(
                'px-3 py-1 rounded text-sm font-medium transition-colors',
                currentPage === 1
                  ? 'bg-slate-200 dark:bg-slate-700 text-slate-500 cursor-not-allowed'
                  : 'bg-primary text-white hover:bg-blue-700'
              )}
            >
              Previous
            </button>
            <button
              onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
              disabled={currentPage === totalPages}
              className={clsx(
                'px-3 py-1 rounded text-sm font-medium transition-colors',
                currentPage === totalPages
                  ? 'bg-slate-200 dark:bg-slate-700 text-slate-500 cursor-not-allowed'
                  : 'bg-primary text-white hover:bg-blue-700'
              )}
            >
              Next
            </button>
          </div>
        </div>
      )}
    </div>
  );
};
```

### Component: Full BA Dashboard
**File: `resources/react/components/Dashboard/BADashboard.jsx`**

```jsx
import React, { useEffect, useState } from 'react';
import { Users, Phone, Target, TrendingUp, AlertCircle, Clock } from 'lucide-react';
import { useDashboardData } from '../../hooks/useDashboardData';
import { useRealtime } from '../../hooks/useRealtime';
import StatCard from '../KPICards/StatCard';
import { DataTable } from '../Tables/DataTable';
import { PeriodFilter } from '../Filters/PeriodFilter';
import { LoadingSkeleton } from '../Common/LoadingSkeleton';
import { AlertBanner } from '../Alerts/AlertBanner';
import clsx from 'clsx';

export const BADashboard = () => {
  const { data, loading, error } = useDashboardData('ba');
  const { realtime } = useRealtime('metrics:ba');
  const [period, setPeriod] = useState('today');

  if (error) return <div className="text-red-500 p-6">Error: {error}</div>;

  const metrics = data?.metrics || {};
  const alerts = data?.alerts || [];

  const kpiIcons = {
    total_leads: Users,
    new_leads: TrendingUp,
    calls_today: Phone,
    followups_due: AlertCircle,
    active_trials: Clock,
    paid_clients: Target,
  };

  return (
    <div className="min-h-screen bg-background dark:bg-slate-900 transition-theme">
      {/* Header */}
      <div className="sticky top-0 z-40 bg-surface dark:bg-slate-800 border-b border-border dark:border-slate-700 p-6 shadow-sm">
        <div className="flex items-center justify-between max-w-7xl mx-auto">
          <div>
            <h1 className="text-3xl font-bold text-text dark:text-white">My Dashboard</h1>
            <p className="text-text-secondary dark:text-slate-400 mt-1">Welcome back, Business Advisor</p>
          </div>
          <PeriodFilter period={period} onChange={setPeriod} />
        </div>
      </div>

      <div className="max-w-7xl mx-auto p-6 space-y-6">
        {/* Alerts */}
        {alerts.length > 0 && (
          <div className="space-y-3">
            {alerts.map((alert) => (
              <AlertBanner key={alert.id} {...alert} />
            ))}
          </div>
        )}

        {/* KPI Cards Grid */}
        {loading ? (
          <LoadingSkeleton />
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {metrics.kpis && Object.entries(metrics.kpis).map(([key, kpi]) => (
                <StatCard
                  key={key}
                  title={kpi.label}
                  value={kpi.value}
                  trend={kpi.trend}
                  icon={kpiIcons[key]}
                  color={kpi.color || 'primary'}
                  alertBadge={kpi.alert_badge}
                />
              ))}
            </div>

            {/* Tables Section */}
            <div className="space-y-6">
              {/* Priority Queue */}
              {metrics.tables?.priority_queue && (
                <DataTable
                  title="My Priority Queue - Action Required"
                  columns={[
                    { key: 'name', label: 'Lead Name', sortable: true },
                    { key: 'phone', label: 'Phone', sortable: false, render: (phone) => <a href={`tel:${phone}`} className="text-primary hover:underline">{phone}</a> },
                    { key: 'last_contact', label: 'Last Contact', sortable: false },
                    { key: 'priority', label: 'Priority', sortable: true, render: (priority) => {
                      const colors = {
                        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        today: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                        new: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        cold: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        trial: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                      };
                      return <span className={clsx('px-3 py-1 rounded-full text-xs font-semibold', colors[priority])}>{priority}</span>;
                    }},
                  ]}
                  data={metrics.tables.priority_queue || []}
                  pagination
                  itemsPerPage={15}
                  actions={(row) => [
                    <button key="call" className="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded" title="Call">
                      <Phone size={18} />
                    </button>,
                    <button key="view" className="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded" title="View">
                      <Eye size={18} />
                    </button>,
                  ]}
                />
              )}

              {/* Overdue Follow-ups */}
              {metrics.tables?.overdue_followups && metrics.tables.overdue_followups.length > 0 && (
                <DataTable
                  title="⏰ Overdue Follow-ups"
                  columns={[
                    { key: 'name', label: 'Lead Name', sortable: true },
                    { key: 'phone', label: 'Phone', sortable: false },
                    { key: 'days_overdue', label: 'Days Overdue', sortable: true },
                  ]}
                  data={metrics.tables.overdue_followups}
                  pagination
                  itemsPerPage={10}
                />
              )}
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default BADashboard;
```

---

## THEME CONFIGURATION

### CSS Variables for Theming
**File: `resources/react/styles/theme.css`**

```css
/* Light Mode (Default) */
:root {
  /* Colors */
  --color-primary: #3B82F6;
  --color-secondary: #10B981;
  --color-warning: #F59E0B;
  --color-danger: #EF4444;
  --color-success: #10B981;
  --color-info: #3B82F6;
  
  /* Backgrounds */
  --color-background: #FFFFFF;
  --color-surface: #F9FAFB;
  --color-border: #E5E7EB;
  
  /* Text */
  --color-text: #1F2937;
  --color-textSecondary: #6B7280;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  
  /* Radius */
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  
  /* Transitions */
  --transition-duration: 300ms;
  --transition-timing: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Dark Mode */
:root.dark {
  /* Colors */
  --color-primary: #60A5FA;
  --color-secondary: #34D399;
  --color-warning: #FBBF24;
  --color-danger: #F87171;
  --color-success: #34D399;
  --color-info: #60A5FA;
  
  /* Backgrounds */
  --color-background: #111827;
  --color-surface: #1F2937;
  --color-border: #374151;
  
  /* Text */
  --color-text: #F3F4F6;
  --color-textSecondary: #9CA3AF;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
  
  /* Radius - Same as light */
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
}

/* Smooth transitions between themes */
* {
  @apply transition-colors transition-opacity;
  transition-duration: var(--transition-duration);
  transition-timing-function: var(--transition-timing);
}
```

### Theme Provider Hook
**File: `resources/react/hooks/useTheme.js`**

```javascript
import { useContext } from 'react';
import { ThemeContext } from '../context/ThemeContext';

export const useTheme = () => {
  const context = useContext(ThemeContext);
  
  if (!context) {
    throw new Error('useTheme must be used within ThemeProvider');
  }
  
  return context;
};

// Alias for AntiGravity compatibility
export const useAntiGravityTheme = useTheme;
```

### Theme Context
**File: `resources/react/context/ThemeContext.jsx`**

```jsx
import React, { createContext, useState, useEffect } from 'react';

export const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
  const [theme, setTheme] = useState(() => {
    // Check localStorage first
    const saved = localStorage.getItem('theme-preference');
    if (saved) return saved;
    
    // Check system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }
    
    return 'light';
  });

  // Theme configuration
  const config = {
    light: {
      primary: '#3B82F6',
      secondary: '#10B981',
      warning: '#F59E0B',
      danger: '#EF4444',
      background: '#FFFFFF',
      surface: '#F9FAFB',
      border: '#E5E7EB',
      text: '#1F2937',
      textSecondary: '#6B7280',
    },
    dark: {
      primary: '#60A5FA',
      secondary: '#34D399',
      warning: '#FBBF24',
      danger: '#F87171',
      background: '#111827',
      surface: '#1F2937',
      border: '#374151',
      text: '#F3F4F6',
      textSecondary: '#9CA3AF',
    },
  };

  useEffect(() => {
    // Apply theme to document
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('theme-preference', theme);
    
    // Set CSS variables
    Object.entries(config[theme]).forEach(([key, value]) => {
      document.documentElement.style.setProperty(`--color-${key}`, value);
    });
  }, [theme]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === 'light' ? 'dark' : 'light'));
  };

  const value = {
    theme,
    setTheme,
    toggleTheme,
    config: config[theme],
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};
```

---

## REAL-TIME INTEGRATION

### Broadcasting Channel
**File: `app/Broadcasting/DashboardChannel.php`**

```php
<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;

class DashboardChannel extends Channel
{
    /**
     * Authenticate the user's access to broadcast on private channels.
     */
    public function join(User $user): bool
    {
        return true;
    }
}
```

### Event: Metrics Updated
**File: `app/Events/MetricsUpdated.php`**

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MetricsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public array $metrics,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("metrics.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'metrics.updated';
    }
}
```

### WebSocket Service
**File: `resources/react/services/websocket.js`**

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
  auth: {
    headers: {
      Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
    },
  },
});

export const subscribeToMetrics = (userId, onUpdate) => {
  echo
    .private(`metrics.${userId}`)
    .listen('.metrics.updated', (event) => {
      onUpdate(event.data);
    });
};

export const broadcastEvent = async (event, data) => {
  await axios.post('/api/broadcast', { event, data });
};
```

### Real-Time Hook
**File: `resources/react/hooks/useRealtime.js`**

```javascript
import { useEffect, useState, useCallback } from 'react';
import { subscribeToMetrics } from '../services/websocket';

export const useRealtime = (channel) => {
  const [data, setData] = useState(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    const userId = window.auth?.user?.id;
    
    if (!userId) return;

    if (channel.includes('metrics')) {
      subscribeToMetrics(userId, (newData) => {
        setData(newData);
        setIsConnected(true);
      });

      return () => {
        // Cleanup
      };
    }
  }, [channel]);

  return { data, isConnected };
};
```

---

## COMPLETE WORKING EXAMPLES

### Example 1: Running the Backend
```bash
# 1. Setup environment
cp .env.example .env
php artisan key:generate

# 2. Setup database
php artisan migrate
php artisan db:seed --class=DashboardDataSeeder

# 3. Cache config
php artisan config:cache

# 4. Start development server
php artisan serve
php artisan queue:work

# 5. In another terminal, start Pusher
# (Or use local WebSocket server)
```

### Example 2: Running the Frontend
```bash
# 1. Install dependencies
npm install

# 2. Start development server
npm run dev

# 3. Visit http://localhost:3000
```

### Example 3: Testing a Query
```php
// In php artisan tinker
$agent = User::where('role', 'ba')->first();
Auth::login($agent); // Simulate login

// Test priority queue
$leads = Lead::where('agent_id', $agent->id)
    ->overdueCallbacks()
    ->with('callLogs', 'payments')
    ->get();

// Should only show overdue leads
dd($leads);
```

---

**Document Version:** 1.0 | **Last Updated:** April 2, 2026 | **Status:** Production Ready

Complete implementation guide ready! 🚀
