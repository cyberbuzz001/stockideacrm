<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in-up">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Team Management Hub</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium">Monitoring {{ $stats['total_team_members'] ?? 0 }} agents across your assigned teams.</p>
        </div>
        <div class="flex items-center gap-3">
             <div class="glass-card px-4 py-2 rounded-2xl flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                <span class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">Manager Mode</span>
            </div>
        </div>
    </div>

    <!-- Manager KPI Strip -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card 
            title="Team Size" 
            :value="number_format($stats['total_team_members'] ?? 0)" 
            icon="users" 
            color="indigo" 
        />
        <x-stat-card 
            title="Team Revenue (Today)" 
            :value="'₹' . number_format($stats['daily_revenue'] ?? 0, 0)" 
            icon="currency-rupee" 
            color="emerald" 
        />
        <x-stat-card 
            title="Active Trials" 
            :value="number_format($stats['trials_running'] ?? 0)" 
            icon="clock" 
            color="blue" 
        />
        <x-stat-card 
            title="Pending Approvals" 
            :value="number_format($stats['pending_payments'] ?? 0)" 
            icon="check-circle" 
            color="amber" 
            :link="route('payments.index')"
        />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Agent Performance Column -->
        <div class="lg:col-span-2 space-y-8">
            <div class="glass-card p-6 rounded-3xl animate-fade-in-up">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Agent Performance</h3>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sorted by Revenue</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Agent</th>
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Leads</th>
                                <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Revenue</th>
                                <th class="pb-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                            @foreach($agent_performance as $agent)
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
                                        <span class="text-xs font-black text-slate-600 dark:text-slate-400">{{ $agent->active_leads_count ?? 0 }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <span class="text-sm font-black text-slate-900 dark:text-white">₹{{ number_format($agent->revenue ?? 0) }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('employees.show', $agent->id) }}" class="text-[10px] font-black text-indigo-600 uppercase hover:underline">Profile</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Escalations -->
            <div class="glass-card p-6 rounded-3xl border-l-4 border-rose-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-black text-rose-600 uppercase tracking-tight">Active Escalations</h3>
                    <span class="px-2 py-1 bg-rose-100 text-rose-600 rounded text-[10px] font-black italic">URGENT</span>
                </div>
                <div class="space-y-4">
                    @forelse($escalations ?? [] as $lead)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50/50 dark:bg-rose-900/10">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $lead->name }}</p>
                                <p class="text-[10px] text-slate-500 font-bold uppercase">{{ $lead->assignee->name ?? 'Unassigned' }} • {{ $lead->status }}</p>
                            </div>
                            <a href="{{ route('leads.show', $lead->id) }}" class="px-4 py-1.5 bg-white text-rose-600 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all">Review</a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic py-4 text-center font-bold">No active escalations. Excellent!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-8">
            <!-- Pending Payments -->
            <div class="glass-card p-6 rounded-3xl">
                <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight mb-4">Awaiting Approval</h3>
                <div class="space-y-4">
                    @forelse($pending_payments as $payment)
                        <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-black text-slate-900 dark:text-white truncate max-w-[120px]">
                                    {{ $payment->lead?->name ?? 'Lead' }}
                                </span>
                                <span class="text-[10px] font-black text-amber-600">₹{{ number_format($payment->amount) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] text-slate-400 font-bold uppercase">
                                    {{ $payment->user?->name ?? 'Agent' }}
                                </span>
                                <a href="{{ route('payments.index') }}" class="text-[9px] font-black text-indigo-600 uppercase hover:underline">Approve →</a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center opacity-30">
                            <p class="text-xs font-black uppercase tracking-widest">Everything Approved</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Global Leaderboard -->
            <x-dashboard.leaderboard :leaderboard="$leaderboard" />
        </div>
    </div>
</div>
