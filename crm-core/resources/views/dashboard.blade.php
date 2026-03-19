<x-app-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $greetingEmoji = $hour < 12 ? 'â˜€ï¸' : ($hour < 17 ? 'ðŸ‘‹' : 'ðŸŒ™');
    @endphp

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             SALES TIP OF THE HOUR â€” SYNCED WIDGET
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <style>
            @keyframes tipFlipIn {
                0% { opacity: 0; transform: rotateX(-90deg) scale(0.95); }
                60% { opacity: 1; transform: rotateX(5deg) scale(1.02); }
                100% { opacity: 1; transform: rotateX(0deg) scale(1); }
            }
            @keyframes tipPulseGlow {
                0%, 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.15); }
                50% { box-shadow: 0 0 0 8px rgba(99, 102, 241, 0); }
            }
            .tip-flip-animate {
                animation: tipFlipIn 0.7s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            }
            .tip-card-glow {
                animation: tipPulseGlow 3s ease-in-out infinite;
            }
            .tip-copied-toast {
                animation: tipFlipIn 0.3s ease forwards;
            }
        </style>
        <div id="sales-tip-widget" class="relative bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 rounded-2xl p-5 shadow-lg border border-indigo-500/20 tip-card-glow overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: url('data:image/svg+xml,<svg width=&quot;40&quot; height=&quot;40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><circle cx=&quot;20&quot; cy=&quot;20&quot; r=&quot;1.5&quot; fill=&quot;white&quot;/></svg>'); background-size: 40px 40px;"></div>
            <div class="relative flex items-start gap-4">
                <!-- Icon -->
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center border border-white/20 shadow-inner">
                    <span class="text-2xl">ðŸ’¡</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Sales Tip of the Hour</h3>
                        <span id="tip-counter" class="text-[9px] font-black text-white/40 bg-white/10 px-2 py-0.5 rounded-full">#1/100</span>
                        <span id="tip-timer" class="text-[9px] font-bold text-white/30 ml-auto hidden sm:inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span id="tip-next-change"></span>
                        </span>
                    </div>
                    <!-- Question -->
                    <p id="tip-question" class="text-white font-bold text-lg sm:text-xl leading-snug tip-flip-animate mb-3" style="perspective: 800px;">
                        Loading question...
                    </p>
                    <!-- Strategy -->
                    <div id="tip-strategy-container" class="bg-black/20 backdrop-blur-md rounded-xl p-3 border border-white/10 tip-flip-animate" style="animation-delay: 0.1s">
                        <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300 mb-1">Winning Strategy</p>
                        <p id="tip-strategy" class="text-white/80 text-xs font-semibold leading-relaxed">
                            Loading strategy...
                        </p>
                    </div>
                </div>
                <!-- Copy Button -->
                <button id="tip-copy-btn" onclick="copySalesTip()" class="flex-shrink-0 mt-1 group relative bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all border border-white/20 hover:border-white/40 hover:scale-105 active:scale-95 flex items-center gap-2 shadow-sm">
                    <svg id="tip-copy-icon" class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span id="tip-copy-label">Copy</span>
                </button>
            </div>
        </div>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             GREETING HEADER + DATE FILTER TABS
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900">{{ $greeting }}, {{ Auth::user()->name }} {{ $greetingEmoji }}</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ now()->format('l, F j') }} Â· Here's your CRM snapshot</p>
            </div>

            {{-- Date Filter Tabs --}}
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-sm font-semibold">
                @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'Month', 'ytd' => 'YTD'] as $key => $label)
                    <a href="?period={{ $key }}"
                       class="px-4 py-2 rounded-lg transition-all {{ (request('period', 'week') === $key) ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <div class="w-px h-6 bg-slate-200 mx-1"></div>
                <a href="{{ route('leads.index', ['tab' => 'trial']) }}" class="px-4 py-2 rounded-lg transition-all text-slate-600 hover:text-slate-800 flex items-center gap-1 bg-white border border-slate-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    Free Trial
                </a>
            </div>
        </div>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             LIVE LEADERBOARD (visible to ALL roles)
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" id="leaderboard-section">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <span class="text-lg">ðŸ†</span>
                    <h2 class="font-bold text-slate-900 text-base">Live Leaderboard â€” Today</h2>
                    <span class="flex items-center gap-1 text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                        LIVE
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="leaderboard-updated" class="text-[10px] text-slate-400 font-mono hidden">Updated <span id="leaderboard-time"></span></span>
                    <a href="{{ route('reports.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                        Full Report <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            {{-- Initial server-rendered (instant display on load) --}}
            <div id="leaderboard-body" class="space-y-3">
                @forelse($leaderboard as $index => $agent)
                    @php
                        $maxRevenue = $leaderboard->max(fn($a) => $a->payments_sum_amount ?? 0) ?: 1;
                        $pct = round((($agent->payments_sum_amount ?? 0) / $maxRevenue) * 100);
                        $medals = ['ðŸ¥‡','ðŸ¥ˆ','ðŸ¥‰'];
                        $barColors = ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-rose-400','bg-slate-300'];
                        $color = $barColors[$index] ?? 'bg-slate-200';
                    @endphp
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-6 text-center font-black text-sm {{ $index === 0 ? 'text-yellow-500' : ($index === 1 ? 'text-slate-400' : ($index === 2 ? 'text-amber-600' : 'text-slate-300')) }}">
                            {{ $medals[$index] ?? '#'.($index+1) }}
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $agent->name }}</p>
                                <span class="text-[10px] text-slate-400 font-mono ml-2 flex-shrink-0">{{ $agent->calls_today ?? 0 }} calls</span>
                            </div>
                            <div class="mt-1.5 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $color }} h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        <div class="text-sm font-black text-slate-700 flex-shrink-0">INR {{ number_format($agent->payments_sum_amount ?? 0) }}</div>
                    </div>
                @empty
                    <div class="text-center py-10" id="leaderboard-empty">
                        <p class="text-slate-400 text-sm">No sales recorded today.</p>
                        <p class="text-xs text-slate-300 mt-1">Be the first on the board!</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             MONTHLY LEADERBOARD (visible to ALL roles)
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" id="monthly-leaderboard-section">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <span class="text-lg">ðŸ“…</span>
                    <h2 class="font-bold text-slate-900 text-base">Monthly Leaderboard â€” <span id="monthly-lb-label">{{ now()->format('F Y') }}</span></h2>
                </div>
                <a href="{{ route('reports.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                    Full Report <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Server-rendered initial state --}}
            <div id="monthly-leaderboard-body" class="space-y-3">
                @php
                    $monthlyLeaderboard = \App\Models\User::whereIn('role', ['BA', 'SBA'])
                        ->withSum([
                            'payments' => fn($q) => $q
                                ->where('payments.status', 'Verified')
                                ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfDay()])
                        ], 'amount')
                        ->withCount([
                            'activities as calls_month' => fn($q) => $q
                                ->where('activity_type', 'Call Started')
                                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfDay()])
                        ])
                        ->orderByDesc('payments_sum_amount')
                        ->take(10)
                        ->get();
                    $monthlyMax = $monthlyLeaderboard->max('payments_sum_amount') ?: 1;
                    $mBars      = ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-rose-400','bg-slate-300'];
                    $mMedals    = ['ðŸ¥‡','ðŸ¥ˆ','ðŸ¥‰'];
                    $mRankClr   = ['text-yellow-500','text-slate-400','text-amber-600','text-slate-300'];
                @endphp

                @forelse($monthlyLeaderboard as $idx => $agent)
                    @php
                        $mPct   = round((($agent->payments_sum_amount ?? 0) / $monthlyMax) * 100);
                        $mColor = $mBars[$idx] ?? 'bg-slate-200';
                    @endphp
                    <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="w-6 text-center font-black text-sm {{ $mRankClr[$idx] ?? 'text-slate-300' }}">
                            {{ $mMedals[$idx] ?? '#'.($idx+1) }}
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-400 to-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($agent->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $agent->name }}</p>
                                <span class="text-[10px] text-slate-400 font-mono ml-2 flex-shrink-0">{{ $agent->calls_month ?? 0 }} calls</span>
                            </div>
                            <div class="mt-1.5 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $mColor }} h-full rounded-full transition-all duration-700" style="width: {{ $mPct }}%"></div>
                            </div>
                        </div>
                        <div class="text-sm font-black text-slate-700 flex-shrink-0">INR {{ number_format($agent->payments_sum_amount ?? 0) }}</div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-slate-400 text-sm">No monthly sales yet.</p>
                        <p class="text-xs text-slate-300 mt-1">First sale wins the throne!</p>
                    </div>
                @endforelse
            </div>
        </div>
        </div>
        @if($role === 'Admin')
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             KPI CARDS (Admin)
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            <!-- Total Leads -->
            <a href="{{ route('leads.index') }}" class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block group">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Leads</p>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ number_format($stats['total_leads']) }}</p>
                <div class="mt-2 flex items-center gap-1 text-emerald-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-bold">{{ $stats['leads_this_week'] ?? '0' }} leads this week</span>
                </div>
            </a>

            <!-- Paid Clients -->
            <a href="{{ route('clients.index') }}" class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block group">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paid Clients</p>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ $stats['paid_clients'] }}</p>
                <div class="mt-2 flex items-center gap-1 text-emerald-600">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-bold">{{ $stats['new_clients_week'] ?? '0' }} new this week</span>
                </div>
            </a>

            <!-- Monthly Revenue -->
            <a href="{{ route('payments.index') }}" class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monthly Revenue</p>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">INR {{ number_format($stats['monthly_revenue']) }}</p>
                <div class="mt-2 flex items-center gap-1 text-slate-400">
                    <span class="text-xs font-medium">â€” {{ $stats['monthly_revenue'] > 0 ? 'Revenue tracked' : 'No transactions yet' }}</span>
                </div>
            </a>

            <!-- Pending Approvals -->
            <a href="{{ route('payments.index') }}" class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm block">
                <div class="flex items-start justify-between mb-3">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Approvals</p>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-slate-900">{{ $stats['pending_approvals'] }}</p>
                <div class="mt-2">
                    @if($stats['pending_approvals'] > 0)
                        <span class="text-xs font-bold text-rose-500">Needs attention</span>
                    @else
                        <span class="text-xs font-medium text-slate-400">â€” All clear</span>
                    @endif
                </div>
            </a>
        </div>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             MAIN GRID: ROLE-SPECIFIC CONTENT
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Spacer placeholder to align with system overview --}}
            <div class="lg:col-span-2"></div>
            <div class="space-y-5">
                <!-- System Quick Stats -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-slate-900 text-base">System Overview</h2>
                        <span class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1 rounded-full">
                            <span class="pulse-dot w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                            {{ $system_health['active_users'] }} Active
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-black text-slate-900">{{ $system_health['leads_today'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">New Leads Today</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <p class="text-2xl font-black text-slate-900">{{ $system_health['active_users'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Active Users</p>
                        </div>
                    </div>

                    <!-- Lead Pipeline -->
                    <div class="mt-5">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Lead Pipeline</p>
                        @php
                            $pipeline = [
                                'New' => \App\Models\Lead::where('status', 'Cold Lead')->count(),
                                'Contacted' => \App\Models\Lead::where('status', 'Call Back')->count(),
                                'Interested' => \App\Models\Lead::where('status', 'Free Trial')->count(),
                                'Converted' => \App\Models\Lead::where('status', 'Paid Client')->count(),
                            ];
                            $maxPipe = max($pipeline) ?: 1;
                            $pipeColors = ['New' => 'bg-indigo-500', 'Contacted' => 'bg-violet-500', 'Interested' => 'bg-amber-500', 'Converted' => 'bg-emerald-500'];
                        @endphp
                        <div class="space-y-2.5">
                            @foreach($pipeline as $stage => $count)
                            <div>
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span class="text-slate-500 font-medium">{{ $stage }}</span>
                                    <span class="font-bold text-slate-700">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $pipeColors[$stage] }} h-full rounded-full transition-all duration-700" style="width: {{ round($count / $maxPipe * 100) }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3">Quick Actions</p>
                    <div class="space-y-2">
                        <a href="{{ route('leads.create') }}" class="w-full flex items-center gap-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl px-4 py-3 text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add New Lead
                        </a>
                        <a href="{{ route('leads.index') }}" class="w-full flex items-center gap-3 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl px-4 py-3 text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Import Leads
                        </a>
                        <a href="{{ route('employees.index') }}" class="w-full flex items-center gap-3 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl px-4 py-3 text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            User Management
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             BOTTOM GRID: PENDING APPROVALS + LIVE FEED
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Pending Approvals -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-slate-900 text-base">Pending Approvals</h2>
                    <a href="{{ route('payments.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Manage All â†’</a>
                </div>
                <div class="space-y-3">
                    @forelse($pending_payments as $payment)
                        <div class="flex items-center gap-4 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-900 text-sm">INR {{ number_format($payment->amount) }}</p>
                                <p class="text-xs text-slate-500">For {{ $payment->lead->name ?? 'Unknown' }} Â· By {{ $payment->user->name ?? 'Unknown' }}</p>
                            </div>
                            <form action="{{ route('payments.verify', $payment) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" name="action" value="approve"
                                    class="text-xs font-bold bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition-colors">
                                    Approve
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-slate-400 text-sm font-medium">All clear! No pending approvals.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Live Market Feed -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="pulse-dot w-2 h-2 bg-red-500 rounded-full inline-block"></span>
                        <h2 class="font-bold text-slate-900 text-base">Live Market Feed</h2>
                    </div>
                    <a href="{{ route('advisory-calls.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">History â†’</a>
                </div>
                <div class="space-y-3">
                    @forelse($latest_calls as $call)
                        <div class="p-3 bg-slate-50 hover:bg-white hover:shadow-md rounded-xl border border-slate-100 transition-all group">
                            <div class="flex justify-between items-center mb-1.5">
                                @php
                                    $segColors = [
                                        'Index Option' => 'bg-blue-100 text-blue-700',
                                        'Stock Cash'   => 'bg-emerald-100 text-emerald-700',
                                        'Future'       => 'bg-amber-100 text-amber-700',
                                    ];
                                    $segColor = $segColors[$call->segment] ?? 'bg-indigo-100 text-indigo-700';
                                @endphp
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full {{ $segColor }}">{{ $call->segment }}</span>
                                <span class="text-[10px] text-slate-400">{{ $call->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-800 line-clamp-2 leading-relaxed">{{ $call->call_text }}</p>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-[10px] text-slate-400">By {{ $call->user->name }}</span>
                                <a href="{{ route('advisory-calls.show', $call) }}" class="text-[10px] font-bold text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">Details â†’</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-slate-400 text-sm">No active market calls right now.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <!-- Today's Follow-ups -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-slate-900 text-base">Today's Callbacks & Follow-ups</h2>
                    <a href="{{ route('leads.index', ['tab' => 'followup']) }}" class="text-xs font-bold text-indigo-600">View All â†’</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($today_followups as $followup)
                        <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="w-2.5 h-2.5 mt-1 rounded-full bg-indigo-500 flex-shrink-0"></span>
                            <div>
                                <a href="{{ route('leads.show', $followup) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600">{{ $followup->name }} <span class="text-xs font-normal text-slate-500 ml-1">({{ $followup->mobile }})</span></a>
                                <p class="text-xs font-bold text-slate-500 mt-0.5">Time: <span class="text-indigo-600">{{ $followup->next_follow_up ? $followup->next_follow_up->format('h:i A') : 'N/A' }}</span> <span class="text-slate-400 font-normal ml-1">Â· {{ $followup->status }}</span></p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6">
                            <p class="text-sm text-slate-400">No callbacks or follow-ups scheduled for today.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @elseif($role === 'Manager')
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             MANAGER DASHBOARD
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Team Members</p>
                <p class="text-3xl font-black text-slate-900">{{ $stats['total_team_members'] }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Daily Revenue</p>
                <p class="text-3xl font-black text-slate-900">INR {{ number_format($stats['daily_revenue']) }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Trials Running</p>
                <p class="text-3xl font-black text-slate-900">{{ $stats['trials_running'] }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pending Payments</p>
                <p class="text-3xl font-black text-slate-900">{{ $stats['pending_payments'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="font-bold text-slate-900 text-base mb-5">Agent Leaderboard</h2>
                <div class="space-y-3">
                    @foreach($agent_performance as $index => $agent)
                        <div class="flex items-center gap-3 p-3 rounded-xl {{ $index === 0 ? 'bg-yellow-50 border border-yellow-100' : 'bg-slate-50' }}">
                            <span class="font-black text-slate-400 w-5 text-sm">#{{ $index + 1 }}</span>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">{{ substr($agent->name, 0, 1) }}</div>
                            <div class="flex-1"><p class="font-bold text-slate-900 text-sm">{{ $agent->name }}</p><p class="text-xs text-slate-400">{{ $agent->active_leads }} Active Leads</p></div>
                            <p class="font-black text-indigo-600 text-sm">INR {{ number_format($agent->revenue) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="font-bold text-rose-600 text-base mb-5">Escalations &amp; Complaints</h2>
                <div class="space-y-3">
                    @forelse($escalations as $esc)
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-100">
                            <div class="flex justify-between mb-1"><p class="font-bold text-rose-900">{{ $esc->name }}</p><span class="text-xs bg-white px-2 py-0.5 rounded text-rose-600 font-bold">Escalated</span></div>
                            <p class="text-sm text-rose-700 mb-2">Assigned to: {{ $esc->assignee->name ?? 'Unassigned' }}</p>
                            <a href="{{ route('leads.show', $esc) }}" class="text-xs font-bold underline text-rose-800">Resolve Issue â†’</a>
                        </div>
                    @empty
                        <div class="text-center py-8"><p class="text-slate-400 text-sm">âœ… No active escalations.</p></div>
                    @endforelse
                </div>
            </div>
            <!-- Today's Follow-ups -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-bold text-slate-900 text-base">Today's Callbacks & Follow-ups</h2>
                    <a href="{{ route('leads.index', ['tab' => 'followup']) }}" class="text-xs font-bold text-indigo-600">View All â†’</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($today_followups as $followup)
                        <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="w-2.5 h-2.5 mt-1 rounded-full bg-indigo-500 flex-shrink-0"></span>
                            <div>
                                <a href="{{ route('leads.show', $followup) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600">{{ $followup->name }} <span class="text-xs font-normal text-slate-500 ml-1">({{ $followup->mobile }})</span></a>
                                <p class="text-xs font-bold text-slate-500 mt-0.5">Time: <span class="text-indigo-600">{{ $followup->next_follow_up ? $followup->next_follow_up->format('h:i A') : 'N/A' }}</span> <span class="text-slate-400 font-normal ml-1">Â· {{ $followup->status }}</span></p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6">
                            <p class="text-sm text-slate-400">No callbacks or follow-ups scheduled for today.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @elseif($role === 'BA' || $role === 'SBA')
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             BA / SBA DASHBOARD
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        @if(isset($pending_training) && $pending_training)
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-sm flex items-center justify-between animate-pulse-slow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl">ðŸš¨</span>
                    </div>
                    <div>
                        <h3 class="text-rose-900 font-black text-lg">Mandatory Training Pending</h3>
                        <p class="text-rose-700 text-sm mt-0.5">Please complete: <span class="font-bold">{{ $pending_training->title }}</span></p>
                        <p class="text-rose-600 text-xs mt-1 font-medium">Access to leads may be blocked until completed.</p>
                    </div>
                </div>
                <a href="{{ route('agent.learning.index') }}" class="px-5 py-2.5 bg-rose-600 text-white rounded-xl font-bold text-sm hover:bg-rose-700 hover:shadow-md transition-all whitespace-nowrap">
                    Start Learning Now
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assigned Total</p>
                <p class="text-3xl font-black text-slate-900">{{ $stats['assigned_total'] ?? 0 }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assigned Today</p>
                <p class="text-3xl font-black text-blue-600">{{ $stats['assigned_today'] ?? 0 }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Calls Today</p>
                <p class="text-3xl font-black text-emerald-600">{{ $stats['calls_today'] ?? 0 }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Follow-ups Due</p>
                <p class="text-3xl font-black text-amber-600">{{ $stats['followups_due'] ?? 0 }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Paid Approved</p>
                <p class="text-3xl font-black text-indigo-600">{{ $stats['paid_approved'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Priority Leads -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="font-bold text-slate-900 text-base">My Priority Leads</h2>
                    <a href="{{ route('leads.index') }}" class="text-xs font-bold text-indigo-600">View All â†’</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-100">
                                <th class="pb-3 text-left">Client</th>
                                <th class="pb-3 text-left">Status</th>
                                <th class="pb-3 text-left">Follow-up</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($my_leads as $lead)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3">
                                        <p class="font-bold text-slate-900">{{ $lead->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $lead->mobile }}</p>
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $lead->status === 'Paid Client' ? 'bg-emerald-100 text-emerald-700' : ($lead->status === 'Free Trial' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-600') }}">{{ $lead->status }}</span>
                                    </td>
                                    <td class="py-3 text-xs text-slate-600">{{ $lead->next_follow_up ? $lead->next_follow_up->format('d M h:i A') : 'â€”' }}</td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('leads.show', $lead) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-100 transition-colors">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-8 text-slate-400 text-sm">No priority leads found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Side Widgets -->
            <div class="space-y-5">
                <!-- My Revenue Card -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-200 mb-2">My Revenue</p>
                    <p class="text-4xl font-black">INR {{ number_format($stats['revenue'] ?? 0) }}</p>
                    <p class="text-xs text-indigo-300 mt-1 uppercase tracking-widest">Approved Sales</p>
                </div>

                <!-- Target Progress -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-slate-900 text-sm">Personal Target</h3>
                        <span class="text-xs font-black text-indigo-600">{{ $target_progress['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-500 h-full rounded-full transition-all duration-700" style="width: {{ min(100, $target_progress['percentage']) }}%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-[11px] text-slate-400">
                        <span>INR {{ number_format($target_progress['achieved']) }}</span>
                        <span>Goal: INR {{ number_format($target_progress['amount']) }}</span>
                    </div>
                </div>

                <!-- Today's Follow-ups -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="font-bold text-slate-900 text-sm mb-3">Today's Calls & Follow-ups</h3>
                    <div class="space-y-3">
                        @forelse($today_followups as $followup)
                            <div class="flex items-start gap-3 p-2 hover:bg-slate-50 rounded-lg transition-colors">
                                <span class="w-2 h-2 mt-1.5 rounded-full {{ $followup->status === 'Call Back' ? 'bg-blue-400' : 'bg-purple-400' }} flex-shrink-0"></span>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('leads.show', $followup) }}" class="text-sm font-bold text-slate-900 hover:text-indigo-600 block truncate">{{ $followup->name }}</a>
                                    <div class="flex justify-between items-center mt-0.5">
                                        <p class="text-[10px] uppercase font-black text-slate-400 tracking-wider">{{ $followup->status }}</p>
                                        <p class="text-xs font-bold text-indigo-600">{{ $followup->next_follow_up ? $followup->next_follow_up->format('h:i A') : 'No time' }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 px-2">No callbacks or follow-ups scheduled today.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>    </div>

    <script>
    // â”€â”€â”€ LIVE LEADERBOARD POLLING â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const LEADERBOARD_URL = "{{ route('leaderboard.live') }}";
    const MEDALS    = ['ðŸ¥‡','ðŸ¥ˆ','ðŸ¥‰'];
    const BARS      = ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-rose-400','bg-slate-300'];
    const RANK_CLR  = ['text-yellow-500','text-slate-400','text-amber-600','text-slate-300'];

    function formatINR(n) {
        return 'INR ' + Number(n).toLocaleString('en-IN');
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (ch) => {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function safeNumber(val, fallback = 0) {
        const n = Number(val);
        return Number.isFinite(n) ? n : fallback;
    }

    function buildLeaderboardRow(agent, i) {
        const medal    = MEDALS[i] ?? '#' + (i + 1);
        const barColor = BARS[i] ?? 'bg-slate-200';
        const rankClr  = RANK_CLR[i] ?? 'text-slate-300';
        const pct      = Math.max(0, Math.min(100, safeNumber(agent.pct, 0)));
        const name     = escapeHtml(agent.name || '');
        const initials = escapeHtml(agent.initials || '');
        const calls    = safeNumber(agent.calls, 0);
        const revenue  = safeNumber(agent.revenue, 0);

        return `
        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors">
            <div class="w-6 text-center font-black text-sm ${rankClr}">${medal}</div>
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                ${initials}
            </div>
            <div class="flex-1 min-w-0 pr-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-900 truncate">${name}</p>
                    <span class="text-[10px] text-slate-400 font-mono ml-2 flex-shrink-0">${calls} calls</span>
                </div>
                <div class="mt-1.5 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="${barColor} h-full rounded-full transition-all duration-700" style="width:${pct}%"></div>
                </div>
            </div>
            <div class="text-sm font-black text-slate-700 flex-shrink-0">${formatINR(revenue)}</div>
        </div>`;
    }

        
    
    function fetchLeaderboard(period, targetBodyId, emptyMessage) {
        fetch(`${LEADERBOARD_URL}?period=${period}`)
            .then(r => {
                if (!r.ok) throw new Error('Fetch failed');
                return r.json();
            })
            .then(data => {
                const body = document.getElementById(targetBodyId);
                if (!body) return;

                if (!data.leaderboard || data.leaderboard.length === 0) {
                    body.innerHTML = `<div class="text-center py-10">
                        <p class="text-slate-400 text-sm">${escapeHtml(emptyMessage)}</p>
                        <p class="text-xs text-slate-300 mt-1">Be the first on the board!</p>
                    </div>`;
                    return;
                }

                body.innerHTML = data.leaderboard.map((agent, i) => buildLeaderboardRow(agent, i)).join('');
            })
            .catch(e => console.error('Leaderboard error:', e));
    }

    // Initialize all periods
    ['today', 'week', 'month', 'ytd'].forEach(p => {
        const targetId = `leaderboard-${p}-body`;
        const msg = (p === 'today') ? 'No activity registered today yet.' : 'No leaderboard data for this period.';
        fetchLeaderboard(p, targetId, msg);
    });

    // ══════════════════════════════════════════════
    // SALES TIPS LOGIC (Q&A FORMAT)
    // ══════════════════════════════════════════════
    (function() {
        function getTipIndex() {
            const now = new Date();
            const hourStamp = now.getFullYear() + '-' + now.getMonth() + '-' + now.getDate() + '-' + now.getHours();
            let hash = 0;
            for (let i = 0; i < hourStamp.length; i++) {
                hash = ((hash << 5) - hash) + hourStamp.charCodeAt(i);
                hash |= 0;
            }
            return Math.abs(hash) % salesTips.length;
        }

        function getMinutesUntilNextChange() {
            return 60 - new Date().getMinutes();
        }

        let currentIdx = -1;

        function renderTip() {
            const idx = getTipIndex();
            if (idx === currentIdx) return;
            currentIdx = idx;

            const questionEl = document.getElementById('tip-question');
            const strategyEl = document.getElementById('tip-strategy');
            const counterEl = document.getElementById('tip-counter');
            const strategyContainer = document.getElementById('tip-strategy-container');
            
            if (!questionEl || !strategyEl) return;

            const tip = salesTips[idx];

            [questionEl, strategyContainer].forEach(el => {
                if (el) {
                    el.classList.remove('tip-flip-animate');
                    void el.offsetWidth;
                    el.classList.add('tip-flip-animate');
                }
            });

            questionEl.textContent = tip.question;
            strategyEl.textContent = tip.strategy;
            if (counterEl) counterEl.textContent = '#' + (idx + 1) + '/' + salesTips.length;
        }

        function updateTimer() {
            const el = document.getElementById('tip-next-change');
            if (el) {
                const mins = getMinutesUntilNextChange();
                el.textContent = 'Next in ' + mins + 'm';
            }
        }

        renderTip();
        updateTimer();

        setInterval(function() {
            renderTip();
            updateTimer();
        }, 15000);
    })();

    function copySalesTip() {
        const questionEl = document.getElementById('tip-question');
        const strategyEl = document.getElementById('tip-strategy');
        const labelEl = document.getElementById('tip-copy-label');
        const iconEl = document.getElementById('tip-copy-icon');
        
        if (!questionEl || !strategyEl) return;

        const text = `Q: ${questionEl.textContent}\nStrategy: ${strategyEl.textContent}`;
        
        navigator.clipboard.writeText(text).then(function() {
            labelEl.textContent = 'Copied!';
            iconEl.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>';
            setTimeout(function() {
                labelEl.textContent = 'Copy';
                iconEl.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>';
            }, 2000);
        });
    }
    </script>
</x-app-layout>
