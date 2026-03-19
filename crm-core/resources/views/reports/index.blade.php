<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Performance Reports') }}
        </h2>
    </x-slot>

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        {{-- ══════════════════════════════════════════
             FILTERS
        ══════════════════════════════════════════ --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                </div>

                @if(auth()->user()->role === 'Admin' || auth()->user()->role === 'Manager')
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Filter Agent</label>
                        <select name="user_id" class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 w-48">
                            <option value="">All Agents</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button type="submit"
                    class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-colors">
                    Filter Report
                </button>

                <a href="{{ route('payments.sales-orders') }}"
                   class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Ledger
                </a>
            </form>
        </div>

        {{-- ══════════════════════════════════════════
             SUMMARY KPI BAR
        ══════════════════════════════════════════ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Revenue</p>
                <p class="text-2xl font-black text-emerald-600">INR {{ number_format($totalRevenue) }}</p>
                <p class="text-xs text-slate-400 mt-1">Verified payments</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Conversions</p>
                <p class="text-2xl font-black text-indigo-600">{{ $totalConversions }}</p>
                <p class="text-xs text-slate-400 mt-1">Unique clients paid</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Calls Made</p>
                <p class="text-2xl font-black text-blue-600">{{ $totalCalls }}</p>
                <p class="text-xs text-slate-400 mt-1">Team total</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Top Agent</p>
                @if($topAgent && $topAgent->revenue > 0)
                    <p class="text-xl font-black text-slate-900 truncate">{{ $topAgent->name }}</p>
                    <p class="text-xs text-emerald-600 font-bold mt-1">INR {{ number_format($topAgent->revenue) }}</p>
                @else
                    <p class="text-lg font-bold text-slate-300 mt-2">—</p>
                    <p class="text-xs text-slate-400 mt-1">No data yet</p>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             AGENT PERFORMANCE TABLE
        ══════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg text-slate-900">Agent Performance Summary</h3>
                    <p class="text-sm text-slate-500">
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/60 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Agent Name</th>
                            <th class="px-6 py-4 text-center">Role</th>
                            <th class="px-6 py-4 text-center">Calls Made</th>
                            <th class="px-6 py-4 text-center">Conversions</th>
                            <th class="px-6 py-4 text-right">Revenue (INR)</th>
                            <th class="px-6 py-4 text-center">Conversion %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($reportData as $row)
                            @if($selectedUserId && $row->id != $selectedUserId) @continue @endif
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ substr($row->name, 0, 2) }}
                                        </div>
                                        <span class="font-bold text-slate-900">{{ $row->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[10px] font-bold">{{ $row->role }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-bold">{{ $row->calls }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg font-bold">{{ $row->conversions }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-slate-900">
                                    INR {{ number_format($row->revenue, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php $rate = $row->calls > 0 ? ($row->conversions / $row->calls) * 100 : 0; @endphp
                                    <span class="font-bold {{ $rate >= 20 ? 'text-emerald-600' : ($rate >= 5 ? 'text-amber-600' : 'text-slate-400') }}">
                                        {{ number_format($rate, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">No agents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-100">
                        <tr>
                            <td class="px-6 py-4 text-slate-700">TOTAL</td>
                            <td></td>
                            <td class="px-6 py-4 text-center text-blue-700">{{ $totalCalls }}</td>
                            <td class="px-6 py-4 text-center text-emerald-700">{{ $totalConversions }}</td>
                            <td class="px-6 py-4 text-right text-slate-900">INR {{ number_format($totalRevenue, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             CHARTS GRID: Revenue Bar + Calls Bar
        ══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-900 mb-4">Revenue by Agent</h3>
                @if($totalRevenue > 0)
                    <canvas id="revenueChart" height="180"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-40 text-slate-300">
                        <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="text-sm">No revenue data for this period</p>
                    </div>
                @endif
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-slate-900 mb-4">Call Volume by Agent</h3>
                @if($totalCalls > 0)
                    <canvas id="callsChart" height="180"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center h-40 text-slate-300">
                        <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <p class="text-sm">No calls logged for this period</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             MONTHLY REVENUE TREND (last 6 months)
        ══════════════════════════════════════════ --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-900 mb-1">6-Month Revenue Trend</h3>
            <p class="text-xs text-slate-400 mb-5">Company-wide verified revenue — last 6 months</p>
            @if($monthlyRevenue->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-slate-300">
                    <svg class="w-10 h-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <p class="text-sm">No payment history yet</p>
                </div>
            @else
                <canvas id="monthlyTrendChart" height="80"></canvas>
            @endif
        </div>

        {{-- ══════════════════════════════════════════
             CONVERSION FUNNEL
        ══════════════════════════════════════════ --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="font-bold text-slate-900 mb-1">Lead Conversion Funnel</h3>
            <p class="text-xs text-slate-400 mb-5">Leads created in the selected date range</p>
            @php
                $funnelMax = max(array_values($conversionFunnel)) ?: 1;
                $funnelColors = ['bg-indigo-500', 'bg-violet-500', 'bg-amber-500', 'bg-emerald-500'];
                $funnelIdx = 0;
            @endphp
            <div class="space-y-4">
                @foreach($conversionFunnel as $stage => $count)
                    @php $pct = round(($count / $funnelMax) * 100); @endphp
                    <div>
                        <div class="flex justify-between items-center text-sm mb-1.5">
                            <span class="font-semibold text-slate-700">{{ $stage }}</span>
                            <span class="font-black text-slate-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div class="{{ $funnelColors[$funnelIdx] }} h-full rounded-full transition-all duration-700"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @php $funnelIdx++; @endphp
                @endforeach
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             ADVISORY CALL PERFORMANCE
        ══════════════════════════════════════════ --}}
        @if($advisoryStats->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-100">
                <h3 class="font-bold text-lg text-slate-900">Advisory Performance Analysis</h3>
                <p class="text-sm text-slate-500">Call outcomes tracked globally by segment</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/60 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Segment</th>
                            <th class="px-6 py-4 text-center">Total Calls</th>
                            <th class="px-6 py-4 text-center">Target Hit</th>
                            <th class="px-6 py-4 text-center">SL Hit</th>
                            <th class="px-6 py-4 text-center">Success Ratio</th>
                            <th class="px-6 py-4">Performance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($advisoryStats as $stat)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg font-bold text-xs">{{ $stat->segment }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $stat->total }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-emerald-600 font-bold">🎯 {{ $stat->target_achieved }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-rose-600 font-bold">📉 {{ $stat->sl_hit }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black {{ $stat->hit_ratio >= 70 ? 'text-emerald-600' : ($stat->hit_ratio >= 40 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $stat->hit_ratio }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-slate-100 rounded-full h-2 max-w-[150px]">
                                        <div class="h-2 rounded-full {{ $stat->hit_ratio >= 70 ? 'bg-emerald-500' : ($stat->hit_ratio >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                            style="width: {{ $stat->hit_ratio }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const reportData = @json($reportData);
        const labels     = reportData.map(d => d.name);
        const revenues   = reportData.map(d => d.revenue);
        const calls      = reportData.map(d => d.calls);

        @if($totalRevenue > 0)
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue (INR)',
                    data: revenues,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    hoverBackgroundColor: '#4338ca',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' INR ' + ctx.parsed.y.toLocaleString('en-IN') } }
                },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } }
            }
        });
        @endif

        @if($totalCalls > 0)
        new Chart(document.getElementById('callsChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Calls Made',
                    data: calls,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 8,
                    hoverBackgroundColor: '#0284c7',
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } }, x: { grid: { display: false } } }
            }
        });
        @endif

        @if(!$monthlyRevenue->isEmpty())
        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyRevenue->pluck('month_label')),
                datasets: [{
                    label: 'Revenue (INR)',
                    data: @json($monthlyRevenue->pluck('total')),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ' INR ' + ctx.parsed.y.toLocaleString('en-IN') } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { callback: v => 'INR ' + (v >= 100000 ? (v/100000).toFixed(1)+'L' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v) }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
        @endif
    </script>
</x-app-layout>
