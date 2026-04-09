<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
@php
    $userCount = Auth::user();
    $initialLeadsCount = 0;
    $initialAcademyAlert = false;
    if ($userCount) {
        $initialLeadsCount = \App\Models\Lead::where('assigned_to', $userCount->id)
            ->whereNotIn('status', ['Lost', 'Junk', 'Not Interested', 'Paid Client', 'Service Expired'])
            ->count();
        $initialAcademyAlert = \App\Models\TrainingModule::whereDoesntHave('logs', function ($q) use ($userCount) {
            $q->where('user_id', $userCount->id);
        })->count() > 0;
    }
@endphp

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $company_name }} — {{ isset($pageTitle) ? $pageTitle : 'Dashboard' }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <script src="/js/crm-haptics.js"></script>
    <meta name="theme-color" content="#4f46e5">
    <link rel="apple-touch-icon" href="/favicon.ico">

    <!-- Fonts: Inter & DM Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind + Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5', // indigo-600
                        surface: '#FFFFFF',
                        pagebg: '#F8F9FB',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['DM Mono', 'monospace'],
                    }
                }
            }
        }
        
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered: ', registration.scope);
                }).catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="{{ asset('css/dashboard-premium.css') }}" rel="stylesheet">
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST') }}',
            wsPort: {{ env('REVERB_PORT', 80) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        // Cash Ring Listener
        window.Echo.channel('sales-alerts')
            .listen('.payment.received', (e) => {
                showCashRing(e);
            });

        function showCashRing(data) {
            // Play Cash Register Sound
            try {
                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2017/2017-preview.mp3');
                audio.play().catch(err => console.log('Audio play failed:', err));
            } catch(e) {}

            // Create Overlay
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-md animate-fade-in';
            overlay.innerHTML = `
                <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-800 p-1 rounded-3xl shadow-[0_0_50px_rgba(79,70,229,0.5)] transform scale-90 opacity-0 transition-all duration-500" id="ring-card">
                    <div class="bg-slate-900 rounded-[22px] p-8 text-center relative overflow-hidden">
                        <!-- Animated Background Particles -->
                        <div class="absolute inset-0 overflow-hidden opacity-20 pointer-events-none">
                            <div class="absolute top-0 left-1/4 w-32 h-32 bg-indigo-500 rounded-full blur-3xl animate-pulse"></div>
                            <div class="absolute bottom-0 right-1/4 w-32 h-32 bg-purple-500 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
                        </div>
                        
                        <div class="relative z-10">
                            <div class="w-20 h-20 bg-indigo-500/20 rounded-full flex items-center justify-center mx-auto mb-6 ring-4 ring-indigo-500/30 animate-bounce">
                                <span class="text-4xl">💰</span>
                            </div>
                            <h2 class="text-3xl font-black text-white mb-2 uppercase tracking-tighter">Big Win!</h2>
                            <p class="text-indigo-300 font-bold uppercase tracking-widest text-[10px] mb-6">High Value Conversion Secured</p>
                            
                            <div class="space-y-4 mb-8">
                                <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                    <p class="text-[10px] text-indigo-300 font-black uppercase mb-1">Top Closer</p>
                                    <p class="text-xl font-black text-white">${data.agentName}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-emerald-500/10 rounded-2xl p-4 border border-emerald-500/20">
                                        <p class="text-[10px] text-emerald-400 font-black uppercase mb-1">Amount</p>
                                        <p class="text-lg font-black text-emerald-300">₹${new Intl.NumberFormat().format(data.amount)}</p>
                                    </div>
                                    <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
                                        <p class="text-[10px] text-indigo-300 font-black uppercase mb-1">Client</p>
                                        <p class="text-lg font-black text-white truncate">${data.leadName}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button onclick="this.closest('.fixed').remove()" class="w-full bg-white text-indigo-900 font-black py-4 rounded-2xl shadow-xl hover:bg-indigo-50 transition-all active:scale-95 uppercase tracking-widest text-sm">
                                Let's Go! 🚀
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            
            // Animate In
            setTimeout(() => {
                const card = document.getElementById('ring-card');
                if (card) {
                    card.classList.remove('scale-90', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                }
            }, 100);

            // Auto Remove after 10s
            setTimeout(() => {
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.remove(), 500);
            }, 10000);
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8F9FB; }
        .font-mono { font-family: 'DM Mono', monospace; }

        /* Sidebar scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(79, 70, 229, 0.1); border-radius: 4px; }

        /* Nav item active state */
        .nav-item-active {
            @apply bg-indigo-50/50 text-indigo-700 font-semibold;
            box-shadow: inset 2px 0 0 0 theme('colors.indigo.600');
        }
        .nav-item-active .nav-icon { @apply text-indigo-600; }

        /* Pulse dot animation */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }
        .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }

        /* Premium SaaS Cards */
        .premium-card {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            transition: box-shadow 0.2s ease;
        }
        .premium-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* Leaderboard Progress Bars */
        .progress-bar-indigo { background: linear-gradient(90deg, #4F46E5 0%, #7C3AED 100%); }
        .progress-bar-emerald { background: #10B981; }
        .progress-bar-amber { background: #F59E0B; }
        .progress-bar-slate { background: #94A3B8; }

        /* Smooth transitions */
        .nav-item { transition: all 0.15s ease; border-radius: 7px; }

        /* ══ Dark Mode Overrides (Minimalist) ══ */
        .dark body { background-color: #0B0F1A; }
        .dark .bg-white { background-color: #111827; }
        .dark .premium-card { background-color: #111827; border-color: #1F2937; }
        .dark .text-slate-900 { color: #F9FAFB; }
        .dark .text-slate-800 { color: #F3F4F6; }
        .dark .border-slate-200 { border-color: #1F2937; }
    </style>
    </style>

    <!-- Anti-FOUC: Apply dark mode before paint -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased transition-colors duration-300" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">

    <div class="min-h-screen bg-[#F8FAFC]">
        <x-premium-toast />
        
        <!-- Sidebar Navigation -->
        <div class="flex h-screen overflow-hidden">

        <!-- ═══════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════ -->
        <!-- Mobile Overlay (Backdrop for mobile drawer) -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="lg:hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40" 
             style="display: none;"></div>

        <aside 
            x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="sidebar-nav fixed inset-y-0 left-0 z-50 flex flex-col glass-sidebar overflow-x-hidden overflow-y-auto w-[220px]">
            
            <!-- Logo -->
            <div class="flex items-center gap-2.5 px-5 py-6 flex-shrink-0 max-w-full overflow-hidden">
                @if($company_logo)
                    <img src="{{ asset('storage/' . $company_logo) }}" alt="Logo" style="width: 30px; height: 30px; min-width: 30px;" class="rounded-[6px] object-cover">
                @else
                    <div class="w-[30px] h-[30px] rounded-[6px] bg-[#4F46E5] flex items-center justify-center flex-shrink-0 text-white font-bold text-sm">
                        {{ substr($company_name, 0, 1) }}
                    </div>
                @endif
                <span class="font-bold text-[#111827] text-lg tracking-tight">
                    {{ $company_name }}
                </span>
            </div>

            <!-- Nav Content -->
            <div class="flex-1 px-3 space-y-6 pb-6">
                
                <!-- Group: MAIN -->
                <div>
                    <h3 class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.05em] text-[#9CA3AF]">Main</h3>
                    <div class="space-y-0.5">
                        <x-nav-link-premium :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="layout" label="Dashboard" />
                        @if(Auth::user()->hasPermission('leads', 'view_all') || Auth::user()->hasPermission('leads', 'view_team') || Auth::user()->hasPermission('leads', 'view_own'))
                        <x-nav-link-premium :href="route('leads.index')" :active="request()->routeIs('leads.*')" icon="users" label="Leads" :badge="$initialLeadsCount" badgeId="sidebar-leads-count" />
                        @endif
                        @if(Auth::user()->hasPermission('clients', 'view_all') || Auth::user()->hasPermission('clients', 'view_team') || Auth::user()->hasPermission('clients', 'view_own'))
                        <x-nav-link-premium :href="route('clients.index')" :active="request()->routeIs('clients.*')" icon="user-check" label="Clients" />
                        @endif
                        <x-nav-link-premium href="{{ route('agent.learning.index') }}" :active="request()->routeIs('agent.learning.*')" icon="graduation-cap" label="My Academy" :pulseAlert="$initialAcademyAlert" pulseId="sidebar-academy-dot" />
                        @if(Auth::user()->hasPermission('performance', 'view_company') || Auth::user()->hasPermission('performance', 'view_team') || Auth::user()->hasPermission('performance', 'view_own'))
                        <x-nav-link-premium href="{{ route('payments.index') }}" :active="request()->routeIs('payments.*')" icon="credit-card" label="Revenue" />
                        @endif
                        @if(auth()->user()->role !== 'BA')
                        <x-nav-link-premium href="{{ route('advisory-calls.index') }}" :active="request()->routeIs('advisory-calls.*')" icon="trending-up" label="Market Calls" />
                        @endif
                    </div>
                </div>

                <!-- Group: COMPLIANCE -->
                <div>
                    <h3 class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.05em] text-[#9CA3AF]">Compliance</h3>
                    <div class="space-y-0.5">
                        <x-nav-link-premium href="{{ route('kyc-rpm.index') }}" :active="request()->routeIs('kyc-rpm.*')" icon="shield-check" label="KYC / RPM Dashboard" />
                        @if(auth()->user()->role === 'Admin')
                        <x-nav-link-premium href="{{ route('message-templates.index') }}" :active="request()->routeIs('message-templates.*')" icon="file-text" label="Message Templates" />
                        @endif
                        @if(auth()->user()->role !== 'Admin')
                        <x-nav-link-premium href="{{ route('internal-mails.index') }}" :active="request()->routeIs('internal-mails.*')" icon="mail" label="Mails" />
                        @endif
                    </div>
                </div>

                @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Manager')
                <!-- Group: SYSTEM -->
                <div>
                    <h3 class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.05em] text-[#9CA3AF]">System</h3>
                    <div class="space-y-0.5">
                        <x-nav-link-premium href="{{ route('employees.index') }}" :active="request()->routeIs('employees.*')" icon="users-round" label="Team" />
                        @if(auth()->user()->role === 'Admin')
                        <x-nav-link-premium href="{{ route('admin.training.index') }}" :active="request()->routeIs('admin.training.*')" icon="book-open" label="Training Manager" />
                        @endif
                        <x-nav-link-premium href="{{ route('leads.reallocate') }}" :active="request()->routeIs('leads.reallocate')" icon="refresh-ccw" label="Re-allocate Leads" />
                    </div>
                </div>

                <!-- Group: PERFORMANCE -->
                <div>
                    <h3 class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.05em] text-[#9CA3AF]">Performance</h3>
                    <div class="space-y-0.5">
                        <x-nav-link-premium href="{{ route('analytics.index') }}" :active="request()->routeIs('analytics.*')" icon="bar-chart-3" label="Insights" />
                        <x-nav-link-premium href="{{ route('ai.index') }}" :active="request()->routeIs('ai.*')" icon="brain" label="AI Analysis" />
                        <x-nav-link-premium href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="clipboard-list" label="Reports" />
                        <x-nav-link-premium href="{{ route('targets.index') }}" :active="request()->routeIs('targets.*')" icon="target" label="Targets" />
                        <x-nav-link-premium href="{{ route('clients.retention') }}" :active="request()->routeIs('clients.retention')" icon="calendar-clock" label="Retention" />
                    </div>
                </div>

                <!-- Group: SYSTEM (Logs) -->
                <div>
                    <h3 class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.05em] text-[#9CA3AF]">System</h3>
                    <div class="space-y-0.5">
                        @if(auth()->user()->role === 'Admin')
                        <x-nav-link-premium href="{{ route('activities.index') }}" :active="request()->routeIs('activities.*')" icon="history" label="Activity Logs" />
                        <x-nav-link-premium href="{{ route('admin.logs') }}" :active="request()->routeIs('admin.logs')" icon="alert-circle" label="Error Logs" />
                        <x-nav-link-premium href="{{ route('admin.roles.matrix') }}" :active="request()->routeIs('admin.roles.matrix')" icon="fingerprint" label="Roles & Access" />
                        <x-nav-link-premium href="{{ route('admin.control-center') }}" :active="request()->routeIs('admin.control-center')" icon="settings-2" label="Control Center" />
                        @endif
                        <x-nav-link-premium href="{{ route('internal-mails.index') }}" :active="request()->routeIs('internal-mails.*')" icon="mail" label="Mails" />
                        <x-nav-link-premium href="{{ route('attendance.index') }}" :active="request()->routeIs('attendance.*')" icon="calendar-days" label="Attendance" />
                    </div>
                </div>
                @endif
            </div>

            <!-- User Footer -->
            <div class="p-4 border-t border-[#E5E7EB] bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#4F46E5] flex items-center justify-center text-white font-bold text-xs">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-[#111827] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#6B7280] truncate">{{ auth()->user()->role }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('profile.edit') }}" class="p-1 px-1.5 rounded-lg text-[#9CA3AF] hover:text-[#4F46E5] hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100" title="Settings">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1 px-1.5 rounded-lg text-[#9CA3AF] hover:text-rose-600 hover:bg-rose-50 transition-all border border-transparent hover:border-rose-100" title="Logout">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════
             MAIN AREA (Topbar + Content)
        ═══════════════════════════════════════════ -->
        <!-- ═══════════════════════════════════════════
             MAIN AREA (Topbar + Content)
        ═══════════════════════════════════════════ -->
        <div 
            :class="sidebarOpen ? 'lg:ml-[220px]' : 'ml-0'"
            class="flex-1 flex flex-col transition-all duration-300 overflow-hidden">

            <!-- ───────────── STICKY TOPBAR ───────────── -->
            <header class="sticky top-0 z-30 glass-card flex items-center h-[56px] px-6 gap-4 flex-shrink-0 !rounded-none !border-t-0 !border-l-0 !border-r-0">

                <!-- Sidebar Toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg text-[#6B7280] hover:bg-[#F3F4F6] transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-sm text-[#6B7280]">
                    <span class="font-medium">Dashboard</span>
                    <span class="text-[#D1D5DB]">/</span>
                    <div class="flex items-center gap-1.5 font-bold text-[#4F46E5]">
                        <span class="pulse-dot w-1.5 h-1.5 bg-[#10B981] rounded-full"></span>
                        Live
                    </div>
                </div>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Global Search Trigger (Pill style) -->
                <div class="relative hidden lg:block">
                    <button @click="window.dispatchEvent(new CustomEvent('open-search'))" type="button" 
                            class="flex items-center gap-2 px-3 py-1.5 bg-[#F3F4F6] hover:bg-[#E5E7EB] transition-all rounded-full border border-transparent w-[210px] text-[#9CA3AF] group shadow-sm hover:shadow-md">
                        <svg class="w-3.5 h-3.5 text-[#9CA3AF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-xs font-medium">Search...</span>
                        <div class="ml-auto flex items-center gap-1 opacity-50">
                            <kbd class="font-sans text-[9px] font-bold uppercase text-[#6B7280] bg-white border border-[#D1D5DB] rounded px-1 py-0.5">Ctrl</kbd>
                            <kbd class="font-sans text-[9px] font-bold uppercase text-[#6B7280] bg-white border border-[#D1D5DB] rounded px-1 py-0.5">K</kbd>
                        </div>
                    </button>
                </div>

                    <div class="flex items-center gap-2 border-l border-[#E5E7EB] pl-4">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#10B981]"></span>
                        </span>
                        <span class="text-[10px] font-black text-[#10B981] uppercase tracking-widest">System Active</span>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <div x-data="darkModeToggle()">
                        <button @click="toggle()" type="button" class="p-2 rounded-lg text-[#6B7280] hover:bg-[#F3F4F6] transition-colors relative overflow-hidden">
                            <svg x-show="isDark" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <svg x-show="!isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        </button>
                    </div>

                    <!-- Notification Bell -->
                    <div x-data="{ open: false, notifCount: {{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }} }" @notifs-updated.window="notifCount = $event.detail.count" class="relative">
                        <button @click="open = !open" class="p-2 rounded-lg text-[#6B7280] hover:bg-[#F3F4F6] transition-colors relative focus:outline-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <template x-if="notifCount > 0">
                                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 ring-2 ring-white"></span>
                                </span>
                            </template>
                        </button>
                        
                        <!-- Dropdown Panel -->
                        <div x-show="open" @click.outside="open = false" style="display: none;"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-[#E5E7EB] z-50 overflow-hidden transform origin-top-right transition-all">
                            <div class="p-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Alerts Center</span>
                                <template x-if="notifCount > 0">
                                    <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-indigo-600 font-bold hover:underline">Mark all read</button>
                                    </form>
                                </template>
                            </div>
                            <div class="max-h-72 overflow-y-auto w-full">
                                @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                    @foreach(auth()->user()->unreadNotifications as $notification)
                                        <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors cursor-pointer group flex items-start gap-3">
                                            <div class="mt-0.5">
                                                @if($notification->data['type'] ?? '' === 'warning')
                                                    <div class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    </div>
                                                @else
                                                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-[11px] text-gray-800 font-medium leading-snug">{{ $notification->data['message'] ?? 'New Alert' }}</p>
                                                <p class="text-[9px] text-gray-400 mt-1 uppercase tracking-widest font-bold">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if(!empty($notification->data['action_url']))
                                                <a href="{{ $notification->data['action_url'] }}" class="text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="p-6 text-center text-gray-400">
                                        <svg class="w-8 h-8 mx-auto mb-2 opacity-50 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">All caught up</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="w-8 h-8 rounded-full bg-[#4F46E5] flex items-center justify-center text-white text-[12px] font-bold ring-2 ring-[#F3F4F6] hover:ring-[#4F46E5] transition-all focus:outline-none">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             @click.outside="open = false" 
                             style="display: none;"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-[#E5E7EB] z-50 py-1 transform origin-top-right transition-all">
                            <div class="px-4 py-2 border-b border-gray-100 bg-gray-50/50">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Logged in as</p>
                                <p class="text-xs font-bold text-gray-800 mt-1 truncate">{{ auth()->user()->name }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Your Profile
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
            </header>

            <!-- ───────────── PAGE CONTENT ───────────── -->
            @isset($header)
                <div class="bg-white border-b border-[#E5E7EB] py-5 px-8 flex justify-between items-center flex-shrink-0">
                    <div>
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="flex-1 overflow-y-auto bg-[#F8F9FB] p-8">
                <!-- Velocity Engine: Performance Ticker -->
                <x-dashboard.velocity-ticker />
                
                {{ $slot }}
            </main>
        </div>
    
    <!-- Command Palette Modal -->
    <x-global-search />

    <!-- Notification Toast Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-[90] space-y-3 w-96"></div>

    <!-- Admin Broadcast Ticker -->
    <div id="admin-ticker" class="fixed top-14 left-0 right-0 z-[45] bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 text-white overflow-hidden transition-all duration-500" style="display: none; height: 0;" :style="sidebarOpen && window.innerWidth >= 1024 ? 'margin-left: 220px' : 'margin-left: 0'">
        <div class="flex items-center h-8 px-4">
            <div class="flex-shrink-0 flex items-center gap-2 pr-4 border-r border-white/20">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-widest">Live</span>
            </div>
            <div class="flex-1 overflow-hidden relative">
                <div id="ticker-content" class="whitespace-nowrap animate-marquee text-xs font-bold tracking-wide pl-4"></div>
            </div>
            <button onclick="dismissTicker()" class="flex-shrink-0 ml-2 p-1 rounded hover:bg-white/20 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <!-- Idle Warning Overlay -->
    <div id="idle-warning-overlay" class="fixed inset-0 z-[95] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center transition-all duration-500" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center transform transition-all">
            <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white">You've been idle</h3>
            <p class="text-sm text-slate-500 mt-2">No activity detected for <span id="idle-minutes" class="font-black text-amber-600">10</span> minutes.</p>
            <p class="text-xs text-slate-400 mt-1">Your session will auto-pause in <span id="idle-countdown" class="font-black text-rose-500">5:00</span></p>
            <button onclick="dismissIdleWarning()" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 rounded-xl transition-colors text-sm uppercase tracking-widest">
                I'm Back!
            </button>
        </div>
    </div>

    <!-- Mobile Interactive Layer -->
    <x-layouts.mobile-quick-actions />

    <!-- AI Intelligence Layer -->
    <x-layouts.ai-sidebar />

    <!-- ───────────── SCRIPTS ───────────── -->
    <script>
        // Real-time Notification Polling
        let lastAdvisoryId = localStorage.getItem('lastAdvisoryId') || 0;
        let lastAnnouncementId = localStorage.getItem('lastAnnouncementId') || 0;

        function checkNotifications() {
            const url = "{{ route('notifications.counts') }}?last_announcement_id=" + lastAnnouncementId;
            fetch(url)
                .then(r => r.json())
                .then(data => {
                    updateBadge('nav-mail-badge', data.mail);
                    updateBadge('sidebar-leads-count', data.leads_count);
                    
                    const academyDot = document.getElementById('sidebar-academy-dot');
                    if (academyDot) {
                        if (data.academy_alert) academyDot.classList.remove('hidden');
                        else academyDot.classList.add('hidden');
                    }

                    const serverAdvisoryId = parseInt(data.latest_advisory_id) || 0;
                    const localAdvisoryId = parseInt(lastAdvisoryId) || 0;
                    let advisoryShown = false;
                    
                    if (localAdvisoryId > 0 && serverAdvisoryId > localAdvisoryId && data.latest_advisory_text) {
                        const tipText = data.latest_advisory_text;
                        showToast({
                            title: 'New Market Call',
                            body: tipText,
                            url: "{{ route('advisory-calls.index') }}",
                            action_label: 'VIEW CALLS',
                            copyText: tipText
                        });
                        advisoryShown = true;
                        try { new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play().catch(() => {}); } catch(e) {}
                    }
                    if (serverAdvisoryId > localAdvisoryId) { lastAdvisoryId = serverAdvisoryId; localStorage.setItem('lastAdvisoryId', serverAdvisoryId); }

                    if (data.new_announcement) {
                        // Skip showing announcement toast if it's the same Market Call we just showed via advisory toast
                        const isDuplicateMarketCall = advisoryShown && (data.new_announcement.title.includes('Market Call') || data.new_announcement.title.includes('📈'));
                        
                        if (!isDuplicateMarketCall) {
                            showToast({ title: data.new_announcement.title, body: data.new_announcement.body, type: data.new_announcement.type, action_label: 'OK' });
                            if (data.new_announcement.type === 'success') { try { new Audio('https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3').play().catch(() => {}); } catch(e) {} }
                        }
                        lastAnnouncementId = data.new_announcement.id; localStorage.setItem('lastAnnouncementId', lastAnnouncementId);
                    } else if (data.latest_announcement_id < lastAnnouncementId) { lastAnnouncementId = data.latest_announcement_id; localStorage.setItem('lastAnnouncementId', lastAnnouncementId); }
                    if (data.latest_announcement_id > lastAnnouncementId) { lastAnnouncementId = data.latest_announcement_id; localStorage.setItem('lastAnnouncementId', lastAnnouncementId); }

                    const msg = data.ticker_message || '';
                    const urgency = data.ticker_urgency || 'normal';
                    if (msg && msg !== lastTickerMsg) {
                        lastTickerMsg = msg;
                        showTicker(msg, urgency);
                    } else if (!msg && lastTickerMsg) {
                        lastTickerMsg = '';
                        dismissTicker();
                    }
                    if (data.new_announcement && data.new_announcement.type === 'ticker') {
                        showTicker(data.new_announcement.body || data.new_announcement.title, 'normal');
                    }

                    if (data.due_followups && data.due_followups.length > 0) {
                        let seen = JSON.parse(localStorage.getItem('seenFollowupIds') || '[]');
                        data.due_followups.forEach(rem => {
                            // Use exact timestamp to avoid skipping minutes due to polling intervals
                            const key = rem.id + '_' + rem.exact_time;
                            if (!seen.includes(key)) { 
                                showToast({ 
                                    title: 'Follow-up Reminder', 
                                    body: `Scheduled call with ${rem.name} at ${rem.time}`, 
                                    url: `/leads/${rem.id}`, 
                                    action_label: 'VIEW LEAD',
                                    type: 'warning'
                                }); 
                                seen.push(key); 
                            }
                        });
                        if (seen.length > 50) seen = seen.slice(-50);
                        localStorage.setItem('seenFollowupIds', JSON.stringify(seen));
                    }
                }).catch(e => console.error('Notification poll failed:', e));
        }

        function updateBadge(id, count) {
            const el = document.getElementById(id);
            if (!el) return;
            if (count > 0) { el.innerText = count; el.classList.remove('hidden'); }
            else { el.classList.add('hidden'); }
        }

        setInterval(checkNotifications, 5000);
        checkNotifications();

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, (ch) => {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
            });
        }

        function safeUrl(url) {
            if (typeof url !== 'string') return '';
            return url.startsWith('/') ? url : '';
        }

        function copyTip(btn) {
            const raw = decodeURIComponent(btn.dataset.copy || '');
            navigator.clipboard.writeText(raw).then(() => {
                btn.textContent = 'Copied';
                btn.classList.add('text-emerald-600');
            }).catch(() => {});
        }

        function showToast(notif) {
            const container = document.getElementById('notification-container');
            const toast = document.createElement('div');

            // Color-coded borders based on notification type
            let borderColor = 'border-indigo-500';
            let iconSvg = '';
            let iconBg = 'bg-indigo-100';
            let iconColor = 'text-indigo-600';

            if (notif.type === 'success' || notif.title?.includes('Payment') || notif.title?.includes('DEAL')) {
                borderColor = 'border-emerald-500';
                iconBg = 'bg-emerald-100'; iconColor = 'text-emerald-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
            } else if (notif.title?.includes('Market Call') || notif.title?.includes('Advisory') || notif.title?.includes('📈')) {
                borderColor = 'border-rose-500';
                iconBg = 'bg-rose-100'; iconColor = 'text-rose-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>';
            } else if (notif.title?.includes('Alert')) {
                borderColor = 'border-blue-500';
                iconBg = 'bg-blue-100'; iconColor = 'text-blue-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>';
            } else if (notif.title?.includes('Idle') || notif.title?.includes('Warning') || notif.type === 'warning') {
                borderColor = 'border-amber-500';
                iconBg = 'bg-amber-100'; iconColor = 'text-amber-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
            } else {
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>';
            }

            // Build a premium toast
            const copyBtn = notif.copyText ? `<button onclick="navigator.clipboard.writeText('${notif.copyText.replace(/'/g, "\\'")}'). then(() => {this.innerText='Copied ✓'; this.classList.add('text-emerald-600')})" class=\"mt-2 text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-100 hover:text-indigo-600 px-3 py-1 rounded-lg transition-colors\">📋 Copy Tip</button>` : '';

            toast.className = `bg-white dark:bg-slate-800 border-l-4 ${borderColor} shadow-2xl rounded-xl p-4 transform transition-all duration-500 translate-x-full opacity-0 flex items-start gap-3 ring-1 ring-black/5`;
            toast.innerHTML = `
                <div class="w-10 h-10 rounded-xl ${iconBg} ${iconColor} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${iconSvg}</svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-slate-900 dark:text-white">${notif.title}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 ${notif.url ? 'mb-1' : ''}">${notif.body}</p>
                    ${notif.url ? `<a href="${notif.url}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline inline-block">${notif.action_label || 'VIEW'} →</a>` : ''}
                    ${copyBtn}
                </div>
                <button onclick="this.closest('.transform').style.transform='translateX(120%)';setTimeout(() => this.closest('.transform')?.remove(), 300)" class="text-slate-300 hover:text-slate-500 dark:text-slate-500 dark:hover:text-slate-300 text-lg leading-none mt-1 flex-shrink-0">&times;</button>
            `;
            container.prepend(toast);
            // Start animation
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });
            // Auto-dismiss after 12s
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 12000);

            // Cap max visible toasts
            const toasts = container.children;
            if (toasts.length > 5) {
                toasts[toasts.length - 1]?.remove();
            }
        }

        // Override toast with HTML-escaped content to prevent XSS
        function showToast(notif) {
            const container = document.getElementById('notification-container');
            if (!container) return;
            const toast = document.createElement('div');

            let borderColor = 'border-indigo-500';
            let iconSvg = '';
            let iconBg = 'bg-indigo-100';
            let iconColor = 'text-indigo-600';

            const titleText = String(notif.title || '');
            if (notif.type === 'success' || titleText.includes('Payment') || titleText.includes('DEAL') || titleText.includes('Deal')) {
                borderColor = 'border-emerald-500';
                iconBg = 'bg-emerald-100'; iconColor = 'text-emerald-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
            } else if (titleText.includes('Market Call') || titleText.includes('Advisory')) {
                borderColor = 'border-rose-500';
                iconBg = 'bg-rose-100'; iconColor = 'text-rose-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>';
            } else if (titleText.includes('Alert')) {
                borderColor = 'border-blue-500';
                iconBg = 'bg-blue-100'; iconColor = 'text-blue-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>';
            } else if (titleText.includes('Idle') || titleText.includes('Warning') || notif.type === 'warning') {
                borderColor = 'border-amber-500';
                iconBg = 'bg-amber-100'; iconColor = 'text-amber-600';
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
            } else {
                iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>';
            }

            const title = escapeHtml(titleText);
            const body = escapeHtml(notif.body || '');
            const url = safeUrl(notif.url || '');
            const actionLabel = escapeHtml(notif.action_label || 'VIEW');
            const copyText = notif.copyText ? encodeURIComponent(String(notif.copyText)) : '';
            const copyBtn = notif.copyText ? `<button data-copy="${copyText}" onclick="copyTip(this)" class="mt-2 text-[10px] font-black uppercase tracking-widest text-slate-500 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-100 hover:text-indigo-600 px-3 py-1 rounded-lg transition-colors">Copy Tip</button>` : '';

            toast.className = `bg-white dark:bg-slate-800 border-l-4 ${borderColor} shadow-2xl rounded-xl p-4 transform transition-all duration-500 translate-x-full opacity-0 flex items-start gap-3 ring-1 ring-black/5`;
            toast.innerHTML = `
                <div class="w-10 h-10 rounded-xl ${iconBg} ${iconColor} flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${iconSvg}</svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-slate-900 dark:text-white">${title}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 ${url ? 'mb-1' : ''}">${body}</p>
                    ${url ? `<a href="${url}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline inline-block">${actionLabel} -></a>` : ''}
                    ${copyBtn}
                </div>
                <button onclick="this.closest('.transform').style.transform='translateX(120%)';setTimeout(() => this.closest('.transform')?.remove(), 300)" class="text-slate-300 hover:text-slate-500 dark:text-slate-500 dark:hover:text-slate-300 text-lg leading-none mt-1 flex-shrink-0">&times;</button>
            `;

            container.prepend(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 12000);

            const toasts = container.children;
            if (toasts.length > 5) {
                toasts[toasts.length - 1]?.remove();
            }
        }

        @if(session('deal_won'))
        try {
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2000/2000-preview.mp3');
            audio.play().catch(() => {});
            setTimeout(() => {
                showToast({ title: '🎉 DEAL WON!', body: 'Fantastic work! Revenue secured.', type: 'success' });
                // Fire confetti
                if (typeof confetti === 'function') {
                    confetti({ particleCount: 200, spread: 90, origin: { y: 0.6 } });
                }
            }, 500);
        } catch(e) {}
        @endif

        // Heartbeat Monitor with Idle Warning
        let userHasInput = false;
        let lastInputTime = Date.now();
        let idleWarningShown = false;
        let idleCountdownInterval = null;

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, () => {
                userHasInput = true;
                lastInputTime = Date.now();
            });
        });

        // Monitor idle state
        setInterval(() => {
            const idleMs = Date.now() - lastInputTime;
            const idleMinutes = Math.floor(idleMs / 60000);

            if (idleMinutes >= 10 && !idleWarningShown) {
                idleWarningShown = true;
                document.getElementById('idle-minutes').textContent = idleMinutes;
                const overlay = document.getElementById('idle-warning-overlay');
                overlay.style.display = 'flex';

                // 5-minute countdown
                let cd = 300;
                idleCountdownInterval = setInterval(() => {
                    cd--;
                    const m = Math.floor(cd / 60);
                    const s = cd % 60;
                    const el = document.getElementById('idle-countdown');
                    if (el) el.textContent = `${m}:${s.toString().padStart(2, '0')}`;
                    if (cd <= 0) {
                        clearInterval(idleCountdownInterval);
                        // Session auto-paused - just dismiss the overlay
                        dismissIdleWarning();
                    }
                }, 1000);

                // Also play a subtle ding
                try { new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play().catch(() => {}); } catch(e) {}
            }
        }, 30000);

        function dismissIdleWarning() {
            idleWarningShown = false;
            lastInputTime = Date.now();
            const overlay = document.getElementById('idle-warning-overlay');
            if (overlay) overlay.style.display = 'none';
            if (idleCountdownInterval) clearInterval(idleCountdownInterval);
        }

        function sendHeartbeat() {
            fetch("{{ route('attendance.heartbeat') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ active_input: userHasInput })
            }).then(async r => {
                if (!r.ok) throw new Error('Network or auth error');
                return r.json();
            }).then(data => {
                const badge = document.getElementById('user-status-badge');
                if (badge) {
                    badge.innerText = data.status;
                    badge.className = `text-xs font-bold px-2 py-0.5 rounded-full ${data.status === 'Active' ? 'bg-emerald-100 text-emerald-700' : (data.status === 'Idle' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')}`;
                }
                userHasInput = false;
            }).catch(() => {
                const badge = document.getElementById('user-status-badge');
                if (badge) {
                    badge.innerText = 'Offline';
                    badge.className = 'text-xs font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-500';
                }
            });
        }
        setInterval(sendHeartbeat, 60000);
        setTimeout(sendHeartbeat, 5000);

        // ── Admin Broadcast Ticker (reads from system_settings via notification poll) ──
        let tickerDismissed = false;
        let lastTickerMsg = '';

        function checkTicker() {
            return;
            if (tickerDismissed) return;
            fetch("{{ route('notifications.counts') }}")
                .then(r => r.json())
                .then(data => {
                    const msg = data.ticker_message || '';
                    const urgency = data.ticker_urgency || 'normal';
                    if (msg && msg !== lastTickerMsg) {
                        lastTickerMsg = msg;
                        showTicker(msg, urgency);
                    } else if (!msg) {
                        dismissTicker();
                    }

                    // Also handle standard announcements
                    if (data.new_announcement && data.new_announcement.type === 'ticker') {
                        showTicker(data.new_announcement.body || data.new_announcement.title, 'normal');
                    }
                }).catch(() => {});
        }

        function showTicker(text, urgency) {
            const ticker = document.getElementById('admin-ticker');
            const content = document.getElementById('ticker-content');
            if (!ticker || !content) return;
            content.textContent = 'NOTICE: ' + text + ' | ' + text + ' | ' + text;

            // Urgency-coded gradient
            const gradients = {
                normal: 'from-indigo-600 via-purple-600 to-indigo-600',
                urgent: 'from-amber-500 via-orange-500 to-amber-500',
                emergency: 'from-rose-600 via-red-600 to-rose-600'
            };
            ticker.className = ticker.className.replace(/from-\S+ via-\S+ to-\S+/, gradients[urgency] || gradients.normal);
            ticker.style.display = 'block';
            requestAnimationFrame(() => { ticker.style.height = '2rem'; });
            tickerDismissed = false;
        }

        // Ticker is updated by the notification poll to avoid duplicate requests

        function dismissTicker() {
            const ticker = document.getElementById('admin-ticker');
            if (ticker) { ticker.style.height = '0'; setTimeout(() => ticker.style.display = 'none', 500); }
            tickerDismissed = true;
        }

        // Dark Mode Toggle Component
        function darkModeToggle() {
            return {
                isDark: document.documentElement.classList.contains('dark'),
                toggle() {
                    this.isDark = !this.isDark;
                    document.documentElement.classList.toggle('dark', this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        function handleCommEngagement(channel, leadId, url) {
            fetch(`/leads/${leadId}/log-comm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ channel: channel })
            })
            .then(response => response.json())
            .then(data => {
                window.dispatchEvent(new CustomEvent('toast-notify', { 
                    detail: { message: data.message, type: 'success' } 
                }));
            })
            .catch(error => console.error('Error logging comm:', error));

            window.open(url, '_blank');
        }
    </script>

    @stack('scripts')
</body>

</html>
