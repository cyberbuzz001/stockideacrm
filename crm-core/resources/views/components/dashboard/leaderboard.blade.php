@props(['leaderboard'])

<div class="glass-card p-6 rounded-3xl flex flex-col h-full animate-fade-in-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Top Revenue Closers</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Live Performance Ranking</p>
        </div>
        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
            <span class="text-lg">🏆</span>
        </div>
    </div>

    <div class="space-y-4 flex-1 overflow-y-auto pr-2 dashboard-scroll">
        @foreach($leaderboard->map(fn($agent, $i) => [
            'rank'     => $i + 1,
            'name'     => $agent->name,
            'initials' => strtoupper(substr($agent->name, 0, 2)),
            'revenue'  => (float) ($agent->payments_sum_amount ?? 0),
            'pct'      => round((($agent->payments_sum_amount ?? 0) / ($leaderboard->max('payments_sum_amount') ?: 1)) * 100),
        ]) as $agent)
            <div class="flex items-center gap-4 group">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs 
                    {{ $agent['rank'] == 1 ? 'rank-badge-1' : ($agent['rank'] == 2 ? 'rank-badge-2' : ($agent['rank'] == 3 ? 'rank-badge-3' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400')) }}">
                    {{ $agent['rank'] }}
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-end mb-1.5">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $agent['name'] }}</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white">₹{{ number_format($agent['revenue'], 0) }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-1000 bg-gradient-to-r {{ $agent['rank'] == 1 ? 'from-amber-400 to-amber-600' : 'from-indigo-500 to-purple-600' }}" style="width: {{ $agent['pct'] }}%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">Live Refreshed</span>
        <a href="{{ route('employees.index') }}" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">View All Teams →</a>
    </div>
</div>
