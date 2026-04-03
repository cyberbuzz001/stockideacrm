# 🚀 CRM DASHBOARD IMPLEMENTATION - QUICK REFERENCE GUIDE

**Quick Links & Implementation Order | AntiGravity + Stitch AI Integration**

---

## 📦 PHASE BREAKDOWN & EXECUTION ORDER

### PHASE 1: Project Setup (Day 1-2)
```bash
# Laravel Setup
composer create-project laravel/laravel crm-dashboard 12.0
cd crm-dashboard

# Install required packages
composer require laravel/sanctum spatie/laravel-permission pusher/pusher-http-php predis/predis laravel/telescope
composer require moneyphp/money

# Node.js / React Setup
npm install react react-dom axios zustand react-query
npm install -D tailwindcss postcss autoprefixer
npm install @shadcn/ui recharts framer-motion sonner
npm install @antigravity/theme-engine @antigravity/core
npm install @stitchai/sdk @stitchai/react next-themes

# Key Files to Create
config/dashboard.php          # Dashboard configuration
config/theme.php              # AntiGravity theme config
config/stitch.php             # Stitch AI configuration
database/migrations/          # Create all tables
```

**Time Estimate:** 2-3 hours

---

### PHASE 2: Database Design (Day 2-3)
```bash
# Create migrations in this order:
php artisan make:migration create_users_table
php artisan make:migration create_leads_table
php artisan make:migration create_call_logs_table
php artisan make:migration create_payments_table
php artisan make:migration create_interactions_table
php artisan make:migration create_audit_logs_table
php artisan make:migration create_dashboard_cache_table
php artisan make:migration create_notifications_table

# Run migrations
php artisan migrate

# Seed sample data
php artisan make:seeder UserSeeder
php artisan make:seeder LeadSeeder
php artisan make:seeder CallLogSeeder
php artisan make:seeder PaymentSeeder

php artisan db:seed
```

**Key Tables:**
- `users` → Agents, Managers, Admin, SBA
- `leads` → Client information
- `call_logs` → Call records
- `payments` → Payment transactions
- `interactions` → Lead interactions (notes, updates)
- `audit_logs` → System audit trail

**Time Estimate:** 2-3 hours

---

### PHASE 3: Theme System (Day 3)
```bash
# Setup AntiGravity Theme Engine
1. Configure config/theme.php with light/dark theme colors
2. Create resources/react/components/Layout/ThemeProvider.jsx
3. Create resources/react/context/ThemeContext.jsx
4. Setup Tailwind CSS variables (tailwind.config.js)
5. Create resources/react/styles/theme.css
6. Create resources/react/store/themeStore.js (Zustand)
7. Test theme switching in browser DevTools
```

**Key Files:**
```
config/theme.php
tailwind.config.js
resources/react/components/Layout/ThemeProvider.jsx
resources/react/context/ThemeContext.jsx
resources/react/styles/theme.css
```

**Time Estimate:** 2 hours

---

### PHASE 4: Core React Components (Day 4-5)
```
Priority Order:
1. Layout Components
   - Header.jsx
   - Sidebar.jsx
   - ThemeToggle.jsx
   - MobileNav.jsx

2. KPI Cards
   - StatCard.jsx
   - TrendIndicator.jsx
   - KPICardGrid.jsx

3. Reusable Components
   - DataTable.jsx (with sorting, filtering, pagination)
   - LoadingSkeleton.jsx
   - EmptyState.jsx
   - AlertBanner.jsx

4. Charts (Recharts)
   - RevenueChart.jsx
   - LeadDistributionChart.jsx
   - CallVolumeChart.jsx
   - ConversionFunnelChart.jsx

5. Forms & Modals
   - LeadDetailModal.jsx
   - CallLogModal.jsx
   - DateRangeFilter.jsx
   - PeriodFilter.jsx

6. Feeds
   - LeaderboardWidget.jsx
   - LatestCallsWidget.jsx
   - LiveBanner.jsx
   - FollowupPanel.jsx
```

**Time Estimate:** 3-4 days

---

### PHASE 5: Backend Metrics & APIs (Day 5-6)
```bash
# Create Controllers
php artisan make:controller AdminController
php artisan make:controller ManagerController
php artisan make:controller SBAController
php artisan make:controller BAController
php artisan make:controller MetricsController
php artisan make:controller AnalyticsController

# Create Services
php artisan make:service DashboardService
php artisan make:service MetricsService
php artisan make:service CacheService
php artisan make:service ReportService
php artisan make:service ThemeService
php artisan make:service StitchAIService

# Create Jobs for async updates
php artisan make:job UpdateDashboardMetrics
php artisan make:job GenerateReport
php artisan make:job CacheWarmup
```

**Implementation Order:**
1. AdminController → get all metrics
2. BAController → get personal metrics (agent-scoped)
3. ManagerController → get team metrics
4. SBAController → get team + personal metrics
5. MetricsService → Calculate all KPIs
6. CacheService → Cache metrics with TTL
7. API Routes → Define /api/metrics/* endpoints

**Time Estimate:** 3-4 days

---

### PHASE 6: Dashboard Pages (Day 7-8)
```
Build in Order:
1. BA Dashboard (Simplest - personal data only)
   components/Dashboard/BADashboard.jsx
   
2. Manager Dashboard (Team data)
   components/Dashboard/ManagerDashboard.jsx
   
3. SBA Dashboard (Team + Personal)
   components/Dashboard/SBADashboard.jsx
   
4. Admin Dashboard (Everything)
   components/Dashboard/AdminDashboard.jsx
```

Each dashboard needs:
- Period filter (Today/Week/Month/YTD)
- KPI cards (with trends)
- Multiple charts
- Data tables (with sorting/filtering)
- Real-time updates
- Alerts system

**Time Estimate:** 3-4 days

---

### PHASE 7: Stitch AI Integration (Day 9)
```bash
# Setup Stitch AI
1. Create config/stitch.php
2. Create app/Services/StitchAIService.php
3. Create resources/react/hooks/useStitchComponent.js
4. Create API routes for component generation
5. Test component generation with sample specs

# Endpoints to Create:
POST /api/stitch/generate-component
POST /api/stitch/generate-dashboard
POST /api/stitch/validate-component
```

**Usage Example:**
```php
// In controller
$stitchAI = new StitchAIService();
$component = $stitchAI->generateComponent([
    'name' => 'CustomStatCard',
    'purpose' => 'Display agent performance',
    'data' => ['calls', 'revenue', 'conversion'],
    'props' => ['data', 'onAction'],
]);
```

**Time Estimate:** 2 days

---

### PHASE 8: Real-Time Updates (Day 10)
```bash
# Setup Pusher/WebSocket
1. Install Laravel Echo & Pusher JS
2. Create Broadcasting Channels (app/Broadcasting/)
3. Create app/Events/ for metric updates
4. Create resources/react/services/websocket.js
5. Create resources/react/hooks/useRealtime.js
6. Setup WebSocket subscriptions in components

# Broadcasting Channels:
- DashboardChannel (private)
- AdminChannel (private)
- TeamChannel (private)
- AgentChannel (private)

# Events to Broadcast:
- MetricsUpdated
- LeadAssigned
- PaymentProcessed
- CallLogged
- LiveCallStarted
- FollowupReminder
```

**Time Estimate:** 2-3 days

---

### PHASE 9: Security & RBAC (Day 11)
```bash
# Setup Permissions
1. Install Spatie RBAC
2. Define permissions in database seeder
3. Create middleware for role checking
4. Create middleware for data access control
5. Add query scoping to models

# Permissions Matrix:
Admin:
  - viewAllDashboards
  - manageSystems
  - approvePayments
  - manageUsers

Manager:
  - viewTeamDashboard
  - approveTeamPayments
  - reassignLeads

SBA:
  - viewTeamDashboard
  - viewPersonalDashboard
  - manageTeamLeads

BA:
  - viewPersonalDashboard
  - managePeLandads
```

**Time Estimate:** 2 days

---

### PHASE 10: Testing & Optimization (Day 12-14)
```bash
# Testing
php artisan make:test AdminDashboardTest
php artisan make:test BADashboardTest
php artisan make:test MetricsServiceTest

# Run tests
php artisan test

# Performance Optimization
1. Add database indexes
2. Setup caching strategy
3. Optimize queries with eager loading
4. Minify/bundle frontend assets
5. Configure CDN for static files
6. Setup error tracking (Sentry)
```

**Time Estimate:** 3-4 days

---

### PHASE 11: Deployment (Day 15)
```bash
# Setup Production Environment
1. Configure .env.production
2. Setup database migrations on production
3. Clear caches and optimize
4. Configure WebSocket on production
5. Setup SSL/TLS certificates
6. Configure CI/CD pipeline

# Deployment Steps:
git push production main
php artisan migrate --force
php artisan optimize
php artisan cache:clear
npm run build
```

**Time Estimate:** 1-2 days

---

## 🎯 KEY DEVELOPMENT GUIDELINES

### ✅ DO's
```
✅ Use eager loading (with()) to prevent N+1 queries
✅ Cache metrics with role-specific TTLs (3-15 minutes)
✅ Implement proper error boundaries in React
✅ Use TypeScript/JSDoc for type safety
✅ Follow component composition patterns
✅ Use Zustand for global state management
✅ Implement proper loading/error states
✅ Test RBAC at every level
✅ Log all data access for audit trails
✅ Use CSS variables for theming (AntiGravity)
```

### ❌ DON'Ts
```
❌ Don't bypass role-based access checks
❌ Don't query data across roles (agent seeing other agent's data)
❌ Don't hardcode colors (use CSS variables)
❌ Don't forget error handling in async operations
❌ Don't leave console.logs in production code
❌ Don't make queries without pagination for large datasets
❌ Don't forget accessibility (ARIA labels, keyboard nav)
❌ Don't skip data validation (frontend + backend)
❌ Don't cache user-specific data globally
❌ Don't render unencrypted PII in logs
```

---

## 📊 METRICS CALCULATION FORMULAS

### KPI Calculations
```php
// Today's Free Trials
Lead::where('status', 'Free Trial')
    ->whereDate('created_at', Carbon::today())
    ->count();

// Today's Revenue
Payment::where('status', 'completed')
    ->whereDate('created_at', Carbon::today())
    ->sum('amount');

// Conversion Rate
(Converted Leads / Total Leads) * 100

// Active Trials
Lead::where('status', 'Free Trial')
    ->where('trial_end_date', '>=', Carbon::today())
    ->count();

// Overdue Follow-ups
Lead::whereIn('status', ['Follow Up', 'Callback'])
    ->where('callback_date', '<', Carbon::today())
    ->where('agent_id', Auth::id())
    ->count();

// Active Agents (Last 5 minutes)
User::where('role', 'agent')
    ->where('last_activity_at', '>=', now()->subMinutes(5))
    ->count();

// Paid Clients
Lead::whereIn('status', ['Paid', 'Subscribed', 'Active'])
    ->count();

// Team Revenue
Payment::where('status', 'completed')
    ->whereIn('agent_id', $teamAgentIds)
    ->whereBetween('created_at', [$dateFrom, $dateTo])
    ->sum('amount');
```

---

## 🎨 THEME CONFIGURATION TEMPLATE

**Light Mode:**
```json
{
  "primary": "#3B82F6",      // blue-600
  "secondary": "#10B981",    // green-500
  "warning": "#F59E0B",      // amber-500
  "danger": "#EF4444",       // red-500
  "success": "#10B981",      // green-500
  "background": "#FFFFFF",
  "surface": "#F9FAFB",      // slate-50
  "border": "#E5E7EB",       // slate-200
  "text": "#1F2937",         // slate-900
  "textSecondary": "#6B7280" // slate-500
}
```

**Dark Mode:**
```json
{
  "primary": "#60A5FA",      // blue-400
  "secondary": "#34D399",    // green-400
  "warning": "#FBBF24",      // amber-400
  "danger": "#F87171",       // red-400
  "success": "#34D399",      // green-400
  "background": "#111827",   // slate-900
  "surface": "#1F2937",      // slate-800
  "border": "#374151",       // slate-700
  "text": "#F3F4F6",         // slate-100
  "textSecondary": "#9CA3AF" // slate-400
}
```

---

## 🔐 SECURITY CHECKLIST

- [ ] All routes protected with auth middleware
- [ ] Role-based access checks on all endpoints
- [ ] Query filtering by agent_id for BA
- [ ] Query filtering by team for Manager/SBA
- [ ] CSRF protection on all forms
- [ ] Rate limiting on API endpoints
- [ ] Input validation on all endpoints
- [ ] Audit logging for data access
- [ ] Sensitive data encrypted at rest
- [ ] Session timeout after 30 minutes inactivity
- [ ] SSL/TLS on production
- [ ] Regular security audits scheduled
- [ ] Password hashing with bcrypt/Argon2
- [ ] API token refresh mechanism

---

## 📈 PERFORMANCE TARGETS

```
Page Load Time:     < 3 seconds
First Contentful Paint: < 1.5 seconds
Interactive Time:   < 3.5 seconds
Cache Hit Rate:     > 70%
API Response Time:  < 500ms
WebSocket Latency:  < 100ms
```

---

## 🐛 COMMON ISSUES & SOLUTIONS

### Issue 1: N+1 Query Problem
**Solution:**
```php
// ❌ Wrong
$leads = Lead::where('agent_id', Auth::id())->get();
foreach ($leads as $lead) {
    echo $lead->agent->name; // Extra query per lead!
}

// ✅ Correct
$leads = Lead::with('agent')->where('agent_id', Auth::id())->get();
foreach ($leads as $lead) {
    echo $lead->agent->name; // No extra queries
}
```

### Issue 2: Agent Seeing Other Agent's Data
**Solution:**
```php
// Add global scope in Lead model
public static function boot()
{
    parent::boot();
    
    if (Auth::user()?->role === 'ba') {
        static::addGlobalScope(fn($q) => $q->where('agent_id', Auth::id()));
    }
}
```

### Issue 3: Theme Not Applying
**Solution:**
```javascript
// Ensure CSS variables are set in root
document.documentElement.style.setProperty('--color-primary', '#3B82F6');

// And Tailwind config references them
theme: {
  colors: {
    primary: 'var(--color-primary)',
  }
}
```

### Issue 4: Real-Time Updates Not Working
**Solution:**
```javascript
// Ensure Pusher is configured
echo.private(`metrics.${userId}`).listen('MetricsUpdated', (event) => {
    console.log('Real-time update:', event);
});

// Check Pusher credentials in .env
PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
```

---

## 📞 SUPPORT & RESOURCES

**Official Documentation:**
- Laravel: https://laravel.com/docs
- React: https://react.dev
- Tailwind: https://tailwindcss.com/docs
- Recharts: https://recharts.org/
- AntiGravity: https://antigravity.dev (assumed)
- Stitch AI: https://stitchai.com (assumed)
- Pusher: https://pusher.com/docs

**Useful Commands:**
```bash
# Development
npm run dev
php artisan serve
php artisan queue:work
php artisan tinker

# Testing
php artisan test
npm run test

# Production
npm run build
php artisan optimize
php artisan cache:clear
```

---

**Last Updated:** April 2, 2026 | **Version:** 2.0
