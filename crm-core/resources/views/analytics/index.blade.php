<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-slate-800 tracking-tight uppercase">Intelligence Command Center</h2>
            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl border border-slate-200">
                <a href="?filter=today" class="px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $filter === 'today' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-400 hover:text-slate-600' }}">Today</a>
                <a href="?filter=week" class="px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $filter === 'week' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-400 hover:text-slate-600' }}">Week</a>
                <a href="?filter=month" class="px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $filter === 'month' ? 'bg-white text-indigo-600 shadow-sm border border-slate-200' : 'text-slate-400 hover:text-slate-600' }}">Month</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Primary Bento Row: Pulse & Projection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Revenue Trajectory -->
                <div class="md:col-span-2 bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8 flex flex-col">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-1">Predictive Engine</p>
                            <h3 class="text-2xl font-black text-slate-800">Revenue Trajectory</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Aggressive Projection</p>
                            <p class="text-2xl font-black text-emerald-600">₹{{ number_format($projectedRevenue) }}</p>
                        </div>
                    </div>
                    <div id="trajectoryChart" class="flex-1 min-h-[300px]"></div>
                </div>

                <!-- Ecosystem Health Pulse -->
                <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 p-8 text-white relative overflow-hidden flex flex-col">
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.2em] mb-4">Ecosystem Health</p>
                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between items-end mb-2">
                                    <p class="text-xs font-bold text-indigo-100">Monthly Target Completion</p>
                                    <p class="text-2xl font-black">{{ round(($companyAchievement / max(1, $companyTarget)) * 100, 1) }}%</p>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-3">
                                    <div class="bg-white h-full rounded-full transition-all duration-1000" style="width: {{ min(100, ($companyAchievement / max(1, $companyTarget)) * 100) }}%"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white/10 rounded-2xl p-4">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-indigo-200 mb-1">Daily Vel.</p>
                                    <p class="text-lg font-black italic">₹{{ number_format($dailyVelocity) }}</p>
                                </div>
                                <div class="bg-white/10 rounded-2xl p-4">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-indigo-200 mb-1">Achievement</p>
                                    <p class="text-lg font-black italic">₹{{ number_format($companyAchievement) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative SVG -->
                    <div class="absolute -bottom-10 -right-10 opacity-10">
                        <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                </div>
            </div>

            <!-- AI Auditor Row -->
            <div x-data="{ 
                loading: false, 
                report: '', 
                runAudit() {
                    this.loading = true;
                    fetch('{{ route('analytics.audit') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.report = data.audit;
                        this.loading = false;
                    })
                    .catch(() => {
                        this.report = 'Audit failed. Check connection.';
                        this.loading = false;
                    });
                }
            }" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8 relative overflow-hidden">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0012 18.75c-1.03 0-1.9-.4-2.593-1.02l-.547-.548z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Gemini Ecosystem Auditor</h3>
                                <p class="text-[10px] font-black text-slate-400 tracking-[0.2em] uppercase">Deep Intelligence Scan</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <template x-if="!report && !loading">
                                <p class="text-sm font-medium text-slate-500">Run a manual audit to identify sales bottlenecks and receive aggressive strategic advice for the current month.</p>
                            </template>
                            <div x-show="loading" class="space-y-2">
                                <div class="h-3 bg-slate-100 rounded-full w-3/4 animate-pulse"></div>
                                <div class="h-3 bg-slate-50 rounded-full w-full animate-pulse"></div>
                            </div>
                            <div x-show="report" class="text-slate-600 font-medium italic text-sm leading-relaxed whitespace-pre-wrap animate-fade-in" x-text="report"></div>
                        </div>
                    </div>
                    <button @click="runAudit()" :disabled="loading" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-slate-800 transition active:scale-95 disabled:opacity-50 shadow-xl shadow-slate-200 flex items-center gap-3">
                        <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="loading ? 'Auditing...' : 'Run AI Audit'"></span>
                    </button>
                </div>
            </div>

            <!-- Bottom Row: Multi-Metric Matrix -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Agent Distribution -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 p-8 flex flex-col lg:col-span-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Revenue Distribution</p>
                    <div id="distributionChart" class="flex-1 min-h-[300px]"></div>
                </div>

                <!-- Strategic Leaderboard -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-xl font-black text-slate-800 tracking-tight uppercase">Agent Performance Matrix</h4>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="px-8 py-4">Agent</th>
                                    <th class="px-4 py-4 text-center">Efficiency</th>
                                    <th class="px-4 py-4 text-right">Sales Volume</th>
                                    <th class="px-4 py-4 text-right">KPI</th>
                                    <th class="px-8 py-4 text-right">Momentum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 font-bold">
                                @foreach($employeeMetrics->sortByDesc('total_sale') as $metric)
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-8 py-5">
                                            <p class="text-sm text-slate-900">{{ $metric['name'] }}</p>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $metric['role'] }}</p>
                                        </td>
                                        <td class="px-4 py-5">
                                            <div class="flex items-center justify-center">
                                                <span class="px-3 py-1 bg-slate-100 text-slate-800 rounded-lg text-[10px] tracking-tight border border-slate-200">
                                                    {{ $metric['active_time'] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-5 text-right font-black text-slate-900">₹{{ number_format($metric['total_sale']) }}</td>
                                        <td class="px-4 py-5 text-right">
                                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] border border-indigo-100">
                                                {{ $metric['kpi_score'] }}%
                                            </span>
                                        </td>
                                        <td class="px-8 py-5">
                                            <div class="flex items-center justify-end gap-3">
                                                <div class="w-20 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000" style="width: {{ min(100, $metric['percentage']) }}%"></div>
                                                </div>
                                                <span class="text-xs font-black text-slate-900">{{ $metric['percentage'] }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Trajectory Chart
            const trajectoryOptions = {
                series: [{
                    name: 'Current Performance',
                    data: [0, {{ $companyAchievement / 2 }}, {{ $companyAchievement }}]
                }, {
                    name: 'Target Forecast',
                    data: [{{ $companyAchievement }}, {{ ($companyAchievement + $projectedRevenue) / 2 }}, {{ $projectedRevenue }}],
                    dashArray: 5
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    sparkline: { enabled: false }
                },
                colors: ['#4f46e5', '#10b981'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: ['Start', 'Current', 'Projected'],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#94a3b8', fontWeight: 600 } }
                },
                yaxis: { show: false },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }
            };
            new ApexCharts(document.querySelector("#trajectoryChart"), trajectoryOptions).render();

            // Distribution Chart
            const distributionOptions = {
                series: @json($employeeMetrics->pluck('total_sale')->values()),
                chart: { type: 'donut', height: 300 },
                labels: @json($employeeMetrics->pluck('name')->values()),
                colors: ['#4f46e5', '#818cf8', '#6366f1', '#4338ca', '#3730a3'],
                legend: { position: 'bottom', fontWeight: 600 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { fontWeight: 900 },
                                value: { fontWeight: 900 },
                                total: {
                                    show: true,
                                    label: 'TOTAL',
                                    formatter: function (w) {
                                        return '₹' + {{ $companyAchievement }}.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector("#distributionChart"), distributionOptions).render();
        });
    </script>
</x-app-layout>
