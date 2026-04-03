# 📊 COMPLETE STOCK ADVISORY CRM DASHBOARD PROMPT
## Advanced Dashboard System with AntiGravity + Stitch AI Integration

**Framework:** Laravel 12 | **UI:** React + Tailwind CSS + Shadcn/UI  
**Theme Engine:** AntiGravity Theme System | **AI Component Builder:** Stitch AI  
**Database:** PostgreSQL/MySQL | **Real-Time:** Pusher/WebSocket | **Caching:** Redis

---

## 🎯 PROJECT OBJECTIVE

Design and build a **production-grade, real-time, multi-role dashboard system** for a Stock Advisory CRM with:
- ✅ 4 role-based dashboards (Admin, Manager, SBA/TL, BA/Agent)
- ✅ Advanced light/dark theming with system preference detection
- ✅ AI-assisted component generation using Stitch AI
- ✅ AntiGravity Theme System for dynamic styling
- ✅ Real-time metrics & WebSocket updates
- ✅ Responsive mobile-first design with accessibility compliance
- ✅ Advanced filtering, search, and analytics
- ✅ Enterprise-grade security & audit logging

---

## 📋 PHASE 1: PROJECT SETUP & INFRASTRUCTURE

### 1.1 Technology Stack

#### Backend (Laravel 12)
```bash
# Required Packages
composer require laravel/framework ^12.0
composer require laravel/sanctum          # API authentication
composer require spatie/laravel-permission # RBAC
composer require pusher/pusher-http-php    # Real-time updates
composer require predis/predis             # Redis client
composer require laravel/telescope         # Debugging & monitoring
composer require spatie/laravel-query-monitor # Query monitoring
composer require laravel/pulse             # Application metrics
composer require moneyphp/money             # Money/currency handling
```

#### Frontend (React + Tailwind)
```bash
# Node dependencies
npm install react@latest react-dom@latest
npm install -D tailwindcss postcss autoprefixer
npm install -D @tailwindcss/forms @tailwindcss/typography
npm install @shadcn/ui                    # Component library
npm install recharts                      # Charts & graphs
npm install zustand                       # State management
npm install axios                         # HTTP client
npm install date-fns                      # Date utilities
npm install clsx tailwind-merge           # Conditional styling
npm install framer-motion                 # Animations
npm install next-themes                   # Theme management
npm install zustand                       # Lightweight state
npm install react-query                   # Server state
npm install sonner                        # Toast notifications
```

#### AntiGravity + Stitch AI Integration
```bash
# Install AntiGravity theme engine
npm install @antigravity/theme-engine @antigravity/core

# Install Stitch AI SDK
npm install @stitchai/sdk @stitchai/react
```

### 1.2 Project Structure

```
laravel-crm/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php       # Base dashboard logic
│   │   │   ├── AdminController.php           # Admin dashboard
│   │   │   ├── ManagerController.php         # Manager dashboard
│   │   │   ├── SBAController.php             # SBA/TL dashboard
│   │   │   ├── BAController.php              # BA/Agent dashboard
│   │   │   ├── MetricsController.php         # Real-time metrics API
│   │   │   ├── AnalyticsController.php       # Advanced analytics
│   │   │   └── ReportController.php          # Report generation
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php            # Role-based access
│   │   │   ├── DataAccessMiddleware.php      # Data isolation by role
│   │   │   └── AuditLoggingMiddleware.php    # Audit trails
│   │   └── Resources/
│   │       ├── LeadResource.php
│   │       ├── PaymentResource.php
│   │       └── CallLogResource.php
│   ├── Models/
│   │   ├── Lead.php
│   │   ├── CallLog.php
│   │   ├── Payment.php
│   │   ├── User.php
│   │   ├── AuditLog.php
│   │   └── DashboardCache.php
│   ├── Services/
│   │   ├── DashboardService.php             # Metrics calculation
│   │   ├── MetricsService.php               # KPI computations
│   │   ├── CacheService.php                 # Cache management
│   │   ├── ReportService.php                # Report generation
│   │   ├── AnalyticsService.php             # Analytics engine
│   │   └── ThemeService.php                 # Theme configuration
│   ├── Broadcasting/
│   │   └── DashboardChannel.php             # WebSocket channels
│   └── Jobs/
│       ├── UpdateDashboardMetrics.php        # Async metric updates
│       ├── GenerateReport.php
│       └── CacheWarmup.php
├── database/
│   ├── migrations/
│   │   ├── create_leads_table.php
│   │   ├── create_call_logs_table.php
│   │   ├── create_payments_table.php
│   │   ├── create_audit_logs_table.php
│   │   └── create_dashboard_cache_table.php
│   └── seeders/
│       ├── LeadSeeder.php
│       ├── UserSeeder.php
│       └── SampleDataSeeder.php
├── routes/
│   ├── web.php                              # Web routes
│   ├── api.php                              # API routes (metrics)
│   └── channels.php                         # WebSocket channels
├── resources/
│   ├── react/
│   │   ├── App.jsx
│   │   ├── components/
│   │   │   ├── Layout/
│   │   │   │   ├── Sidebar.jsx
│   │   │   │   ├── Header.jsx
│   │   │   │   ├── ThemeToggle.jsx
│   │   │   │   └── MobileNav.jsx
│   │   │   ├── Dashboard/
│   │   │   │   ├── AdminDashboard.jsx       # Admin role dashboard
│   │   │   │   ├── ManagerDashboard.jsx     # Manager role dashboard
│   │   │   │   ├── SBADashboard.jsx         # SBA/TL role dashboard
│   │   │   │   └── BADashboard.jsx          # BA/Agent role dashboard
│   │   │   ├── KPICards/
│   │   │   │   ├── StatCard.jsx             # Reusable stat card
│   │   │   │   ├── TrendIndicator.jsx
│   │   │   │   └── KPICardGrid.jsx
│   │   │   ├── Charts/
│   │   │   │   ├── RevenueChart.jsx
│   │   │   │   ├── LeadDistributionChart.jsx
│   │   │   │   ├── CallVolumeChart.jsx
│   │   │   │   ├── ConversionFunnelChart.jsx
│   │   │   │   └── HeatmapChart.jsx
│   │   │   ├── Tables/
│   │   │   │   ├── DataTable.jsx            # Generic sortable/filterable table
│   │   │   │   ├── LeadsTable.jsx
│   │   │   │   ├── AgentPerformanceTable.jsx
│   │   │   │   ├── PaymentApprovalsTable.jsx
│   │   │   │   └── CallLogsTable.jsx
│   │   │   ├── Feeds/
│   │   │   │   ├── LeaderboardWidget.jsx
│   │   │   │   ├── LatestCallsWidget.jsx
│   │   │   │   ├── LiveBanner.jsx
│   │   │   │   ├── FollowupPanel.jsx
│   │   │   │   └── TargetProgressBar.jsx
│   │   │   ├── Alerts/
│   │   │   │   ├── AlertBanner.jsx
│   │   │   │   ├── NotificationCenter.jsx
│   │   │   │   └── AlertTypes.jsx
│   │   │   ├── Filters/
│   │   │   │   ├── DateRangeFilter.jsx
│   │   │   │   ├── PeriodFilter.jsx
│   │   │   │   ├── AdvancedFilter.jsx
│   │   │   │   └── SavedFilters.jsx
│   │   │   ├── Modals/
│   │   │   │   ├── LeadDetailModal.jsx
│   │   │   │   ├── CallLogModal.jsx
│   │   │   │   ├── PaymentApprovalModal.jsx
│   │   │   │   └── ReportGeneratorModal.jsx
│   │   │   └── Common/
│   │   │       ├── LoadingSkeleton.jsx
│   │   │       ├── EmptyState.jsx
│   │   │       ├── ErrorBoundary.jsx
│   │   │       └── ConfirmDialog.jsx
│   │   ├── hooks/
│   │   │   ├── useDashboardData.js          # Fetch dashboard metrics
│   │   │   ├── useTheme.js                  # Theme context hook
│   │   │   ├── useRealtime.js               # WebSocket hook
│   │   │   ├── useFilters.js                # Filter state management
│   │   │   ├── usePermissions.js            # Role-based access
│   │   │   └── usePagination.js             # Pagination logic
│   │   ├── context/
│   │   │   ├── ThemeContext.jsx             # Light/dark/system theme
│   │   │   ├── AuthContext.jsx              # Authentication context
│   │   │   ├── RealtimeContext.jsx          # WebSocket context
│   │   │   └── NotificationContext.jsx      # Toast notifications
│   │   ├── services/
│   │   │   ├── api.js                       # API client with interceptors
│   │   │   ├── websocket.js                 # WebSocket service
│   │   │   ├── theme.js                     # AntiGravity theme service
│   │   │   ├── cache.js                     # Local cache service
│   │   │   └── analytics.js                 # Analytics tracking
│   │   ├── store/
│   │   │   ├── authStore.js                 # Zustand auth store
│   │   │   ├── metricsStore.js              # Zustand metrics store
│   │   │   ├── uiStore.js                   # UI state (modals, filters)
│   │   │   └── themeStore.js                # Theme settings store
│   │   ├── utils/
│   │   │   ├── formatters.js                # Format numbers, dates, currency
│   │   │   ├── validators.js                # Input validation
│   │   │   ├── colors.js                    # Color utilities
│   │   │   ├── theme-utils.js               # AntiGravity theme utilities
│   │   │   └── stitch-utils.js              # Stitch AI utilities
│   │   ├── styles/
│   │   │   ├── globals.css                  # Global styles
│   │   │   ├── theme.css                    # AntiGravity theme variables
│   │   │   ├── animations.css               # Custom animations
│   │   │   └── responsive.css               # Responsive utilities
│   │   └── pages/
│   │       ├── DashboardPage.jsx
│   │       ├── AnalyticsPage.jsx
│   │       ├── SettingsPage.jsx
│   │       └── ReportsPage.jsx
│   └── views/
│       └── dashboard.blade.php              # Root Blade template
└── config/
    ├── dashboard.php                        # Dashboard configuration
    ├── theme.php                            # AntiGravity theme config
    └── stitch.php                           # Stitch AI config
```

---

## 🎨 PHASE 2: ANTIGRAVITY THEME SYSTEM SETUP

### 2.1 AntiGravity Configuration

**File: `config/theme.php`**
```php
<?php

return [
    'engine' => 'antigravity',
    
    'themes' => [
        'light' => [
            'name' => 'Light Mode',
            'primary' => '#3B82F6',      // blue-600
            'secondary' => '#10B981',    // green-500
            'warning' => '#F59E0B',      // amber-500
            'danger' => '#EF4444',       // red-500
            'success' => '#10B981',      // green-500
            'info' => '#3B82F6',         // blue-500
            
            'background' => '#FFFFFF',
            'surface' => '#F9FAFB',      // slate-50
            'border' => '#E5E7EB',       // slate-200
            'text' => '#1F2937',         // slate-900
            'textSecondary' => '#6B7280',// slate-500
            
            'shadows' => [
                'sm' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'md' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                'lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
            ],
            
            'radius' => [
                'sm' => '0.375rem',
                'md' => '0.5rem',
                'lg' => '0.75rem',
            ],
        ],
        
        'dark' => [
            'name' => 'Dark Mode',
            'primary' => '#60A5FA',      // blue-400
            'secondary' => '#34D399',    // green-400
            'warning' => '#FBBF24',      // amber-400
            'danger' => '#F87171',       // red-400
            'success' => '#34D399',      // green-400
            'info' => '#60A5FA',         // blue-400
            
            'background' => '#111827',   // slate-900
            'surface' => '#1F2937',      // slate-800
            'border' => '#374151',       // slate-700
            'text' => '#F3F4F6',         // slate-100
            'textSecondary' => '#9CA3AF',// slate-400
            
            'shadows' => [
                'sm' => '0 1px 2px 0 rgba(0, 0, 0, 0.3)',
                'md' => '0 4px 6px -1px rgba(0, 0, 0, 0.4)',
                'lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.5)',
            ],
            
            'radius' => [
                'sm' => '0.375rem',
                'md' => '0.5rem',
                'lg' => '0.75rem',
            ],
        ],
    ],
    
    'system_preference' => true,  // Use system light/dark preference
    'persist_preference' => true, // Store user preference
    'transition_duration' => 300, // Theme transition in ms
];
```

### 2.2 AntiGravity React Integration

**File: `resources/react/components/Layout/ThemeProvider.jsx`**
```jsx
import React, { createContext, useContext, useEffect, useState } from 'react';
import { AntiGravityTheme } from '@antigravity/theme-engine';
import { useThemeStore } from '../../store/themeStore';

const AntiGravityContext = createContext();

export const AntiGravityProvider = ({ children }) => {
  const { theme, systemTheme } = useThemeStore();
  const [currentTheme, setCurrentTheme] = useState('light');
  const [themeConfig, setThemeConfig] = useState(null);

  useEffect(() => {
    // Determine active theme
    let activeTheme = theme;
    if (theme === 'system') {
      activeTheme = systemTheme || 'light';
    }
    setCurrentTheme(activeTheme);

    // Initialize AntiGravity with theme config
    const config = fetchThemeConfig(activeTheme);
    setThemeConfig(config);
    
    // Apply CSS variables
    applyThemeCSSVariables(config);
    
    // Update document class
    document.documentElement.classList.toggle('dark', activeTheme === 'dark');
  }, [theme, systemTheme]);

  const fetchThemeConfig = (themeName) => {
    // Fetch from API or use bundled config
    const themes = {
      light: window.__THEME_CONFIG__?.light,
      dark: window.__THEME_CONFIG__?.dark,
    };
    return themes[themeName];
  };

  const applyThemeCSSVariables = (config) => {
    const root = document.documentElement;
    Object.entries(config).forEach(([key, value]) => {
      root.style.setProperty(`--color-${key}`, value);
    });
  };

  return (
    <AntiGravityContext.Provider value={{ theme: currentTheme, config: themeConfig }}>
      {children}
    </AntiGravityContext.Provider>
  );
};

export const useAntiGravityTheme = () => {
  const context = useContext(AntiGravityContext);
  if (!context) {
    throw new Error('useAntiGravityTheme must be used within AntiGravityProvider');
  }
  return context;
};
```

### 2.3 Tailwind CSS Configuration with AntiGravity Variables

**File: `tailwind.config.js`**
```javascript
/** @type {import('tailwindcss').Config} */
export default {
  darkMode: ['class'],
  content: [
    './resources/react/**/*.{js,jsx,ts,tsx}',
    './resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        // Map CSS variables to Tailwind
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        warning: 'var(--color-warning)',
        danger: 'var(--color-danger)',
        success: 'var(--color-success)',
        info: 'var(--color-info)',
        background: 'var(--color-background)',
        surface: 'var(--color-surface)',
        border: 'var(--color-border)',
        text: 'var(--color-text)',
        'text-secondary': 'var(--color-textSecondary)',
      },
      boxShadow: {
        'sm': 'var(--shadow-sm)',
        'md': 'var(--shadow-md)',
        'lg': 'var(--shadow-lg)',
      },
      borderRadius: {
        'sm': 'var(--radius-sm)',
        'md': 'var(--radius-md)',
        'lg': 'var(--radius-lg)',
      },
      animation: {
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'slide-in': 'slideIn 0.3s ease-out',
        'fade-in': 'fadeIn 0.2s ease-out',
      },
      keyframes: {
        slideIn: {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    // Custom plugin for smooth transitions
    function ({ addUtilities }) {
      addUtilities({
        '.transition-theme': {
          '@apply transition-colors transition-opacity duration-300': {},
        },
      });
    },
  ],
};
```

---

## 🤖 PHASE 3: STITCH AI INTEGRATION FOR COMPONENT GENERATION

### 3.1 Stitch AI Configuration

**File: `config/stitch.php`**
```php
<?php

return [
    'enabled' => env('STITCH_AI_ENABLED', true),
    
    'api_key' => env('STITCH_AI_API_KEY'),
    
    'api_endpoint' => env('STITCH_AI_ENDPOINT', 'https://api.stitchai.com/v1'),
    
    'models' => [
        'component_generation' => 'stitch-ui-v1',
        'theme_adaptation' => 'stitch-theme-v1',
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 86400, // 24 hours
    ],
    
    'generation_config' => [
        'framework' => 'react',
        'ui_library' => 'shadcn',
        'css_framework' => 'tailwind',
        'theme_engine' => 'antigravity',
        'include_accessibility' => true,
        'include_animations' => true,
        'mobile_first' => true,
    ],
];
```

### 3.2 Stitch AI Service Layer

**File: `app/Services/StitchAIService.php`**
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StitchAIService
{
    private string $apiKey;
    private string $apiEndpoint;
    private array $config;

    public function __construct()
    {
        $this->apiKey = config('stitch.api_key');
        $this->apiEndpoint = config('stitch.api_endpoint');
        $this->config = config('stitch.generation_config');
    }

    /**
     * Generate a React component based on specifications
     */
    public function generateComponent(array $specifications): array
    {
        $cacheKey = 'stitch_component_' . md5(json_encode($specifications));

        if (config('stitch.cache.enabled') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $prompt = $this->buildComponentPrompt($specifications);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->apiEndpoint}/generate/component", [
            'prompt' => $prompt,
            'specifications' => $specifications,
            'config' => $this->config,
        ]);

        if ($response->failed()) {
            throw new \Exception('Stitch AI component generation failed: ' . $response->body());
        }

        $result = $response->json();

        Cache::put($cacheKey, $result, config('stitch.cache.ttl'));

        return $result;
    }

    /**
     * Generate theme-adapted components
     */
    public function generateThemedComponent(array $specifications, string $theme): array
    {
        $specifications['theme'] = $theme;
        
        return $this->generateComponent($specifications);
    }

    /**
     * Generate a complete dashboard layout
     */
    public function generateDashboard(string $role, array $config): array
    {
        $cacheKey = "stitch_dashboard_{$role}";

        if (config('stitch.cache.enabled') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $prompt = $this->buildDashboardPrompt($role, $config);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->post("{$this->apiEndpoint}/generate/dashboard", [
            'role' => $role,
            'prompt' => $prompt,
            'config' => array_merge($this->config, $config),
            'metrics' => $config['metrics'] ?? [],
            'layout' => $config['layout'] ?? 'default',
        ]);

        if ($response->failed()) {
            throw new \Exception('Stitch AI dashboard generation failed');
        }

        $result = $response->json();

        Cache::put($cacheKey, $result, config('stitch.cache.ttl'));

        return $result;
    }

    /**
     * Build component generation prompt
     */
    private function buildComponentPrompt(array $specs): string
    {
        return <<<PROMPT
        Generate a React component with the following specifications:
        
        Component Name: {$specs['name']}
        Purpose: {$specs['purpose']}
        Data: {$specs['data']}
        Props: {$specs['props']}
        
        Requirements:
        - Framework: React (functional component with hooks)
        - UI Library: Shadcn/UI components
        - Styling: Tailwind CSS with CSS variables for theming
        - Theme Support: Light/Dark modes using CSS variables
        - Accessibility: WCAG 2.1 AA compliant (ARIA labels, semantic HTML)
        - Responsiveness: Mobile-first approach
        - Animations: Smooth transitions using Framer Motion
        - Error Handling: Proper error boundaries and fallbacks
        - Loading States: Skeleton loaders
        - Type Safety: PropTypes or JSDoc comments
        
        Code Style:
        - Use modern React hooks (useState, useContext, useCallback)
        - Implement proper error boundaries
        - Include component documentation
        - Export as default export
        
        Provide the complete, production-ready component code.
        PROMPT;
    }

    /**
     * Build dashboard generation prompt
     */
    private function buildDashboardPrompt(string $role, array $config): string
    {
        $metrics = implode(', ', array_keys($config['metrics'] ?? []));
        
        return <<<PROMPT
        Generate a complete {$role} Dashboard layout with the following specifications:
        
        Role: {$role}
        Metrics: {$metrics}
        Features: {$config['features']}
        
        Requirements:
        - Multi-section layout (KPI Cards, Charts, Tables, Feeds)
        - Real-time data updates via WebSocket
        - Advanced filtering and date range selection
        - Responsive grid layout (4-col desktop, 2-col tablet, 1-col mobile)
        - Theme switching (light/dark modes)
        - Loading skeletons for data
        - Empty states for no data
        - Error handling with retry logic
        
        Components to Include:
        - KPI stat cards with trend indicators
        - Multi-series charts (line, bar, doughnut)
        - Sortable/filterable data tables
        - Real-time feed widgets
        - Action buttons and modals
        - Notification system
        
        Provide complete, production-ready JSX code with all hooks and services.
        PROMPT;
    }

    /**
     * Validate generated component
     */
    public function validateComponent(string $code): bool
    {
        // Validate JSX syntax
        // Check for required imports
        // Verify component exports
        // Check accessibility requirements
        
        return true;
    }
}
```

### 3.3 Stitch AI React Hook

**File: `resources/react/hooks/useStitchComponent.js`**
```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useStitchComponent = (specifications) => {
  const [component, setComponent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const generateComponent = async () => {
      try {
        setLoading(true);
        const response = await axios.post('/api/stitch/generate-component', {
          specifications,
        });
        
        setComponent(response.data.component);
        setError(null);
      } catch (err) {
        setError(err.message);
        setComponent(null);
      } finally {
        setLoading(false);
      }
    };

    generateComponent();
  }, [specifications]);

  return { component, loading, error };
};

export const useStitchDashboard = (role, config) => {
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const generateDashboard = async () => {
      try {
        setLoading(true);
        const response = await axios.post('/api/stitch/generate-dashboard', {
          role,
          config,
        });
        
        setDashboard(response.data.dashboard);
        setError(null);
      } catch (err) {
        setError(err.message);
        setDashboard(null);
      } finally {
        setLoading(false);
      }
    };

    generateDashboard();
  }, [role, config]);

  return { dashboard, loading, error };
};
```

---

## 🏗️ PHASE 4: BACKEND ARCHITECTURE

### 4.1 Dashboard Controller Base Class

**File: `app/Http/Controllers/DashboardController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

abstract class DashboardController extends Controller
{
    protected DashboardService $dashboardService;
    protected CacheService $cacheService;
    protected int $cacheTTL = 300; // Override in child classes

    public function __construct(
        DashboardService $dashboardService,
        CacheService $cacheService
    ) {
        $this->dashboardService = $dashboardService;
        $this->cacheService = $cacheService;
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Get dashboard data with caching
     */
    protected function getDashboardData(string $role): array
    {
        $cacheKey = "dashboard_{$role}_" . Auth::id();

        return $this->cacheService->remember($cacheKey, $this->cacheTTL, function () use ($role) {
            return match ($role) {
                'admin' => $this->getAdminMetrics(),
                'manager' => $this->getManagerMetrics(),
                'sba' => $this->getSBAMetrics(),
                'ba' => $this->getBAMetrics(),
                default => [],
            };
        });
    }

    /**
     * Get period-based metrics
     */
    protected function getPeriod(): string
    {
        return request()->query('period', 'today');
    }

    /**
     * Get date range based on period
     */
    protected function getDateRange(): array
    {
        $period = $this->getPeriod();
        
        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'ytd' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    /**
     * Abstract methods to be implemented by child classes
     */
    abstract protected function getAdminMetrics(): array;
    abstract protected function getManagerMetrics(): array;
    abstract protected function getSBAMetrics(): array;
    abstract protected function getBAMetrics(): array;
}
```

### 4.2 Admin Dashboard Controller

**File: `app/Http/Controllers/AdminController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AdminController extends DashboardController
{
    protected int $cacheTTL = 300; // 5 minutes for admin

    public function index()
    {
        $this->authorize('viewAdminDashboard', Auth::user());

        $metrics = $this->getDashboardData('admin');
        $period = $this->getPeriod();

        return inertia('Dashboard/AdminDashboard', [
            'metrics' => $metrics,
            'period' => $period,
            'user' => Auth::user()->only('name', 'email', 'role'),
        ]);
    }

    protected function getAdminMetrics(): array
    {
        [$dateFrom, $dateTo] = $this->getDateRange();

        return [
            // KPI Metrics
            'kpis' => [
                'free_trials_today' => $this->getFreyTrialsToday(),
                'calling_activity' => $this->getCallingActivityToday(),
                'revenue_today' => $this->getRevenueToday(),
                'active_agents' => $this->getActiveAgents(),
                'paid_clients' => $this->getPaidClientsCount(),
            ],

            // Additional KPIs
            'extended_kpis' => [
                'total_leads' => Lead::count(),
                'conversion_rate' => $this->getConversionRate(),
                'avg_call_duration' => $this->getAvgCallDuration(),
                'pending_approvals' => $this->getPendingApprovalsCount(),
                'system_uptime' => $this->getSystemUptime(),
            ],

            // Charts Data
            'charts' => [
                'revenue_trend' => $this->getRevenueTrend(),
                'lead_distribution' => $this->getLeadDistribution(),
                'calls_vs_conversions' => $this->getCallsVsConversions(),
                'team_heatmap' => $this->getTeamHeatmap(),
                'payment_pipeline' => $this->getPaymentPipeline(),
            ],

            // Tables Data
            'tables' => [
                'today_free_trials' => $this->getTodayFreeTrials(),
                'top_agents' => $this->getTopRevenueAgents(),
                'agent_status' => $this->getAgentRealTimeStatus(),
                'pending_approvals' => $this->getPendingApprovals(),
                'system_logs' => $this->getSystemEventsLog(),
            ],

            // Feeds
            'feeds' => [
                'leaderboard' => $this->getLeaderboard(),
                'latest_calls' => $this->getLatestCalls(),
                'live_banner' => $this->getLiveBannerData(),
                'overdue_followups' => $this->getOverdueFollowups('all'),
                'pending_payments' => $this->getPendingPayments(),
            ],
        ];
    }

    private function getFreyTrialsToday(): array
    {
        $count = Lead::where('status', 'Free Trial')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $yesterday = Lead::where('status', 'Free Trial')
            ->whereDate('created_at', Carbon::yesterday())
            ->count();

        return [
            'count' => $count,
            'trend' => $this->calculateTrend($count, $yesterday),
        ];
    }

    private function getCallingActivityToday(): array
    {
        $count = CallLog::whereDate('created_at', Carbon::today())
            ->count();

        $yesterday = CallLog::whereDate('created_at', Carbon::yesterday())
            ->count();

        $duration = CallLog::whereDate('created_at', Carbon::today())
            ->sum('duration_seconds');

        return [
            'count' => $count,
            'trend' => $this->calculateTrend($count, $yesterday),
            'total_duration' => $this->formatDuration($duration),
        ];
    }

    private function getRevenueToday(): array
    {
        $revenue = Payment::where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $yesterday = Payment::where('status', 'completed')
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('amount');

        return [
            'amount' => $revenue,
            'currency' => '₹',
            'trend' => $this->calculateTrend($revenue, $yesterday),
        ];
    }

    private function getActiveAgents(): array
    {
        $count = User::where('role', 'agent')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->count();

        return [
            'count' => $count,
            'status_indicator' => $count > 0 ? 'online' : 'offline',
        ];
    }

    private function getPaidClientsCount(): int
    {
        return Lead::whereIn('status', ['Paid', 'Subscribed', 'Active'])
            ->count();
    }

    // Additional helper methods...
    private function calculateTrend($current, $previous): string
    {
        if ($previous == 0) return $current > 0 ? '↑' : '→';
        $percentage = (($current - $previous) / $previous) * 100;
        return $percentage > 0 ? "↑ {$percentage}%" : "↓ {$percentage}%";
    }

    private function formatDuration($seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return "{$hours}h {$minutes}m";
    }

    // ... implement all other metric methods
}
```

### 4.3 BA/Agent Dashboard Controller

**File: `app/Http/Controllers/BAController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\CallLog;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BAController extends DashboardController
{
    protected int $cacheTTL = 180; // 3 minutes for BA (real-time needed)

    public function index()
    {
        $this->authorize('viewBADashboard', Auth::user());

        $metrics = $this->getDashboardData('ba');
        $period = $this->getPeriod();

        return inertia('Dashboard/BADashboard', [
            'metrics' => $metrics,
            'period' => $period,
            'user' => Auth::user()->only('name', 'email', 'daily_target'),
        ]);
    }

    protected function getBAMetrics(): array
    {
        $agentId = Auth::id();

        return [
            // KPI Metrics (All Personal)
            'kpis' => [
                'total_leads' => $this->getTotalLeads($agentId),
                'new_leads' => $this->getNewLeads($agentId),
                'calls_today' => $this->getCallsToday($agentId),
                'followups_due' => $this->getFollowupsDue($agentId),
                'active_trials' => $this->getActiveTrials($agentId),
                'paid_clients' => $this->getPaidClients($agentId),
            ],

            // Extended Metrics
            'extended_metrics' => [
                'call_duration_today' => $this->getCallDurationToday($agentId),
                'conversion_rate' => $this->getConversionRate($agentId),
                'avg_lead_age' => $this->getAvgLeadAge($agentId),
                'period_revenue' => $this->getPeriodRevenue($agentId),
            ],

            // Charts (Personal)
            'charts' => [
                'call_volume' => $this->getCallVolumeChart($agentId),
                'conversion_funnel' => $this->getConversionFunnel($agentId),
                'revenue_trend' => $this->getRevenueTrendChart($agentId),
                'call_timeline' => $this->getCallTimeline($agentId),
            ],

            // Tables (Personal Only)
            'tables' => [
                'priority_queue' => $this->getPriorityQueue($agentId),
                'upcoming_followups' => $this->getUpcomingFollowups($agentId),
                'overdue_followups' => $this->getOverdueFollowups($agentId),
                'active_trials' => $this->getActiveTrialsTable($agentId),
                'paid_clients_list' => $this->getPaidClientsList($agentId),
                'cold_leads' => $this->getColdLeads($agentId),
                'call_logs_today' => $this->getCallLogsToday($agentId),
            ],

            // Feeds (Common)
            'feeds' => [
                'leaderboard' => $this->getLeaderboard(),
                'latest_calls' => $this->getLatestCalls(),
                'live_banner' => $this->getLiveBannerData(),
                'target_progress' => $this->getTargetProgress($agentId),
            ],

            // Alerts
            'alerts' => [
                'pending_training' => $this->getPendingTraining($agentId),
                'renewal_alerts' => $this->getRenewalAlerts($agentId),
                'call_target_alert' => $this->getCallTargetAlert($agentId),
                'conversion_alert' => $this->getConversionAlert($agentId),
            ],
        ];
    }

    /**
     * Priority Queue: Strict ordering by urgency
     * 1. Overdue Follow-ups (red)
     * 2. Today's Follow-ups (orange)
     * 3. New Leads Today (blue)
     * 4. Cold Leads (yellow)
     * 5. Active Trials (green)
     */
    private function getPriorityQueue(int $agentId): array
    {
        // Overdue follow-ups
        $overdueFollowups = Lead::where('agent_id', $agentId)
            ->whereIn('status', ['Follow Up', 'Callback'])
            ->where('callback_date', '<', Carbon::today())
            ->with(['client', 'interactions'])
            ->orderBy('callback_date', 'asc')
            ->limit(15)
            ->get();

        // Today's follow-ups
        $todaysFollowups = Lead::where('agent_id', $agentId)
            ->whereIn('status', ['Follow Up', 'Callback'])
            ->whereDate('callback_date', Carbon::today())
            ->with(['client', 'interactions'])
            ->orderBy('callback_date', 'asc')
            ->limit(15 - count($overdueFollowups))
            ->get();

        // New leads today
        $newLeads = Lead::where('agent_id', $agentId)
            ->whereDate('created_at', Carbon::today())
            ->with(['client', 'interactions'])
            ->orderBy('created_at', 'desc')
            ->limit(15 - count($overdueFollowups) - count($todaysFollowups))
            ->get();

        // Cold leads (not contacted in 7+ days)
        $coldLeads = Lead::where('agent_id', $agentId)
            ->where('last_contact_at', '<', now()->subDays(7))
            ->with(['client', 'interactions'])
            ->orderBy('last_contact_at', 'asc')
            ->limit(15 - count($overdueFollowups) - count($todaysFollowups) - count($newLeads))
            ->get();

        // Active trials
        $trials = Lead::where('agent_id', $agentId)
            ->where('status', 'Free Trial')
            ->whereDate('trial_end_date', '>=', Carbon::today())
            ->with(['client', 'interactions'])
            ->orderBy('trial_end_date', 'asc')
            ->limit(15 - count($overdueFollowups) - count($todaysFollowups) - count($newLeads) - count($coldLeads))
            ->get();

        // Merge and format
        $queue = [];
        
        foreach ($overdueFollowups as $lead) {
            $queue[] = [
                ...$lead->toArray(),
                'priority' => 'overdue',
                'priority_color' => 'red',
                'days_overdue' => $lead->callback_date->diffInDays(Carbon::today()),
            ];
        }

        foreach ($todaysFollowups as $lead) {
            $queue[] = [
                ...$lead->toArray(),
                'priority' => 'today',
                'priority_color' => 'orange',
                'days_overdue' => 0,
            ];
        }

        foreach ($newLeads as $lead) {
            $queue[] = [
                ...$lead->toArray(),
                'priority' => 'new',
                'priority_color' => 'blue',
            ];
        }

        foreach ($coldLeads as $lead) {
            $queue[] = [
                ...$lead->toArray(),
                'priority' => 'cold',
                'priority_color' => 'yellow',
            ];
        }

        foreach ($trials as $lead) {
            $queue[] = [
                ...$lead->toArray(),
                'priority' => 'trial',
                'priority_color' => 'green',
                'days_remaining' => $lead->trial_end_date->diffInDays(Carbon::today()),
            ];
        }

        return array_slice($queue, 0, 15); // Return only first 15
    }

    // ... implement all other BA-specific methods
}
```

### 4.4 Metrics API Routes

**File: `routes/api.php`**
```php
<?php

use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // Real-time metrics endpoints
    Route::prefix('metrics')->group(function () {
        Route::get('/kpis/{role}', [MetricsController::class, 'getKPIs']);
        Route::get('/charts/{role}/{chartType}', [MetricsController::class, 'getChartData']);
        Route::get('/tables/{role}/{tableType}', [MetricsController::class, 'getTableData']);
    });

    // Stitch AI endpoints
    Route::prefix('stitch')->group(function () {
        Route::post('/generate-component', [StitchAIController::class, 'generateComponent']);
        Route::post('/generate-dashboard', [StitchAIController::class, 'generateDashboard']);
        Route::post('/validate-component', [StitchAIController::class, 'validateComponent']);
    });

    // WebSocket endpoints
    Route::prefix('realtime')->group(function () {
        Route::post('/subscribe/{channel}', [RealtimeController::class, 'subscribe']);
        Route::post('/unsubscribe/{channel}', [RealtimeController::class, 'unsubscribe']);
    });
});
```

---

## 🎨 PHASE 5: FRONTEND COMPONENTS

### 5.1 Reusable Stat Card Component

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
  variant = 'default',
  loading = false,
  className,
}) => {
  const { theme } = useAntiGravityTheme();

  return (
    <div
      onClick={onClick}
      className={clsx(
        'bg-surface border-2 border-border rounded-lg p-6',
        'transition-theme transition-all duration-300',
        'hover:shadow-lg hover:scale-105 cursor-pointer',
        'dark:bg-slate-800 dark:border-slate-700',
        size === 'lg' && 'p-8',
        variant === 'gradient' && `bg-gradient-to-br from-${color}-50 to-${color}-100`,
        loading && 'animate-pulse',
        className,
      )}
    >
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-text-secondary text-sm font-medium">{title}</p>
          
          {loading ? (
            <div className="h-8 bg-slate-200 rounded mt-2 w-32"></div>
          ) : (
            <div className="mt-2 flex items-baseline gap-2">
              <h3 className="text-3xl font-bold text-text">{value}</h3>
              {trend && <TrendIndicator trend={trend} />}
            </div>
          )}
        </div>

        {Icon && (
          <div
            className={clsx(
              'p-3 rounded-lg',
              `bg-${color}-100 text-${color}-600`,
              'dark:bg-slate-700 dark:text-slate-300',
            )}
          >
            <Icon size={24} />
          </div>
        )}
      </div>
    </div>
  );
};

export default StatCard;
```

### 5.2 Data Table Component with Sorting & Filtering

**File: `resources/react/components/Tables/DataTable.jsx`**
```jsx
import React, { useState, useMemo } from 'react';
import { ChevronUp, ChevronDown } from 'lucide-react';
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
  filters,
  className,
}) => {
  const { theme } = useAntiGravityTheme();
  const [sortConfig, setSortConfig] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [filterState, setFilterState] = useState(filters?.initial || {});

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
        return {
          key: columnKey,
          direction: prev.direction === 'asc' ? 'desc' : 'asc',
        };
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
    <div className={clsx('rounded-lg border border-border bg-surface', className)}>
      {title && (
        <div className="px-6 py-4 border-b border-border">
          <h3 className="text-lg font-semibold text-text">{title}</h3>
        </div>
      )}

      <div className="overflow-x-auto">
        <table className="w-full">
          <thead>
            <tr className="border-b border-border bg-slate-50 dark:bg-slate-800">
              {columns.map((column) => (
                <th
                  key={column.key}
                  onClick={() => column.sortable && handleSort(column.key)}
                  className={clsx(
                    'px-6 py-3 text-left text-sm font-semibold text-text-secondary',
                    column.sortable && 'cursor-pointer hover:text-text',
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
              // Loading skeleton
              Array.from({ length: 5 }).map((_, idx) => (
                <tr key={idx} className="border-b border-border">
                  {columns.map((_, colIdx) => (
                    <td key={colIdx} className="px-6 py-4">
                      <div className="h-4 bg-slate-200 rounded dark:bg-slate-700 w-3/4"></div>
                    </td>
                  ))}
                </tr>
              ))
            ) : paginatedData.length === 0 ? (
              <tr>
                <td colSpan={columns.length + (actions ? 1 : 0)} className="px-6 py-8 text-center">
                  <p className="text-text-secondary">No data available</p>
                </td>
              </tr>
            ) : (
              paginatedData.map((row, rowIdx) => (
                <tr
                  key={rowIdx}
                  onClick={() => onRowClick?.(row)}
                  className={clsx(
                    'border-b border-border transition-colors',
                    onRowClick && 'hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer',
                  )}
                >
                  {columns.map((column) => (
                    <td key={column.key} className="px-6 py-4 text-sm text-text">
                      {column.render ? column.render(row[column.key], row) : row[column.key]}
                    </td>
                  ))}
                  {actions && (
                    <td className="px-6 py-4 text-right">
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
        <div className="px-6 py-4 border-t border-border flex items-center justify-between">
          <p className="text-sm text-text-secondary">
            Page {currentPage} of {totalPages}
          </p>
          <div className="flex gap-2">
            <button
              onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
              disabled={currentPage === 1}
              className="px-3 py-1 rounded bg-primary text-white disabled:opacity-50"
            >
              Previous
            </button>
            <button
              onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
              disabled={currentPage === totalPages}
              className="px-3 py-1 rounded bg-primary text-white disabled:opacity-50"
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

### 5.3 Chart Components (Recharts Integration)

**File: `resources/react/components/Charts/RevenueChart.jsx`**
```jsx
import React from 'react';
import { LineChart, Line, AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { useAntiGravityTheme } from '../../hooks/useTheme';

export const RevenueChart = ({ data, period = '30d' }) => {
  const { config: themeConfig } = useAntiGravityTheme();

  return (
    <div className="bg-surface rounded-lg border border-border p-6">
      <h3 className="text-lg font-semibold text-text mb-4">Revenue Trend - Last {period}</h3>

      <ResponsiveContainer width="100%" height={300}>
        <AreaChart data={data}>
          <defs>
            <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="5%" stopColor={themeConfig?.primary} stopOpacity={0.8} />
              <stop offset="95%" stopColor={themeConfig?.primary} stopOpacity={0} />
            </linearGradient>
          </defs>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip
            contentStyle={{
              backgroundColor: themeConfig?.surface,
              borderColor: themeConfig?.border,
              color: themeConfig?.text,
            }}
          />
          <Area
            type="monotone"
            dataKey="revenue"
            stroke={themeConfig?.primary}
            fillOpacity={1}
            fill="url(#colorRevenue)"
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
};
```

### 5.4 Real-Time Dashboard Component

**File: `resources/react/components/Dashboard/BADashboard.jsx`**
```jsx
import React, { useEffect, useState } from 'react';
import { useDashboardData } from '../../hooks/useDashboardData';
import { useRealtime } from '../../hooks/useRealtime';
import StatCard from '../KPICards/StatCard';
import { DataTable } from '../Tables/DataTable';
import { RevenueChart } from '../Charts/RevenueChart';
import { PeriodFilter } from '../Filters/PeriodFilter';
import { LoadingSkeleton } from '../Common/LoadingSkeleton';
import { AlertBanner } from '../Alerts/AlertBanner';
import clsx from 'clsx';

export const BADashboard = () => {
  const { data, loading, error } = useDashboardData('ba');
  const { realtime } = useRealtime('dashboard:ba');
  const [period, setPeriod] = useState('today');

  useEffect(() => {
    if (realtime?.metrics) {
      // Update metrics in real-time
      console.log('Real-time update:', realtime.metrics);
    }
  }, [realtime]);

  if (loading) return <LoadingSkeleton role="ba" />;
  if (error) return <div className="text-red-500">Error: {error}</div>;

  const { metrics, alerts } = data;

  return (
    <div className="min-h-screen bg-background transition-theme">
      {/* Header */}
      <div className="bg-surface border-b border-border p-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-text">My Dashboard</h1>
            <p className="text-text-secondary mt-1">Welcome back, Business Advisor</p>
          </div>
          <PeriodFilter period={period} onChange={setPeriod} />
        </div>
      </div>

      {/* Alerts */}
      {alerts?.length > 0 && (
        <div className="p-6 space-y-3">
          {alerts.map((alert) => (
            <AlertBanner key={alert.id} {...alert} />
          ))}
        </div>
      )}

      {/* KPI Cards */}
      <div className="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {metrics.kpis && Object.entries(metrics.kpis).map(([key, kpi]) => (
          <StatCard
            key={key}
            title={kpi.label}
            value={kpi.value}
            trend={kpi.trend}
            icon={kpi.icon}
            color={kpi.color}
          />
        ))}
      </div>

      {/* Charts */}
      <div className="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <RevenueChart data={metrics.charts.revenue_trend} />
        {/* More charts */}
      </div>

      {/* Priority Queue Table */}
      <div className="p-6">
        <DataTable
          title="My Priority Queue"
          columns={[
            { key: 'name', label: 'Lead Name', sortable: true },
            { key: 'phone', label: 'Phone', sortable: false },
            { key: 'days_since_contact', label: 'Days Since Contact', sortable: true },
            { key: 'priority', label: 'Priority', sortable: true },
          ]}
          data={metrics.tables.priority_queue}
          pagination
        />
      </div>
    </div>
  );
};
```

---

## 🔄 PHASE 6: REAL-TIME UPDATES & WEBSOCKET

### 6.1 WebSocket Service

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

export const subscribeToMetrics = (userId, callback) => {
  echo.private(`metrics.${userId}`).listen('MetricsUpdated', (event) => {
    callback(event.data);
  });
};

export const subscribeToLiveCall = (callback) => {
  echo.channel('live-calls').listen('LiveCallStarted', (event) => {
    callback(event.data);
  });
};

export const subscribeToFollowupReminder = (userId, callback) => {
  echo.private(`followups.${userId}`).listen('FollowupReminder', (event) => {
    callback(event.data);
  });
};
```

### 6.2 Real-Time Hook

**File: `resources/react/hooks/useRealtime.js`**
```javascript
import { useEffect, useState } from 'react';
import { subscribeToMetrics, subscribeToLiveCall, subscribeToFollowupReminder } from '../services/websocket';

export const useRealtime = (channel) => {
  const [realtime, setRealtime] = useState(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    if (channel === 'dashboard:ba') {
      const userId = window.auth?.user?.id;
      subscribeToMetrics(userId, (data) => {
        setRealtime(data);
        setIsConnected(true);
      });
    }

    if (channel === 'live-calls') {
      subscribeToLiveCall((data) => {
        setRealtime(data);
      });
    }

    if (channel.includes('followups')) {
      const userId = channel.split(':')[1];
      subscribeToFollowupReminder(userId, (data) => {
        setRealtime(data);
      });
    }

    return () => {
      // Cleanup subscriptions
    };
  }, [channel]);

  return { realtime, isConnected };
};
```

---

## 📋 PHASE 7: SECURITY & PERFORMANCE

### 7.1 Data Access Middleware

**File: `app/Http/Middleware/DataAccessMiddleware.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Store user access level in request for queries
        match ($user->role) {
            'admin' => $request->setAttribute('data_access_level', 'all'),
            'manager' => $request->setAttribute('data_access_level', 'team'),
            'sba' => $request->setAttribute('data_access_level', 'team_and_personal'),
            'ba' => $request->setAttribute('data_access_level', 'personal'),
            default => $request->setAttribute('data_access_level', 'none'),
        };

        return $next($request);
    }
}
```

### 7.2 Query Scoping by Role

**File: `app/Models/Lead.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
{
    // Scope for BA (personal only)
    public function scopePersonal($query)
    {
        return $query->where('agent_id', Auth::id());
    }

    // Scope for Manager/SBA (team only)
    public function scopeTeam($query)
    {
        $user = Auth::user();
        $teamAgentIds = $user->team->agents()->pluck('id');
        return $query->whereIn('agent_id', $teamAgentIds);
    }

    // Scope for Admin (all)
    public function scopeAllData($query)
    {
        return $query;
    }

    // Global scope based on user role
    public static function boot()
    {
        parent::boot();

        $user = Auth::user();

        if ($user) {
            match ($user->role) {
                'ba' => static::addGlobalScope('personal', fn($q) => $q->personal()),
                'manager', 'sba' => static::addGlobalScope('team', fn($q) => $q->team()),
                'admin' => null,
                default => static::addGlobalScope('none', fn($q) => $q->whereRaw('1=0')),
            };
        }
    }
}
```

---

## 🚀 PHASE 8: DEPLOYMENT & OPTIMIZATION

### 8.1 Performance Optimization Checklist

```
✅ Query Optimization
   - Add indexes to frequently queried columns
   - Use eager loading (with())
   - Implement query caching
   - Set up database query monitoring

✅ Caching Strategy
   - Redis for session & metrics caching
   - Browser caching for assets (Cache-Control headers)
   - API response caching with proper TTL

✅ Frontend Optimization
   - Code splitting with React.lazy()
   - Image optimization (WebP format, lazy loading)
   - CSS/JS minification and bundling
   - Tree shaking for unused code

✅ Database Optimization
   - Optimize indexes
   - Use pagination for large datasets
   - Archive old logs/data

✅ CDN & Static Assets
   - Serve static files from CDN
   - Compress assets (gzip)
   - Implement service workers for offline support

✅ Monitoring
   - Set up error tracking (Sentry)
   - Monitor performance (New Relic, DataDog)
   - Set up alerting for critical metrics
```

### 8.2 Environment Configuration

**File: `.env`**
```bash
# App
APP_NAME="Stock Advisory CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://crm.example.com

# Database
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Cache
CACHE_DRIVER=redis
REDIS_HOST=
REDIS_PASSWORD=

# Queue
QUEUE_CONNECTION=redis

# Broadcasting
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

# AntiGravity & Stitch
STITCH_AI_ENABLED=true
STITCH_AI_API_KEY=
STITCH_AI_ENDPOINT=https://api.stitchai.com/v1

# Monitoring
SENTRY_DSN=
TELESCOPE_ENABLED=false

# File Storage
FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

---

## 📚 COMPLETE IMPLEMENTATION CHECKLIST

### Backend
- [ ] Setup Laravel 12 project structure
- [ ] Create all models (Lead, CallLog, Payment, User, AuditLog, etc.)
- [ ] Create database migrations & seeders
- [ ] Implement all controllers (Admin, Manager, SBA, BA)
- [ ] Create services (DashboardService, MetricsService, CacheService, etc.)
- [ ] Setup WebSocket broadcasting channels
- [ ] Implement role-based access control (Spatie)
- [ ] Add query scoping by role
- [ ] Create API endpoints for metrics
- [ ] Setup Pusher/WebSocket configuration
- [ ] Add audit logging middleware
- [ ] Implement caching strategy (Redis)

### Frontend
- [ ] Setup React project with Vite
- [ ] Configure Tailwind CSS with AntiGravity theme variables
- [ ] Create theme provider with light/dark mode support
- [ ] Build reusable components (StatCard, DataTable, Charts, etc.)
- [ ] Create dashboard layouts for all 4 roles
- [ ] Implement real-time updates with WebSocket
- [ ] Add filtering, sorting, pagination
- [ ] Create alert/notification system
- [ ] Add error boundaries and loading states
- [ ] Implement responsive design for mobile

### AntiGravity & Stitch AI
- [ ] Install & configure AntiGravity theme engine
- [ ] Setup Stitch AI SDK integration
- [ ] Create component generation services
- [ ] Build Stitch AI API endpoints
- [ ] Test component generation & validation
- [ ] Create fallback components for failed generations

### Security & Testing
- [ ] Add authentication & authorization
- [ ] Implement rate limiting
- [ ] Add CSRF protection
- [ ] Create unit tests for controllers
- [ ] Create integration tests for APIs
- [ ] Setup end-to-end tests with Playwright/Cypress
- [ ] Perform security audit (OWASP)
- [ ] Test data access isolation

### Deployment
- [ ] Configure production environment
- [ ] Setup CI/CD pipeline (GitHub Actions, GitLab CI)
- [ ] Configure monitoring & error tracking
- [ ] Setup database backups
- [ ] Create deployment documentation
- [ ] Perform load testing
- [ ] Setup health checks & uptime monitoring

---

## 📞 NEXT STEPS

1. **Start Backend Setup:** Begin with Laravel 12 project scaffolding and database design
2. **Setup Theme System:** Configure AntiGravity theme variables and CSS
3. **Create Base Components:** Build StatCard, DataTable, and other reusable components
4. **Integrate Stitch AI:** Setup component generation for custom dashboards
5. **Build Controllers:** Implement dashboard controllers for each role
6. **Add Real-Time:** Integrate WebSocket for live metrics updates
7. **Test Everything:** Unit, integration, and E2E tests
8. **Deploy & Monitor:** Push to production with monitoring & alerting

---

**Document Version:** 1.0 | **Created:** April 2, 2026 | **Status:** Ready for Development

This prompt provides everything needed to build a production-grade CRM dashboard with advanced theming, AI-powered component generation, and real-time metrics. Use it as a comprehensive guide for your development team! 🚀
