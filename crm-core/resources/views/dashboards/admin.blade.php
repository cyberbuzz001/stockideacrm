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

    <!-- Middle Section: Revenue Trends & Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 glass-card p-8 rounded-3xl min-h-[450px]">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Revenue Trajectory</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">MTD Performance vs Target</p>
                </div>
                <select class="bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                    <option>Last 30 Days</option>
                    <option>This Month</option>
                </select>
            </div>
            
            <div class="h-80 w-full relative">
                <!-- Placeholder for Chart.js -->
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Leaderboard -->
        <x-dashboard.leaderboard :leaderboard="$leaderboard" />
    </div>

    <!-- Bottom Section: Detailed Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- System Health -->
        <div class="glass-card p-8 rounded-3xl">
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-6">System Health</h3>
            <div class="space-y-6">
                @foreach([
                    ['label' => 'Lead Ingestion', 'status' => 'Healthy', 'val' => '100%', 'color' => 'emerald'],
                    ['label' => 'Server Load', 'status' => 'Optimal', 'val' => '12%', 'color' => 'blue'],
                    ['label' => 'Broadcasting', 'status' => 'Active', 'val' => '2.4ms', 'color' => 'indigo'],
                ] as $health)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-{{ $health['color'] }}-500"></div>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest">{{ $health['label'] }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $health['status'] }}</p>
                            <p class="text-[10px] text-slate-400 font-bold">{{ $health['val'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pending Approvals Feed -->
        <div class="lg:col-span-2 glass-card p-8 rounded-3xl overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Pending Approvals</h3>
                <a href="#" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">View Queue →</a>
            </div>
            <div class="space-y-4">
                @forelse($pending_payments ?? [] as $payment)
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 group hover:bg-slate-100 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white dark:bg-slate-700 flex items-center justify-center font-black text-slate-400">
                                {{ strtoupper(substr($payment->lead?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white">{{ $payment->lead?->name ?? 'Unknown Lead' }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic font-sans">{{ $payment->package?->name ?? 'No Package' }} • ₹{{ number_format($payment->amount) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                             <button class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:scale-105 transition-all">Approve</button>
                             <button class="p-2 bg-slate-200 dark:bg-slate-700 text-slate-600 rounded-xl hover:bg-rose-500 hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                             </button>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center opacity-30">
                        <p class="text-xs font-black uppercase tracking-widest font-sans">Clear Skies! No Pending Items</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_keys($revenue_trend ?? [])) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode(array_values($revenue_trend ?? [])) !!},
                    borderColor: '#4F46E5',
                    borderWidth: 4,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4F46E5',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { font: { weight: 'bold', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 10 } }
                    }
                }
            }
        });
    });
</script>
