<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Welcome back, {{ auth()->user()->name }} 👋</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Here's your productivity pulse for today.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="glass-card px-4 py-2 rounded-2xl flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">System Live</span>
            </div>
        </div>
    </div>

    <!-- Primary Bento Grid (Productivity Focused) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Stats & Charts (Visual Core) -->
        <div class="lg:col-span-3 space-y-8">
            <!-- High Intensity KPI Row -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach([
                    ['label' => 'Total Leads', 'val' => $stats['assigned_total'] ?? 0, 'icon' => 'users', 'color' => 'indigo'],
                    ['label' => 'Calls Today', 'val' => $stats['calls_today'] ?? 0, 'icon' => 'phone', 'color' => 'blue'],
                    ['label' => 'Revenue MTD', 'val' => '₹' . number_format(($stats['revenue'] ?? 0) / 1000, 1) . 'k', 'icon' => 'currency-rupee', 'color' => 'emerald'],
                    ['label' => 'KPI Score', 'val' => ($kpi['total_score'] ?? 85) . '/100', 'icon' => 'zap', 'color' => 'amber'],
                ] as $card)
                    <div class="glass-card p-6 rounded-[2rem] hover:scale-105 transition-all">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-xl bg-{{ $card['color'] }}-500/10 flex items-center justify-center text-{{ $card['color'] }}-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $card['label'] }}</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 leading-none tracking-tighter">{{ $card['val'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Activity Analytics -->
            <div class="glass-card p-8 rounded-[2.5rem]">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">Performance Velocity</h3>
                        <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mt-1">7-Day Engagement Patterns</p>
                    </div>
                    <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-100 flex gap-2">
                         <span class="px-4 py-1.5 rounded-lg bg-white shadow-sm text-[10px] font-black uppercase text-indigo-600">Calling Activity</span>
                    </div>
                </div>
                <div id="baActivityChart" class="min-h-[300px]"></div>
            </div>

            <x-dashboard.priority-queue :leads="$my_leads ?? collect()" title="Next Best Actions" />
        </div>

        <!-- Sticky Productivity Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <x-dashboard.bento-roadmap 
                :targetProgress="$target_progress" 
                :nextLead="$today_followups->first()" 
                :upcomingFollowups="$upcoming_followups ?? collect()" 
            />

            <!-- Market Intelligence -->
            <div class="glass-card p-8 rounded-[2rem] bg-slate-900 text-white relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl group-hover:bg-rose-500/20 transition-all duration-700"></div>
                <div class="relative z-10 space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Live Intel</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold mb-2">"{{ $live_market_call->title ?? 'Market Volatility Alert' }}"</h3>
                        <p class="text-xs text-slate-400 line-clamp-2 italic opacity-80">
                            {{ $live_market_call->call_summary ?? 'Stay defensive at current levels. Monitor 21,500 index support for fresh entries.' }}
                        </p>
                    </div>
                    <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/10 rounded-2xl text-[9px] font-black uppercase tracking-[0.2em] transition-all">Join Broadcast →</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // BA Activity Chart (7 Days)
        const activityData = {!! json_encode(array_column($call_volume ?? [], 'count')) !!};
        const activityLabels = {!! json_encode(array_column($call_volume ?? [], 'date')) !!};

        const activityOptions = {
            series: [{
                name: 'Calls',
                data: activityData
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '40%',
                    distributed: false,
                }
            },
            colors: ['#4F46E5'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: activityLabels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#94a3b8', fontWeight: 700, fontSize: '10px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94a3b8', fontWeight: 700, fontSize: '10px' }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } }
            },
            tooltip: { theme: 'dark' }
        };

        const activityChart = new ApexCharts(document.querySelector("#baActivityChart"), activityOptions);
        activityChart.render();
    });
</script>
