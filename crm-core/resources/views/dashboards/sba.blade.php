<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">SBA Control Center</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Leading Team and Managing Personal Portfolio.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="glass-card px-4 py-2 rounded-2xl flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">Live Team Mode</span>
            </div>
        </div>
    </div>

    <!-- SBA KPI Strip (Team Focus) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="Team Leads" 
            :value="number_format($stats['team_leads'] ?? 0)" 
            icon="users" 
            color="indigo" 
        />
        <x-stat-card 
            title="Team Revenue" 
            :value="'₹' . number_format($stats['team_revenue'] ?? 0, 0)" 
            icon="currency-rupee" 
            color="emerald" 
        />
        <x-stat-card 
            title="Team Calls Today" 
            :value="number_format($stats['team_calls_today'] ?? 0)" 
            icon="phone" 
            color="blue" 
            :trend="round((($stats['team_calls_today'] ?? 0) / ($stats['team_calls_yesterday'] ?: 1) - 1) * 100)"
            :trendUp="($stats['team_calls_today'] ?? 0) >= ($stats['team_calls_yesterday'] ?? 0)"
        />
        <x-stat-card 
            title="Team Trials" 
            :value="number_format($stats['team_trials'] ?? 0)" 
            icon="check-circle" 
            color="amber" 
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Team Performance & Charts Column -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Team Overview Table -->
            <div class="glass-card p-6 rounded-3xl">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6">Team Performance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agent</th>
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Calls</th>
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @foreach($team_performance as $agent)
                                <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-black text-xs text-slate-500">
                                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                                            </div>
                                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $agent->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="text-xs font-black text-slate-600 dark:text-slate-400">{{ $agent->calls_today ?? 0 }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <span class="text-sm font-black text-slate-900 dark:text-white">₹{{ number_format($agent->revenue ?? 0) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SBA Personal Priority Queue -->
            <x-dashboard.priority-queue :leads="$my_leads ?? collect()" title="My Personal High-Priority Leads" />
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-8">
            <!-- Global Leaderboard -->
            <x-dashboard.leaderboard :leaderboard="$leaderboard" />
            
            <!-- Team Revenue Chart (Small) -->
            <div class="glass-card p-6 rounded-3xl min-h-[300px]">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-4">Team Trend</h3>
                <div class="h-48 relative">
                    <canvas id="teamRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('teamRevenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($team_revenue_trend ?? [])) !!},
                datasets: [{
                    label: 'Team Revenue',
                    data: {!! json_encode(array_values($team_revenue_trend ?? [])) !!},
                    borderColor: '#10B981',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    });
</script>
