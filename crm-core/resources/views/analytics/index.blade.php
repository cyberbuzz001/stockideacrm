<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Smart Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Company Overview Card -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl shadow-xl p-8 mb-8 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-bold text-indigo-100 uppercase tracking-widest mb-2 flex items-center gap-2">
                            Company Performance
                            <span class="bg-indigo-500/50 px-2 py-0.5 rounded text-xs">
                                {{ $filter === 'today' ? 'Today' : ($filter === 'week' ? 'This Week' : 'This Month') }}
                            </span>
                        </p>
                        <h3 class="text-4xl font-black flex items-center gap-4">
                            @if($filter === 'today')
                                {{ now()->format('d M Y') }}
                            @elseif($filter === 'week')
                                {{ now()->startOfWeek()->format('d M') }} - {{ now()->format('d M Y') }}
                            @else
                                {{ now()->format('F Y') }}
                            @endif
                        </h3>
                    </div>
                    <div class="text-right flex flex-col items-end gap-3">
                        <form method="GET" action="{{ route('analytics.index') }}" class="flex items-center gap-2 bg-white/10 rounded-xl p-1 backdrop-blur-md border border-white/20">
                            <button type="submit" name="filter" value="today" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all {{ $filter === 'today' ? 'bg-white text-indigo-900 shadow-sm' : 'text-indigo-100 hover:bg-white/10' }}">Today</button>
                            <button type="submit" name="filter" value="week" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all {{ $filter === 'week' ? 'bg-white text-indigo-900 shadow-sm' : 'text-indigo-100 hover:bg-white/10' }}">Week</button>
                            <button type="submit" name="filter" value="month" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all {{ $filter === 'month' ? 'bg-white text-indigo-900 shadow-sm' : 'text-indigo-100 hover:bg-white/10' }}">Month</button>
                        </form>
                        
                        @php
                            $companyPercent = $companyTarget > 0 ? round(($companyAchievement / $companyTarget) * 100, 1) : 0;
                        @endphp
                        <div>
                            <p class="text-sm font-bold text-indigo-100 uppercase">Overall Achievement</p>
                            <p class="text-5xl font-black">{{ $companyPercent }}%</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <p class="text-xs font-bold text-indigo-100 uppercase">Total Target</p>
                        <p class="text-2xl font-black">INR {{ number_format($companyTarget) }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4">
                        <p class="text-xs font-bold text-indigo-100 uppercase">Total Achievement</p>
                        <p class="text-2xl font-black">INR {{ number_format($companyAchievement) }}</p>
                    </div>
                </div>
            </div>            @if($companyTarget == 0 && count($employeeMetrics) == 0)
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 mb-2">No Targets Set for {{ now()->format('F Y') }}</h2>
                    <p class="text-slate-500 mb-8 max-w-md mx-auto">It looks like the company targets and employee goals have not been defined for this period yet. Set targets to unlock visual analytics.</p>
                    <a href="{{ route('targets.index') }}" class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Set Targets Now
                    </a>
                </div>
            @else
                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Pie Chart: Target vs Achievement -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h4 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                            Target vs Achievement
                        </h4>
                        <div class="relative" style="height: 280px;">
                            <canvas id="targetPieChart"></canvas>
                        </div>
                    </div>

                    <!-- Bar Chart: Employee Comparison -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h4 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Employee Performance Comparison
                        </h4>
                        <div class="relative" style="height: 280px;">
                            <canvas id="employeeBarChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Employee Performance Table -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h4 class="text-lg font-black text-slate-900">Detailed Performance Breakdown</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest bg-slate-50/50">
                                    <th class="px-6 py-4">Name</th>
                                    <th class="px-6 py-4">Role</th>
                                    <th class="px-6 py-4 text-right">Active Time</th>
                                    <th class="px-6 py-4 text-right">Total Calls</th>
                                    <th class="px-6 py-4 text-right">Free Trials</th>
                                    <th class="px-6 py-4 text-right">Total Sale</th>
                                    <th class="px-6 py-4 text-right">KPI Score</th>
                                    <th class="px-6 py-4 text-right">Achievement %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($employeeMetrics->sortByDesc('total_sale') as $metric)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-slate-900">{{ $metric['name'] }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">
                                                {{ $metric['role'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-black border border-slate-200">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $metric['active_time'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-700">{{ $metric['total_calls'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-cyan-600">{{ $metric['free_trials'] }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-slate-900">
                                            INR {{ number_format($metric['total_sale']) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span
                                                class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100">
                                                {{ $metric['kpi_score'] }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-indigo-600 h-full rounded-full"
                                                        style="width: {{ min(100, $metric['percentage']) }}%"></div>
                                                </div>
                                                <span
                                                    class="font-black text-xs {{ $metric['percentage'] >= 100 ? 'text-green-600' : 'text-slate-700' }}">
                                                    {{ $metric['percentage'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div></div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Pie Chart: Target vs Achievement
        const pieCtx = document.getElementById('targetPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Achievement', 'Remaining Target'],
                datasets: [{
                    data: [{{ $companyAchievement }}, {{ max(0, $companyTarget - $companyAchievement) }}],
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',  // Indigo
                        'rgba(248, 113, 113, 0.8)'  // Red
                    ],
                    borderColor: [
                        'rgba(99, 102, 241, 1)',
                        'rgba(248, 113, 113, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'INR ' + context.parsed.toLocaleString();
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart: Employee Comparison
        const barCtx = document.getElementById('employeeBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: @json($employeeMetrics->pluck('name')->values()),
                datasets: [{
                    label: 'Total Sales (INR)',
                    data: @json($employeeMetrics->pluck('total_sale')->values()),
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.8,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'INR ' + value.toLocaleString();
                            },
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Sales: INR ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
