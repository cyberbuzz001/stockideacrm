<x-app-layout>
    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Revenue Operations</h1>
                <p class="text-sm text-slate-500 mt-0.5">Financial insights and payment approvals</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="switchTab('pending')" class="relative bg-white border border-slate-200 text-slate-700 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 text-sm font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pending Approvals
                    @if($pendingCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] font-black text-white items-center justify-center">{{ $pendingCount }}</span>
                        </span>
                    @endif
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center gap-3 shadow-sm animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-bold flex items-center gap-3 shadow-sm animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                {{ session('warning') }}
            </div>
        @endif

        {{-- KPI SUMMARY CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            {{-- This Month --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">This Month</p>
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 tracking-tight">INR {{ number_format($thisMonth) }}</p>
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $momGrowth >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $momGrowth >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                            {{ number_format(abs($momGrowth), 1) }}%
                        </span>
                        <span class="text-xs font-semibold text-slate-400">vs Last Month</span>
                    </div>
                </div>
            </div>

            {{-- This Week --}}
            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="absolute right-0 top-0 w-24 h-24 bg-violet-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">This Week</p>
                        <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-black text-slate-900 tracking-tight">INR {{ number_format($thisWeek) }}</p>
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $wowGrowth >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $wowGrowth >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                            {{ number_format(abs($wowGrowth), 1) }}%
                        </span>
                        <span class="text-xs font-semibold text-slate-400">vs Last Week</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN LAYOUT: Chart (Left) + Tabs (Right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- MONTHLY CHART --}}
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col h-full">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="font-black text-slate-900 text-lg tracking-tight">Revenue Trend</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Last 6 Months Trajectory</p>
                    </div>
                    <a href="{{ route('payments.sales-orders') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                        Full Ledger
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                @if($monthlyTrend->isEmpty())
                    <div class="flex flex-col items-center justify-center flex-1 py-16 text-slate-400">
                        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <p class="text-sm font-bold">No data available to chart.</p>
                    </div>
                @else
                    <div class="relative flex-1 w-full min-h-[300px]">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                @endif
            </div>

            {{-- TABS AREA --}}
            <div class="lg:col-span-1 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col h-[500px]">
                
                {{-- Tab Header --}}
                <div class="flex bg-slate-50/50 p-2 gap-1 border-b border-slate-100" id="paymentTabs">
                    <button onclick="switchTab('history')" id="tab-history" class="flex-1 py-2.5 px-3 text-xs font-black uppercase tracking-widest rounded-xl transition-all {{ session('success') || session('warning') ? 'text-slate-400 hover:bg-white hover:shadow-sm' : 'bg-white text-indigo-600 shadow-sm border border-slate-200/60' }}">
                        Ledger
                    </button>
                    <button onclick="switchTab('pending')" id="tab-pending" class="flex-1 py-2.5 px-3 text-xs font-black uppercase tracking-widest rounded-xl transition-all relative {{ session('success') || session('warning') ? 'bg-white text-indigo-600 shadow-sm border border-slate-200/60' : 'text-slate-400 hover:bg-white hover:shadow-sm' }}">
                        Approvals
                        @if($pendingCount > 0)
                            <span class="absolute top-2 right-2 flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                            </span>
                        @endif
                    </button>
                </div>

                {{-- PANEL 1: HISTORY --}}
                <div id="panel-history" class="flex-1 overflow-y-auto {{ session('success') || session('warning') ? 'hidden' : '' }}">
                    @if($verifiedPayments->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <p class="text-sm font-bold">No verified payments yet.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($verifiedPayments as $vp)
                                <div class="p-4 hover:bg-slate-50/80 transition-colors flex items-center justify-between group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm border border-emerald-100 flex-shrink-0">
                                            {{ substr($vp->lead->name ?? 'N/A', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 text-sm leading-tight">{{ $vp->lead->name ?? 'Deleted Lead' }}</p>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                <span>{{ $vp->payment_date ? $vp->payment_date->format('d M y') : $vp->created_at->format('d M y') }}</span>
                                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                <span>{{ $vp->payment_mode }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-emerald-600 text-base">INR {{ number_format($vp->amount) }}</p>
                                        <a href="{{ route('payments.invoice', $vp) }}" target="_blank" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-600 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity">
                                            Invoice ↗
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PANEL 2: PENDING --}}
                <div id="panel-pending" class="flex-1 overflow-y-auto bg-slate-50/30 {{ session('success') || session('warning') ? '' : 'hidden' }}">
                    @if($payments->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                            <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-600">All caught up!</p>
                            <p class="text-xs text-slate-400 mt-0.5">No pending approvals.</p>
                        </div>
                    @else
                        <div class="p-4 space-y-4">
                            @foreach($payments as $payment)
                                <div class="bg-white border border-amber-100 shadow-sm rounded-2xl overflow-hidden group hover:border-amber-300 transition-colors">
                                    <div class="p-4 flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 border border-amber-100">
                                            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-black text-slate-900 text-lg">INR {{ number_format($payment->amount) }}</p>
                                            <p class="text-xs font-semibold text-slate-500 mt-0.5">
                                                Lead: <a href="{{ route('leads.show', $payment->lead_id) }}" class="text-indigo-600 hover:underline inline-block">{{ $payment->lead->name ?? 'Deleted Lead' }}</a>
                                            </p>
                                            <div class="mt-2 text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                                <span>{{ $payment->payment_mode }}</span>
                                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                <span>Agent: {{ explode(' ', $payment->lead->assignee->name ?? 'None')[0] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-slate-50/80 px-4 py-3 border-t border-slate-100 flex flex-col gap-2">
                                        <form action="{{ route('payments.verify', $payment) }}" method="POST" class="flex flex-col gap-2 w-full">
                                            @csrf
                                            <input type="text" name="remarks" placeholder="Add remarks..." class="text-xs border-slate-200 rounded-lg px-3 py-2 w-full focus:ring-indigo-500 shadow-sm font-medium">
                                            <div class="flex gap-2 w-full">
                                                <button name="action" value="approve" class="flex-1 bg-emerald-600 text-white py-2 rounded-lg text-[10px] uppercase font-black tracking-widest hover:bg-emerald-700 transition-colors shadow-sm">
                                                    Verify
                                                </button>
                                                <button name="action" value="reject" class="flex-1 bg-white border border-rose-200 text-rose-600 py-2 rounded-lg text-[10px] uppercase font-black tracking-widest hover:bg-rose-50 transition-colors">
                                                    Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>

    {{-- CHART.JS & SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function switchTab(tab) {
            ['history', 'pending'].forEach(t => {
                document.getElementById('panel-' + t).classList.add('hidden');
                const btn = document.getElementById('tab-' + t);
                btn.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm', 'border', 'border-slate-200/60');
                btn.classList.add('text-slate-400');
            });
            document.getElementById('panel-' + tab).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + tab);
            activeBtn.classList.remove('text-slate-400');
            activeBtn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm', 'border', 'border-slate-200/60');
        }

        @if(!$monthlyTrend->isEmpty())
        const ctx = document.getElementById('monthlyRevenueChart').getContext('2d');
        
        // Dynamic gradient for bars
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 1)'); // Indigo 500
        gradient.addColorStop(1, 'rgba(168, 85, 247, 0.6)'); // Purple 500

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($monthlyTrend->pluck('month_label')),
                datasets: [{
                    label: 'Verified Revenue (INR)',
                    data: @json($monthlyTrend->pluck('total')),
                    backgroundColor: gradient,
                    borderRadius: 12,
                    borderSkipped: false,
                    barThickness: 'flex',
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a', /* slate-900 */
                        titleFont: { family: 'Inter', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: ctx => 'INR ' + ctx.parsed.y.toLocaleString('en-IN')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(241, 245, 249, 1)', drawBorder: false }, /* slate-100 */
                        border: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11, weight: 'bold' },
                            color: '#94a3b8', /* slate-400 */
                            padding: 12,
                            callback: v => 'INR ' + (v >= 100000 ? (v/100000).toFixed(1)+'L' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v)
                        }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11, weight: 'bold' },
                            color: '#64748b', /* slate-500 */
                            padding: 8
                        }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            }
        });
        @endif
    </script>
</x-app-layout>
