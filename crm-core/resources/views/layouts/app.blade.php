<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'StockIdea CRM') }} — {{ isset($pageTitle) ? $pageTitle : 'Dashboard' }}</title>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <link rel="apple-touch-icon" href="/favicon.ico">

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind + Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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

    <style>
        * { font-family: 'Inter', sans-serif; }

        /* Sidebar scrollbar */
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.3); border-radius: 4px; }

        /* Page content scrollbar */
        .main-content::-webkit-scrollbar { width: 6px; }
        .main-content::-webkit-scrollbar-track { background: #f1f5f9; }
        .main-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* Nav item active state */
        .nav-item-active {
            background: rgba(99,102,241,0.10);
            color: #4f46e5 !important;
            font-weight: 600;
        }
        .nav-item-active .nav-icon { color: #4f46e5; }
        .nav-item-active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 60%; background: #4f46e5; border-radius: 0 4px 4px 0;
        }

        /* Pulse dot animation */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }
        .pulse-dot { animation: pulse-dot 2s ease-in-out infinite; }

        /* KPI card hover */
        .kpi-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }

        /* Smooth transitions */
        .nav-item { transition: background 0.15s ease, color 0.15s ease; }

        /* ══ Dark Mode Overrides ══ */
        .dark body, .dark .bg-white { background-color: #0f172a; }
        .dark .bg-slate-50 { background-color: #020617; }
        .dark .bg-slate-100 { background-color: #1e293b; }
        .dark .border-slate-100, .dark .border-slate-200 { border-color: #1e293b; }
        .dark .text-slate-900 { color: #f1f5f9; }
        .dark .text-slate-800 { color: #e2e8f0; }
        .dark .text-slate-700 { color: #cbd5e1; }
        .dark .text-slate-600 { color: #94a3b8; }
        .dark .text-slate-500 { color: #64748b; }
        .dark .text-slate-400 { color: #475569; }
        .dark .hover\:bg-slate-100:hover { background-color: #1e293b; }
        .dark .hover\:bg-slate-50:hover { background-color: #0f172a; }
        .dark .bg-white\/95 { background-color: rgba(15, 23, 42, 0.95); }

        /* Dark mode specifics: sidebar, cards, tables */
        .dark aside { background-color: #0f172a !important; border-color: #1e293b !important; }
        .dark header { background-color: rgba(15, 23, 42, 0.95) !important; border-color: #1e293b !important; }
        .dark .rounded-2xl, .dark .rounded-3xl { border-color: #1e293b; }
        .dark .bg-white.rounded-2xl, .dark .bg-white.rounded-3xl { background-color: #1e293b; }
        .dark .bg-slate-50\/50 { background-color: rgba(30, 41, 59, 0.5); }
        .dark .bg-slate-50\/60 { background-color: rgba(30, 41, 59, 0.6); }
        .dark .divide-slate-50 > :not([hidden]) ~ :not([hidden]) { border-color: #1e293b; }
        .dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: #1e293b; }

        /* Dark mode form inputs */
        .dark input, .dark select, .dark textarea {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        .dark input::placeholder { color: #475569 !important; }

        /* Dark scrollbars */
        .dark .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); }
        .dark .main-content::-webkit-scrollbar-track { background: #0f172a; }
        .dark .main-content::-webkit-scrollbar-thumb { background: #334155; }

        /* Dark mode toggle animation */
        .theme-toggle-icon {
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease;
        }

        /* Marquee animation for admin ticker */
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee { animation: marquee 20s linear infinite; }

        /* Fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
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

<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased transition-colors duration-300" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">

        <!-- ═══════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════ -->
        <aside class="sidebar-nav fixed inset-y-0 left-0 z-40 flex flex-col bg-white border-r border-slate-200 overflow-y-auto transition-all duration-300"
               :class="sidebarOpen ? 'w-60' : 'w-16'">

            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-100 flex-shrink-0">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-black text-sm">S</span>
                </div>
                <span class="font-black text-slate-900 text-lg tracking-tight transition-opacity duration-200"
                      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                    Stock<span class="text-indigo-600">Idea</span>
                </span>
            </div>

            <!-- Nav Sections -->
            <nav class="flex-1 px-3 py-4 space-y-1">

                <!-- MAIN -->
                <div x-show="sidebarOpen" class="px-2 pb-1 pt-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main</p>
                </div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 group {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-sm font-medium transition-opacity duration-200" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Dashboard</span>
                </a>

                <a href="{{ route('leads.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('leads.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('leads.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Leads</span>
                    @php $leadBadge = \App\Models\Lead::whereDate('created_at', today())->count(); @endphp
                    @if($leadBadge > 0)
                    <span class="ml-auto bg-indigo-100 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full" :class="sidebarOpen ? '' : 'hidden'">{{ $leadBadge }}</span>
                    @endif
                </a>

                <a href="{{ route('clients.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('clients.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('clients.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Clients</span>
                </a>

                <a href="{{ route('agent.learning.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('agent.learning.*') ? 'nav-item-active' : '' }}">
                    <span class="nav-icon w-5 h-5 flex-shrink-0 flex items-center justify-center text-lg {{ request()->routeIs('agent.learning.*') ? 'text-indigo-600' : 'text-slate-500' }}">🎓</span>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">My Academy</span>
                    @if(\App\Models\TrainingLog::where('user_id', Auth::id())->where('completion_status', 'pending')->exists() || 
                        \App\Models\TrainingModule::where(function($q) {
                            $q->where('target_team', 'All')->orWhere('target_team', Auth::user()->role);
                        })->whereDoesntHave('logs', function($q) {
                            $q->where('user_id', Auth::id());
                        })->exists())
                        <span class="ml-auto bg-rose-500 w-2 h-2 rounded-full animate-pulse" :class="sidebarOpen ? '' : 'hidden'"></span>
                    @endif
                </a>

                <a href="{{ route('payments.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('payments.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('payments.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Revenue</span>
                </a>

                @if(in_array(auth()->user()->role, ['Admin', 'Manager', 'SBA']))
                <a href="{{ route('advisory-calls.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('advisory-calls.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('advisory-calls.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Market Calls</span>
                </a>
                @endif

                @if(in_array(auth()->user()->role, ['Admin', 'Manager', 'SBA']))
                <div x-show="sidebarOpen" class="px-2 pb-1 pt-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">System</p>
                </div>
                <ul class="space-y-1">
                    <!-- Users -->
                    <li>
                        <a href="{{ route('employees.index') }}" class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('employees.*') ? 'nav-item-active' : '' }}">
                            <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('employees.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Team</span>
                        </a>
                    </li>
                    @if(auth()->user()->role === 'Admin')
                    <li>
                        <a href="{{ route('admin.training.index') }}" class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('admin.training.*') ? 'nav-item-active' : '' }}">
                            <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.training.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Training Manager</span>
                        </a>
                    </li>
                    @endif
                    <!-- Re-allocate Leads -->
                    <li>
                        <a href="{{ route('leads.reallocate') }}" class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('leads.reallocate') ? 'nav-item-active' : '' }}">
                            <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('leads.reallocate') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Re-allocate Leads</span>
                        </a>
                    </li>
                </ul>
                @endif

                <!-- PERFORMANCE -->
                @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <div x-show="sidebarOpen" class="px-2 pb-1 pt-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Performance</p>
                </div>

                <a href="{{ route('analytics.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('analytics.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('analytics.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Insights</span>
                </a>

                <a href="{{ route('ai.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('ai.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('ai.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">AI Analysis</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('reports.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Reports</span>
                </a>

                <a href="{{ route('targets.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('targets.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Targets</span>
                </a>

                <a href="{{ route('employees.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('employees.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Team</span>
                </a>
                @endif

                <!-- SYSTEM -->
                @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <div x-show="sidebarOpen" class="px-2 pb-1 pt-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">System</p>
                </div>
                @endif

                @if(auth()->user()->role === 'Admin')
                <a href="{{ route('activities.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('activities.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Activity Logs</span>
                </a>

                <a href="{{ route('admin.logs') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('admin.logs') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Error Logs</span>
                </a>

                <a href="{{ route('admin.control-center') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('admin.control-center') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.control-center') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Control Center</span>
                </a>
                @endif



                <a href="{{ route('internal-mails.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('internal-mails.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0 {{ request()->routeIs('internal-mails.*') ? 'text-indigo-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Mails</span>
                    <span id="nav-mail-badge" class="hidden ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full" :class="sidebarOpen ? '' : 'hidden'">0</span>
                </a>

                @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <a href="{{ route('attendance.index') }}"
                   class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('attendance.*') ? 'nav-item-active' : '' }}">
                    <svg class="nav-icon w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-medium" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">Attendance</span>
                </a>
                @endif

            </nav>

            <!-- User Profile Footer -->
            <div class="border-t border-slate-100 p-3 flex-shrink-0">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="text-left overflow-hidden" :class="sidebarOpen ? 'block' : 'hidden'">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->role }}</p>
                        </div>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute bottom-full left-0 mb-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profile Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════
             MAIN AREA (Topbar + Content)
        ═══════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
             :class="sidebarOpen ? 'ml-60' : 'ml-16'">

            <!-- ───────────── STICKY TOPBAR ───────────── -->
            <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm border-b border-slate-200 flex items-center h-14 px-6 gap-4 flex-shrink-0">

                <!-- Sidebar Toggle -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Breadcrumb -->
                @php
                    $routePageTitles = [
                        'dashboard'          => 'Dashboard',
                        'leads.index'        => 'Leads',
                        'leads.show'         => 'Lead Profile',
                        'leads.create'       => 'New Lead',
                        'leads.edit'         => 'Edit Lead',
                        'clients.index'      => 'Clients',
                        'clients.show'       => 'Client Profile',
                        'payments.index'     => 'Revenue',
                        'advisory-calls.index' => 'Market Calls',
                        'advisory-calls.show'  => 'Call Details',
                        'analytics.index'    => 'Insights',
                        'ai.index'           => 'AI Analysis',
                        'reports.index'      => 'Reports',
                        'targets.index'      => 'Targets',
                        'employees.index'    => 'Team',
                        'activities.index'   => 'Activity Logs',
                        'admin.logs'         => 'Error Logs',

                        'internal-mails.index' => 'Mails',
                        'attendance.index'   => 'Attendance',
                        'admin.control-center' => 'Control Center',
                        'profile.edit'       => 'Profile',
                    ];
                    $currentPageTitle = 'Dashboard';
                    foreach($routePageTitles as $routeName => $pageLabel) {
                        if(request()->routeIs($routeName)) { $currentPageTitle = $pageLabel; break; }
                    }
                @endphp
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <span class="font-semibold text-slate-800">{{ $currentPageTitle }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-indigo-600 flex items-center gap-1.5">
                        <span class="pulse-dot inline-block w-2 h-2 bg-emerald-500 rounded-full"></span>
                        Live
                    </span>
                </div>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Global Search Trigger -->
                <div class="relative hidden md:block">
                    <button @click="window.dispatchEvent(new CustomEvent('open-search'))" type="button" class="flex items-center gap-2 pl-3 pr-4 py-2 bg-slate-100/80 hover:bg-slate-200 transition-colors rounded-xl text-sm border border-transparent w-64 text-slate-400 group">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="font-medium">Search Leads, Clients...</span>
                        <div class="ml-auto flex items-center gap-1 opacity-70">
                            <kbd class="font-sans text-[10px] font-black uppercase text-slate-500 bg-white shadow-sm border border-slate-200 rounded px-1.5 py-0.5">Ctrl</kbd>
                            <span class="text-xs text-slate-400">+</span>
                            <kbd class="font-sans text-[10px] font-black uppercase text-slate-500 bg-white shadow-sm border border-slate-200 rounded px-1.5 py-0.5">K</kbd>
                        </div>
                    </button>
                </div>

                <!-- Live Status -->
                <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-xl hidden md:flex">
                    <span class="text-xs text-slate-400 font-medium">Status:</span>
                    <span id="user-status-badge" class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">Connecting…</span>
                </div>

                <!-- Dark Mode Toggle -->
                <div x-data="darkModeToggle()" class="relative">
                    <button @click="toggle()" type="button" class="relative p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors overflow-hidden" title="Toggle Dark Mode">
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg x-show="isDark" x-transition:enter="theme-toggle-icon" x-transition:enter-start="rotate-90 opacity-0" x-transition:enter-end="rotate-0 opacity-100" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg x-show="!isDark" x-transition:enter="theme-toggle-icon" x-transition:enter-start="-rotate-90 opacity-0" x-transition:enter-end="rotate-0 opacity-100" class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>

                <!-- Notification Bell -->
                <div x-data="{ open: false, notifCount: 0, followups: [], payments: [], renewals: [] }" 
                     @notifs-updated.window="notifCount = $event.detail.count; followups = $event.detail.followups; payments = $event.detail.payments; renewals = $event.detail.renewals;"
                     class="relative">
                    <button @click="open = !open" class="relative p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="notifCount > 0" x-text="notifCount" style="display: none;" class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white"></span>
                    </button>

                    <!-- Dropdown Content -->
                    <div x-show="open" @click.outside="open = false" x-transition style="display: none;"
                         class="absolute top-full right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50 flex flex-col max-h-[80vh]">
                        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 text-sm">Notifications</h3>
                            <button @click="open = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </div>
                        <div class="overflow-y-auto flex-1 p-2 space-y-1">
                            <template x-if="notifCount === 0">
                                <div class="p-4 text-center text-slate-400 text-sm">
                                    No new notifications.
                                </div>
                            </template>
                            
                            <!-- Followups -->
                            <template x-if="followups.length > 0">
                                <div class="mb-2">
                                    <p class="px-2 py-1 text-[10px] font-black uppercase text-amber-500 tracking-wider">Follow-ups Due (<span x-text="followups.length"></span>)</p>
                                    <template x-for="notif in followups" :key="notif.id">
                                        <a :href="`/leads/${notif.id}`" class="block p-2 rounded-xl hover:bg-slate-50 transition-colors group">
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-amber-600 transition" x-text="notif.name"></p>
                                            <p class="text-xs text-slate-500" x-text="'Due: ' + notif.time"></p>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Payments -->
                            <template x-if="payments.length > 0">
                                <div class="mb-2">
                                    <p class="px-2 py-1 text-[10px] font-black uppercase text-emerald-500 tracking-wider">Pending Approvals (<span x-text="payments.length"></span>)</p>
                                    <template x-for="notif in payments" :key="notif.id">
                                        <a href="/payments" class="block p-2 rounded-xl hover:bg-slate-50 transition-colors border-l-2 border-emerald-400 group">
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition" x-text="'INR ' + notif.amount"></p>
                                            <p class="text-[10px] text-slate-500" x-text="notif.lead_name + ' via ' + notif.agent_name"></p>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Renewals -->
                            <template x-if="renewals.length > 0">
                                <div class="mb-2">
                                    <p class="px-2 py-1 text-[10px] font-black uppercase text-rose-500 tracking-wider">Expiring Near (<span x-text="renewals.length"></span>)</p>
                                    <template x-for="notif in renewals" :key="notif.id">
                                        <a :href="`/leads/${notif.id}`" class="block p-2 rounded-xl hover:bg-slate-50 transition-colors border-l-2 border-rose-400 group">
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-rose-600 transition" x-text="notif.name"></p>
                                            <p class="text-xs text-slate-500" x-text="'Expires: ' + notif.expires_on"></p>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- User Avatar -->
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </a>
            </header>

            <!-- ───────────── PAGE CONTENT ───────────── -->
            <main class="main-content flex-1 overflow-y-auto">
                {{-- Page Heading (Slot) --}}
                @if (isset($header))
                    <header class="bg-white border-b border-slate-100 shadow-sm sticky top-0 z-30">
                        <div class="px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                {{ $slot }}
            </main>
        </div>
    
    <!-- Command Palette Modal -->
    <x-global-search />

    <!-- Notification Toast Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-[90] space-y-3 w-96"></div>

    <!-- Admin Broadcast Ticker -->
    <div id="admin-ticker" class="fixed top-14 left-0 right-0 z-[45] bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 text-white overflow-hidden transition-all duration-500" style="display: none; height: 0;" :style="sidebarOpen ? 'margin-left: 15rem' : 'margin-left: 4rem'">
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

                    const serverAdvisoryId = parseInt(data.latest_advisory_id) || 0;
                    const localAdvisoryId = parseInt(lastAdvisoryId) || 0;
                    if (localAdvisoryId > 0 && serverAdvisoryId > localAdvisoryId) {
                        const tipText = data.latest_advisory_text || 'New advisory call broadcasted';
                        showToast({
                            title: 'New Market Call',
                            body: tipText,
                            url: "{{ route('advisory-calls.index') }}",
                            action_label: 'VIEW CALLS',
                            copyText: tipText
                        });
                        try { new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play().catch(() => {}); } catch(e) {}
                    }
                    if (serverAdvisoryId > localAdvisoryId) { lastAdvisoryId = serverAdvisoryId; localStorage.setItem('lastAdvisoryId', serverAdvisoryId); }

                    if (data.new_announcement) {
                        showToast({ title: data.new_announcement.title, body: data.new_announcement.body, type: data.new_announcement.type, action_label: 'OK' });
                        if (data.new_announcement.type === 'success') { try { new Audio('https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3').play().catch(() => {}); } catch(e) {} }
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

        setInterval(checkNotifications, 10000);
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
</body>

</html>
