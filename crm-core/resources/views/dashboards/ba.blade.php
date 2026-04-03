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

    <!-- KPI Strip -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="Total Leads" 
            :value="number_format($stats['assigned_total'] ?? 0)" 
            icon="users" 
            color="indigo" 
            :trend="12" 
            :trendUp="true"
        />
        <x-stat-card 
            title="Today's Calls" 
            :value="number_format($stats['calls_today'] ?? 0)" 
            icon="phone" 
            color="blue" 
            :trend="round((($stats['calls_today'] ?? 0) / ($stats['calls_yesterday'] ?: 1) - 1) * 100)"
            :trendUp="($stats['calls_today'] ?? 0) >= ($stats['calls_yesterday'] ?? 0)"
        />
        <x-stat-card 
            title="Conversions" 
            :value="number_format($stats['paid_approved'] ?? 0)" 
            icon="check-circle" 
            color="emerald" 
        />
        <x-stat-card 
            title="Revenue" 
            :value="'₹' . number_format($stats['revenue'] ?? 0, 0)" 
            icon="currency-rupee" 
            color="amber" 
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Priority Queue Column -->
        <div class="lg:col-span-2 space-y-8">
            <x-dashboard.priority-queue :leads="$priority_leads ?? collect()" title="My Priority Actions" />
            
            <!-- Target Progress & Market Heat -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Target Progress -->
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Revenue Target</h3>
                            <span class="text-xs font-black text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full italic">
                                {{ $target_progress['percentage'] }}% Achieved
                            </span>
                        </div>
                        
                        <div class="flex flex-col items-center py-6">
                            <div class="relative w-48 h-48">
                                <!-- Circular Progress SVG -->
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <circle class="text-slate-100 dark:text-slate-800" stroke-width="8" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50" />
                                    <circle class="text-indigo-600 transition-all duration-1000 ease-out" 
                                        stroke-width="8" 
                                        stroke-dasharray="{{ 2 * pi() * 40 }}" 
                                        stroke-dashoffset="{{ (1 - ($target_progress['percentage'] / 100)) * (2 * pi() * 40) }}" 
                                        stroke-linecap="round" 
                                        stroke="currentColor" 
                                        fill="transparent" r="40" cx="50" cy="50" 
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-black text-slate-900 dark:text-white">₹{{ number_format($target_progress['achieved'] / 1000, 1) }}k</span>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">of ₹{{ number_format($target_progress['amount'] / 100000, 1) }}L</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Market Call -->
                <div class="glass-card p-8 rounded-3xl bg-slate-900 text-white relative overflow-hidden group">
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Live Advisory Call</span>
                            </div>
                            <h3 class="text-xl font-bold mb-2">"{{ $live_market_call->title ?? 'Nifty Breakout Alert' }}"</h3>
                            <p class="text-sm text-slate-400 line-clamp-3">
                                {{ $live_market_call->call_summary ?? 'Market showing strong resistance at 19,800. Suggested strategy: Bull Call Spread on weekly expiry.' }}
                            </p>
                        </div>
                        <button class="mt-6 w-full py-3 bg-white text-slate-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-500 hover:text-white transition-all">
                            Join Meeting Now
                        </button>
                    </div>
                    <!-- Background aesthetic -->
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-8">
            <!-- Leaderboard Widget -->
            <x-dashboard.leaderboard :leaderboard="$leaderboard" />

            <!-- Call Volume Chart -->
            <div class="glass-card p-6 rounded-3xl min-h-[300px]">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-4">Activity Trend</h3>
                <div class="h-48 relative">
                    <canvas id="callVolumeChart"></canvas>
                </div>
            </div>

            <!-- Daily Goals -->
            <div class="glass-card p-6 rounded-3xl">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-4">Today's Goals</h3>
                <div class="space-y-4">
                    @php
                        $goals = [
                            ['label' => 'Calls Completed', 'done' => 12, 'target' => 50, 'color' => 'blue'],
                            ['label' => 'New Demos', 'done' => 2, 'target' => 5, 'color' => 'emerald'],
                            ['label' => 'Follow-ups', 'done' => 18, 'target' => 20, 'color' => 'amber'],
                        ];
                    @endphp
                    @foreach($goals as $goal)
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-[11px] font-black uppercase tracking-widest">
                                <span class="text-slate-500">{{ $goal['label'] }}</span>
                                <span class="text-slate-900 dark:text-white">{{ $goal['done'] }}/{{ $goal['target'] }}</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-{{ $goal['color'] }}-500 rounded-full animate-pulse" style="width: {{ ($goal['done']/$goal['target'])*100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Call Volume Chart
        const callCtx = document.getElementById('callVolumeChart')?.getContext('2d');
        if (callCtx) {
            new Chart(callCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($call_volume ?? [], 'date')) !!},
                    datasets: [{
                        label: 'Calls',
                        data: {!! json_encode(array_column($call_volume ?? [], 'count')) !!},
                        backgroundColor: '#4F46E5',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10, weight: 'bold' } } }
                    }
                }
            });
        }
    });
</script>
