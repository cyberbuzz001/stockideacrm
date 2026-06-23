<x-app-layout>
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $greetingEmoji = $hour < 12 ? '🌟' : ($hour < 17 ? '👋' : '🌙');
        $role = Auth::user()->role;
    @endphp

    <div class="space-y-8">

        {{-- Unified Greeting & Period Selector moved into role-specific blocks or made common --}}


        @if($role === 'Admin')
            <!-- ───────────── ADMIN VIEW ───────────── -->
            <div class="space-y-8">
                <!-- TOP SECTION: Greeting -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-[#111827] tracking-tight mb-1">
                            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, Admin 🌟
                        </h1>
                        <p class="text-sm font-bold text-[#6B7280] tracking-wide uppercase">
                            {{ now()->format('l, F j') }} · Here's your CRM snapshot
                        </p>
                    </div>

                    <div class="flex items-center bg-white p-1 rounded-xl border border-[#E5E7EB] shadow-sm">
                        @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'Month', 'ytd' => 'YTD'] as $key => $label)
                            <a href="?period={{ $key }}" 
                               class="px-4 py-1.5 rounded-lg text-xs font-black transition-all {{ (request('period', 'week') === $key) ? 'bg-[#4F46E5] text-white shadow-md shadow-indigo-100' : 'text-[#6B7280] hover:bg-[#F9FAFB]' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- LEADERBOARD ROW -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Card: Leaderboard Today -->
                    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-[#F3F4F6] flex justify-between items-center bg-white">
                            <h3 class="font-black text-sm text-[#111827] flex items-center gap-2">
                                <span class="text-lg">🏆</span> Live Leaderboard — Today
                            </h3>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1.5 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-[#10B981] rounded-full animate-pulse"></span>
                                    <span class="text-[9px] font-black text-[#10B981] uppercase tracking-widest">Live</span>
                                </div>
                                <a href="#" class="text-[10px] font-black text-[#4F46E5] uppercase tracking-widest hover:underline">Full Report</a>
                            </div>
                        </div>
                        <div class="flex-1 p-6 space-y-5">
                            @foreach($leaderboard->take(4) as $idx => $player)
                                <div class="flex items-center gap-4 group">
                                    <div class="w-6 text-center font-black text-xs {{ $idx == 0 ? 'text-amber-500' : ($idx == 1 ? 'text-slate-400' : ($idx == 2 ? 'text-amber-700' : 'text-[#9CA3AF]')) }}">
                                        {{ $idx == 0 ? '🥇' : ($idx == 1 ? '🥈' : ($idx == 2 ? '🥉' : $idx + 1)) }}
                                    </div>
                                    <div class="w-10 h-10 rounded-xl bg-[#F3F4F6] flex items-center justify-center font-black text-xs text-[#4F46E5] group-hover:bg-[#EEF2FF] transition-colors">
                                        {{ strtoupper(substr($player->name, 0, 2)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-end mb-1.5">
                                            <span class="font-black text-sm text-[#374151] truncate">{{ $player->name }}</span>
                                            <span class="font-mono text-sm font-black text-[#111827]">INR {{ number_format($player->payments_sum_amount ?? 0) }}</span>
                                        </div>
                                        <div class="w-full bg-[#F3F4F6] rounded-full h-1.5 overflow-hidden">
                                            @php $max_revenue = $leaderboard->max('payments_sum_amount') ?: 1; @endphp
                                            <div class="h-full bg-[#4F46E5] rounded-full group-hover:bg-[#6366F1] transition-all duration-700" style="width: {{ ($player->payments_sum_amount / $max_revenue) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Card: Monthly Leaderboard -->
                    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden flex flex-col">
                        <div class="p-6 border-b border-[#F3F4F6] bg-white flex justify-between items-center">
                            <h3 class="font-black text-sm text-[#111827] flex items-center gap-2">
                                <span class="text-lg">📅</span> Monthly Performance — {{ now()->format('F Y') }}
                            </h3>
                            <button class="p-1.5 rounded-lg hover:bg-[#F3F4F6] text-[#9CA3AF]">
                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="flex-1 p-6 space-y-6">
                            @if(isset($target_progress))
                            <div class="flex flex-col items-center relative text-center">
                                <!-- SVG Circular Gauge -->
                                <div class="relative w-28 h-28 flex items-center justify-center">
                                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="40" stroke="#F3F4F6" stroke-width="8" fill="none" />
                                        <?php
                                            $dashArray = 251.2;
                                            $percentage = min(100, max(0, $target_progress['percentage']));
                                            $dashOffset = $dashArray - ($dashArray * $percentage) / 100;
                                        ?>
                                        <circle cx="50" cy="50" r="40" stroke="url(#progressGradientAdmin)" stroke-width="8" fill="none" 
                                                stroke-linecap="round" 
                                                stroke-dasharray="{{ $dashArray }}" 
                                                stroke-dashoffset="{{ $dashOffset }}" 
                                                class="transition-all duration-1000 ease-out" />
                                        <defs>
                                            <linearGradient id="progressGradientAdmin" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" stop-color="#4F46E5" />
                                                <stop offset="100%" stop-color="#10B981" />
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-2xl font-black font-mono text-[#111827]">{{ $target_progress['percentage'] }}<span class="text-xs text-gray-400">%</span></span>
                                    </div>
                                </div>
                                <div class="mt-4 w-full flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <div class="text-left">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Achieved</p>
                                        <p class="text-xs font-bold text-emerald-600">INR {{ number_format($target_progress['achieved']) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Goal</p>
                                        <p class="text-xs font-bold text-slate-700">INR {{ number_format($target_progress['amount']) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-[#F3F4F6]">
                                <div class="p-4 rounded-xl bg-[#F9FAFB] border border-[#F3F4F6]">
                                    <p class="text-[9px] font-black text-[#9CA3AF] uppercase tracking-widest mb-1">Conversion Rate</p>
                                    <p class="text-xl font-black text-[#111827]">14.2%</p>
                                </div>
                                <div class="p-4 rounded-xl bg-[#F9FAFB] border border-[#F3F4F6]">
                                    <p class="text-[9px] font-black text-[#9CA3AF] uppercase tracking-widest mb-1">Avg. Ticket Size</p>
                                    <p class="text-xl font-black text-[#111827]">INR 8,420</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Sales Mastery Widget (Rotating Every 30 Min as requested for Shreesvarn CRM) -->
                @php
                    $masteryFile = resource_path('data/sales_mastery.json');
                    $tips = file_exists($masteryFile) ? json_decode(file_get_contents($masteryFile)) : [];
                @endphp
                @if(!empty($tips))
                <div x-data="{ 
                    tips: {{ json_encode($tips) }},
                    currentIndex: 0,
                    init() {
                        setInterval(() => {
                            this.currentIndex = (this.currentIndex + 1) % this.tips.length;
                        }, 1800000);
                    }
                }" class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-[24px] p-6 text-white shadow-xl shadow-indigo-200/50 mb-8 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                    <div class="relative flex items-center justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white/20 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider">💡 Sales Mastery Tip</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <h2 class="text-xl font-black tracking-tight mb-2" x-text="tips[currentIndex].title"></h2>
                            <p class="text-indigo-50 text-sm font-medium leading-relaxed max-w-2xl" x-text="tips[currentIndex].description"></p>
                        </div>
                        <div class="hidden sm:flex flex-col items-end gap-1">
                            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md border border-white/10">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- KPI STRIP (Admin) -->
                @php
                    $adminKpis = [
                        ['label' => 'Total Leads', 'value' => $stats['total_leads'] ?? 0],
                        ['label' => 'Paid Clients', 'value' => $stats['paid_clients'] ?? 0],
                        ['label' => 'Revenue (' . ucfirst(request('period', 'week')) . ')', 'value' => 'INR ' . number_format($stats['monthly_revenue'] ?? 0), 'success' => true],
                        ['label' => 'Pending Approvals', 'value' => $stats['pending_approvals'] ?? 0, 'alert' => ($stats['pending_approvals'] ?? 0) > 0],
                        ['label' => 'System Status', 'value' => 'Live', 'badge' => true],
                    ];
                @endphp
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-6">
                    @foreach($adminKpis as $kpi)
                        <div class="bg-white p-5 rounded-2xl border border-[#E5E7EB] shadow-sm hover:border-[#4F46E5] transition-colors group cursor-default">
                            <p class="text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest mb-2">{{ $kpi['label'] }}</p>
                            <div class="flex items-center justify-between">
                                <p class="text-lg font-black {{ isset($kpi['success']) && $kpi['success'] ? 'text-[#10B981]' : 'text-[#111827]' }}">
                                    {{ $kpi['value'] }}
                                </p>
                                @if(isset($kpi['trend']))
                                    <span class="text-[10px] font-black {{ strpos($kpi['trend'], '+') !== false ? 'text-[#10B981]' : 'text-[#EF4444]' }}">
                                        {{ $kpi['trend'] }}
                                    </span>
                                @endif
                                @if(isset($kpi['badge']) && $kpi['badge'])
                                    <span class="flex h-2 w-2 rounded-full bg-rose-500 animate-pulse"></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- BOTTOM SECTION (Grid for Approvals, Feed, Followups) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Left: Approvals + Feed -->
                    <div class="lg:col-span-8 space-y-8">
                        <!-- Pending Approvals -->
                        <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-[#F3F4F6] flex justify-between items-center bg-white">
                                <h3 class="font-black text-sm text-[#111827]">Pending Approvals</h3>
                                <a href="{{ route('payments.index') }}" class="text-[10px] font-black text-[#4F46E5] uppercase tracking-widest hover:underline">Manage All</a>
                            </div>
                            <div class="divide-y divide-[#F3F4F6]">
                                @forelse([] as $pay) {{-- Mocked for view --}}
                                @empty
                                    <div class="p-10 text-center">
                                        <p class="text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest mb-1">All clear</p>
                                        <p class="text-xs text-[#6B7280]">No pending payment approvals at the moment.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Live Feed -->
                        <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-[#F3F4F6] bg-white">
                                <h3 class="font-black text-sm text-[#111827] flex items-center gap-2">
                                    Live Feed <span class="flex h-2 w-2 bg-rose-500 rounded-full animate-ping"></span>
                                </h3>
                            </div>
                            <div class="p-6 space-y-6">
                                @foreach($latest_calls->take(3) as $call)
                                    <div class="flex gap-4 group">
                                        <div class="relative">
                                            <div class="w-2.5 h-2.5 rounded-full bg-[#4F46E5] ring-4 ring-indigo-50 mt-1"></div>
                                            <div class="absolute top-4 left-1.25 w-px h-full bg-[#F3F4F6] group-last:hidden"></div>
                                        </div>
                                        <div class="flex-1 pb-6 group-last:pb-0">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-[10px] font-black text-[#4F46E5] uppercase tracking-widest">{{ $call->segment }}</span>
                                                <span class="text-[10px] font-bold text-[#9CA3AF]">{{ $call->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs font-medium text-[#374151] leading-relaxed">
                                                {{ $call->call_text }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right: AI Dialer / Smart Feed -->
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-[#E5E7EB] shadow-sm overflow-hidden sticky top-24">
                        <div class="p-6 border-b border-[#F3F4F6] bg-white flex justify-between items-center">
                            <h3 class="font-black text-sm text-[#111827] flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> AI Dialer
                            </h3>
                            <span class="text-[9px] font-black text-white bg-indigo-600 px-2 py-0.5 rounded-full uppercase tracking-tighter pulse-dot">Active</span>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Currently Calling Mockup -->
                            <div class="p-4 rounded-2xl bg-slate-900 text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform">
                                <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/20 rounded-full blur-xl"></div>
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center animate-pulse">
                                        <i data-lucide="phone-outgoing" class="w-6 h-6 text-emerald-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black text-indigo-300 uppercase tracking-widest">Currently Calling</p>
                                        <p class="text-sm font-black text-white">Rahul Sharma</p>
                                        <p class="text-[10px] text-slate-400">Dialing mobile: +91 98XXX XXX01</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <button class="flex-1 py-2 bg-emerald-500 text-[10px] font-black rounded-lg hover:bg-emerald-600 transition-colors uppercase">Mute</button>
                                    <button class="flex-1 py-2 bg-rose-500 text-[10px] font-black rounded-lg hover:bg-rose-600 transition-colors uppercase">Hang Up</button>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-[#4F46E5] shadow-sm">
                                    <i data-lucide="calendar" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-[#111827]">{{ $today_followups->count() }} Calls in Queue</p>
                                    <p class="text-[10px] font-bold text-[#4F46E5] uppercase tracking-widest">Smart-Route Active</p>
                                </div>
                            </div>
                            <button class="w-full py-4 bg-[#4F46E5] text-white rounded-2xl text-[11px] font-black shadow-lg shadow-indigo-100 hover:bg-[#4338CA] transition-all transform hover:-translate-y-0.5 uppercase tracking-widest">
                                Open Full Dialer Panel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- ───────────── EMPLOYEE VIEW ───────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (KPIs + Priority Leads) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Employee KPI Strip -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @php
                            $personalKpis = [
                                ['label' => 'Assigned Total', 'value' => $stats['assigned_total'] ?? 0],
                                ['label' => 'Assigned Today', 'value' => $stats['assigned_today'] ?? 0],
                                ['label' => 'Calls Today', 'value' => $stats['calls_today'] ?? 0, 'alert' => (($stats['calls_today'] ?? 0) == 0)],
                                ['label' => 'Follow-ups Due', 'value' => $stats['followups_due'] ?? 0, 'badge' => ($stats['followups_due'] ?? 0) > 0],
                                ['label' => 'Paid Approved', 'value' => $stats['paid_approved'] ?? 0, 'success' => true],
                            ];
                        @endphp
                        @foreach($personalKpis as $pkpi)
                            <div class="bg-white p-4 rounded-xl border border-[#E5E7EB] shadow-sm">
                                <p class="text-[9px] font-black text-[#9CA3AF] uppercase tracking-widest mb-1">{{ $pkpi['label'] }}</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-2xl font-black {{ isset($pkpi['success']) && $pkpi['success'] ? 'text-[#10B981]' : (isset($pkpi['alert']) && $pkpi['alert'] ? 'text-amber-500' : 'text-[#111827]') }}">
                                        {{ $pkpi['value'] }}
                                    </p>
                                    @if(isset($pkpi['badge']) && $pkpi['badge'])
                                        <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 text-[10px] font-black">!</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- LEADERBOARD ROW (Employee) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Live Leaderboard -->
                        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden flex flex-col">
                            <div class="p-4 border-b border-[#F3F4F6] flex items-center justify-between">
                                <h3 class="font-bold text-sm text-[#111827]">Live Leaderboard</h3>
                                <div class="flex items-center gap-1.5 text-[9px] font-black text-[#10B981] bg-[#10B981]/10 px-2 py-0.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-[#10B981] rounded-full"></span> LIVE
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                @foreach($leaderboard->take(4) as $idx => $agent)
                                    @php
                                        $isMe = ($agent->id === Auth::id());
                                        $maxRev = $leaderboard->max('payments_sum_amount') ?: 1;
                                    @endphp
                                    <div class="flex items-center gap-3 p-2 rounded-lg {{ $isMe ? 'bg-[#EEF2FF] border border-[#4F46E5]/10' : '' }}">
                                        <div class="w-8 h-8 rounded-full bg-white border border-[#E5E7EB] flex items-center justify-center text-[10px] font-bold">
                                            {{ substr($agent->name, 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-[#111827] truncate">{{ $agent->name }} {{ $isMe ? '(You)' : '' }}</p>
                                            <div class="w-full bg-[#F3F4F6] rounded-full h-1 mt-1">
                                                <div class="bg-[#4F46E5] h-full rounded-full" style="width: {{ ($agent->payments_sum_amount / $maxRev) * 100 }}%"></div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono font-bold text-[#4F46E5]">INR {{ number_format($agent->payments_sum_amount) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                         <!-- Today's Follow-ups (Personal) -->
                        <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden flex flex-col">
                            <div class="p-4 border-b border-[#F3F4F6]">
                                <h3 class="font-bold text-sm text-[#111827]">Upcoming Follow-ups</h3>
                            </div>
                            <div class="p-4 space-y-3 flex-1 overflow-y-auto max-h-[300px]">
                                @forelse($today_followups as $tf)
                                    <div class="p-3 border border-[#F3F4F6] rounded-xl flex justify-between items-center hover:bg-[#F9FAFB] transition-colors cursor-pointer group" onclick="window.location='{{ route('leads.show', $tf) }}'">
                                        <div>
                                            <p class="text-xs font-black text-[#111827] group-hover:text-[#4F46E5] transition-colors">{{ $tf->name }}</p>
                                            <p class="text-[10px] text-[#9CA3AF]">{{ $tf->status }}</p>
                                        </div>
                                        <span class="text-[11px] font-bold text-[#4F46E5]">{{ $tf->next_follow_up ? $tf->next_follow_up->format('h:i A') : '—' }}</span>
                                    </div>
                                @empty
                                    <div class="py-10 text-center text-[#9CA3AF] text-xs font-bold uppercase">No tasks due</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Priority Leads Table -->
                    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm">
                        <div class="p-5 border-b border-[#F3F4F6] flex justify-between items-center">
                            <h3 class="font-bold text-[#111827]">My Priority Leads</h3>
                            <a href="{{ route('leads.index') }}" class="text-xs font-bold text-[#4F46E5]">View All →</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest border-b border-[#F3F4F6]">
                                        <th class="px-6 py-4 text-left">Client</th>
                                        <th class="px-6 py-4 text-left">Status</th>
                                        <th class="px-6 py-4 text-left">Follow-up</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F3F4F6]">
                                    @forelse($my_leads->take(6) as $lead)
                                        <tr class="group hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-bold text-[#111827]">{{ $lead->name }}</p>
                                                <p class="text-[11px] text-[#9CA3AF]">{{ $lead->mobile }}</p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase
                                                    {{ $lead->status === 'Paid Client' ? 'bg-[#ECFDF5] text-[#10B981]' : 
                                                       ($lead->status === 'Free Trial' ? 'bg-[#E0F2FE] text-[#0284C7]' : 'bg-[#F3F4F6] text-[#6B7280]') }}">
                                                    {{ $lead->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-[11px] font-medium text-[#111827]">
                                                {{ $lead->next_follow_up ? $lead->next_follow_up->format('d M, h:i A') : '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('leads.show', $lead) }}" class="text-[11px] font-bold text-[#4F46E5] hover:underline">Open Profile</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-10 text-center text-[#9CA3AF] text-sm font-medium">No priority leads found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Hero Card + Progress) -->
                <div class="space-y-8">
                    <!-- MY REVENUE HERO CARD -->
                    <div class="bg-gradient-to-br from-[#4F46E5] to-[#7C3AED] rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-100/70 mb-4">My Revenue</p>
                            <h3 class="text-4xl font-black font-mono mb-2">INR {{ number_format($stats['revenue'] ?? 0) }}</h3>
                            <p class="text-xs font-bold text-indigo-100/50 uppercase tracking-widest leading-relaxed">
                                Approved Sales <br> for current period
                            </p>
                        </div>
                    </div>

                    <!-- Dynamic Target Progress Widget -->
                    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm p-6 flex flex-col items-center relative overflow-hidden text-center">
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-400 to-indigo-500"></div>
                        <h4 class="font-black text-sm text-[#111827] w-full text-left mb-6 uppercase tracking-widest text-[10px] text-slate-400">Target Progress</h4>
                        
                        <!-- SVG Circular Gauge -->
                        <div class="relative w-36 h-36 flex items-center justify-center">
                            <!-- Background Circle -->
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" stroke="#F3F4F6" stroke-width="8" fill="none" />
                                <?php
                                    $dashArray = 251.2; // 2 * pi * 40
                                    $percentage = min(100, max(0, $target_progress['percentage']));
                                    $dashOffset = $dashArray - ($dashArray * $percentage) / 100;
                                ?>
                                <circle cx="50" cy="50" r="40" stroke="url(#progressGradient)" stroke-width="8" fill="none" 
                                        stroke-linecap="round" 
                                        stroke-dasharray="{{ $dashArray }}" 
                                        stroke-dashoffset="{{ $dashOffset }}" 
                                        class="transition-all duration-1000 ease-out" />
                                <defs>
                                    <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#4F46E5" />
                                        <stop offset="100%" stop-color="#10B981" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            
                            <!-- Inner Percentage Text -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-black font-mono text-[#111827]">{{ $target_progress['percentage'] }}<span class="text-sm text-gray-400">%</span></span>
                            </div>
                        </div>

                        <div class="mt-6 w-full flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <div class="text-left">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Achieved</p>
                                <p class="text-sm font-bold text-emerald-600">INR {{ number_format($target_progress['achieved']) }}</p>
                            </div>
                            <div class="h-6 w-px bg-slate-200"></div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Goal</p>
                                <p class="text-sm font-bold text-slate-700">INR {{ number_format($target_progress['amount']) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Alerts / Notifications -->
                    <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm p-6">
                        <h4 class="font-bold text-sm text-[#111827] mb-4">System Alerts</h4>
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></span>
                                <p class="text-xs text-[#4B5563] font-medium leading-relaxed">New market call released for <span class="font-bold text-[#111827]">Nifty Bank</span>. Check advisory.</p>
                            </div>
                            <div class="flex gap-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"></span>
                                <p class="text-xs text-[#4B5563] font-medium leading-relaxed">You have <span class="font-bold text-[#111827]">3 follow-ups</span> due in the next hour.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        @endif

    </div>

    <style>
        .font-mono { font-family: 'DM Mono', monospace !important; }
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
    </style>

</x-app-layout>
