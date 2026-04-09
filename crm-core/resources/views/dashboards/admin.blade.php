<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter">Command Center</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Real-time enterprise overview & metrics.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="glass-card px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-lg">
                Generate Reports
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="Active Agents" 
            :value="number_format($stats['active_agents'] ?? 0)" 
            icon="users" 
            color="indigo" 
        />
        <x-stat-card 
            title="Total Revenue (MTD)" 
            :value="'₹' . number_format($stats['monthly_revenue'] ?? 0, 0)" 
            icon="currency-rupee" 
            color="emerald" 
            :trend="round((($stats['today_revenue'] ?? 0) / ($stats['yesterday_revenue'] ?: 1) - 1) * 100)"
            :trendUp="($stats['today_revenue'] ?? 0) >= ($stats['yesterday_revenue'] ?? 0)"
        />
        <x-stat-card 
            title="Conversion (Week)" 
            :value="round(($stats['new_clients_week'] ?? 0) / ($stats['leads_this_week'] ?: 1) * 100) . '%'" 
            icon="target" 
            color="blue" 
        />
        <x-stat-card 
            title="Pending Approvals" 
            :value="number_format($stats['pending_approvals'] ?? 0)" 
            icon="check-circle" 
            color="rose" 
        />
    </div>

    <!-- Main Dashboard Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Revenue Trajectory (ApexCharts) -->
        <div class="lg:col-span-3 glass-card p-10 rounded-[2.5rem] bg-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Revenue Dynamics</h3>
                    <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mt-1">Enterprise Trajectory • MTD Performance</p>
                </div>
                <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-800 p-2 rounded-2xl border border-slate-100 dark:border-slate-700">
                    <button class="px-5 py-2.5 rounded-xl bg-white dark:bg-slate-700 text-[10px] font-black uppercase text-indigo-600 shadow-sm transition-all">Line</button>
                    <button class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase text-slate-400 hover:text-indigo-600 transition-all">Bar</button>
                    <div class="h-6 w-[2px] bg-slate-200 dark:bg-slate-600 mx-2"></div>
                    <select class="bg-transparent border-none text-[10px] font-black uppercase tracking-widest text-slate-500 focus:ring-0">
                        <option>Current Month</option>
                        <option>Last Quarter</option>
                    </select>
                </div>
            </div>
            
            <div id="revenueChart" class="min-h-[400px]"></div>
        </div>

        <!-- Productivity Roadmap -->
        <div class="lg:col-span-1 h-full">
            <x-dashboard.bento-roadmap 
                :targetProgress="$target_progress" 
                :nextLead="$today_followups->first()" 
                :upcomingFollowups="$today_followups->skip(1)->take(4)" 
            />
        </div>
    </div>

    <!-- Secondary Insights Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Leaderboard -->
        <div class="lg:col-span-1">
            <x-dashboard.leaderboard :leaderboard="$leaderboard" />
        </div>

        <!-- System Health & Activity Feed -->
        <div class="lg:col-span-2 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Health Metrics -->
                <div class="glass-card p-8 rounded-[2rem]">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-[0.2em] mb-8">Ecosystem Health</h3>
                    <div class="grid grid-cols-2 gap-6">
                        @foreach([
                            ['label' => 'Uptime', 'val' => '99.9%', 'color' => 'emerald'],
                            ['label' => 'Latency', 'val' => '12ms', 'color' => 'blue'],
                            ['label' => 'Sync', 'val' => 'Live', 'color' => 'indigo'],
                            ['label' => 'Queue', 'val' => '0 pending', 'color' => 'rose'],
                        ] as $item)
                        <div class="space-y-1">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $item['label'] }}</p>
                            <p class="text-xl font-black text-slate-800 dark:text-white">{{ $item['val'] }}</p>
                            <div class="w-8 h-1 bg-{{ $item['color'] }}-500 rounded-full"></div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Active Agents (Mini) -->
                <div class="glass-card p-8 rounded-[2rem] bg-indigo-600 text-white overflow-hidden relative">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <h3 class="text-sm font-black uppercase tracking-[0.2em] opacity-60 mb-8">Active Power</h3>
                    <p class="text-5xl font-black tracking-tighter mb-2">{{ $stats['active_agents'] ?? 0 }}</p>
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80">Agents Online Now</p>
                    <button class="mt-8 text-[9px] font-black uppercase tracking-[0.2em] py-3 px-6 bg-white/20 hover:bg-white/30 rounded-xl transition-all">Monitor Team →</button>
                </div>
            </div>

            <!-- Approval Queue -->
            <div class="glass-card p-8 rounded-[2rem]">
                 <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-[0.2em]">Compliance Gate</h3>
                    <a href="#" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Verify All →</a>
                </div>
                <div class="space-y-4">
                    @forelse($pending_payments ?? [] as $payment)
                        <div class="flex items-center justify-between p-5 rounded-3xl bg-slate-50/50 hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all border border-transparent hover:border-slate-100 group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center font-black text-slate-900 border border-slate-100 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    {{ strtoupper(substr($payment->lead?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $payment->lead?->name ?? 'Unknown Lead' }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">₹{{ number_format($payment->amount) }} • {{ $payment->package?->name ?? 'Premium Item' }}</p>
                                </div>
                            </div>
                            <button class="w-10 h-10 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-emerald-500 shadow-sm hover:bg-emerald-500 hover:text-white transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="py-12 text-center opacity-40">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] font-sans">Verification Queue Empty</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const revenueTrend = {!! json_encode(array_values($revenue_trend ?? [])) !!};
        const revenueLabels = {!! json_encode(array_keys($revenue_trend ?? [])) !!};

        const options = {
            series: [{
                name: 'Revenue',
                data: revenueTrend
            }],
            chart: {
                type: 'area',
                height: 400,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#4F46E5'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 4
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: revenueLabels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontWeight: 700,
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) { return "₹" + (val / 1000) + "k" },
                    style: {
                        colors: '#94a3b8',
                        fontWeight: 700,
                        fontSize: '10px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'dark',
                x: { show: true },
                y: {
                    title: { formatter: () => 'Revenue: ' }
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#revenueChart"), options);
        chart.render();
    });
</script>
