@props(['targetProgress', 'nextLead' => null, 'upcomingFollowups' => collect()])

<div class="glass-card p-6 rounded-[2rem] flex flex-col h-full bg-gradient-to-br from-white/90 to-indigo-50/30">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">Daily Roadmap</h3>
            <p class="text-[10px] text-indigo-600 font-extrabold uppercase tracking-widest">Priority Execution Engine</p>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-indigo-600/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    <div class="flex-1 space-y-8">
        <!-- Next Priority Action -->
        <div class="relative overflow-hidden group">
            <div class="absolute inset-0 bg-indigo-600/5 group-hover:bg-indigo-600/10 transition-colors rounded-2xl"></div>
            <div class="relative p-5">
                <span class="inline-block px-2 py-0.5 bg-indigo-600 text-white text-[8px] font-black uppercase rounded-md mb-3 tracking-tighter">Next Priority</span>
                @if($nextLead)
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-slate-900 truncate max-w-[150px]">{{ $nextLead->name }}</h4>
                            <p class="text-xs font-semibold text-slate-500 italic">{{ $nextLead->follow_up_date?->diffForHumans() ?? 'Scheduled now' }}</p>
                        </div>
                        <a href="{{ route('leads.show', $nextLead->id) }}" class="w-8 h-8 rounded-xl bg-white shadow-sm flex items-center justify-center text-indigo-600 hover:scale-110 active:scale-95 transition-all">
                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @else
                    <p class="text-sm font-bold text-slate-400">Queue is clear! Take a breather. ☕</p>
                @endif
            </div>
        </div>

        <!-- Progress Widget -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white/40 p-5 rounded-2xl border border-white/60">
                <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Month Goal</p>
                <div class="flex items-end gap-1">
                    <span class="text-xl font-bold text-slate-900">{{ $targetProgress['percentage'] }}%</span>
                </div>
                <div class="w-full h-1 bg-slate-100 rounded-full mt-3 overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000" style="width: {{ min(100, $targetProgress['percentage']) }}%"></div>
                </div>
            </div>
            <div class="bg-white/40 p-5 rounded-2xl border border-white/60">
                <p class="text-[9px] font-black text-slate-400 uppercase mb-2">Revenue</p>
                <p class="text-xl font-bold text-slate-900 truncate">₹{{ number_format($targetProgress['achieved'] / 1000, 0) }}k</p>
                <p class="text-[10px] text-emerald-600 font-bold mt-2">+{{ $targetProgress['percentage'] > 0 ? 'Verified' : '0.0%' }}</p>
            </div>
        </div>

        <!-- Upcoming Schedule -->
        <div>
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Upcoming Schedule</h4>
            <div class="space-y-3">
                @forelse($upcomingFollowups as $followup)
                    <div class="flex items-center gap-3 group">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 group-hover:scale-150 transition-all"></div>
                        <span class="text-xs font-bold text-slate-700 truncate flex-1">{{ $followup->name }}</span>
                        <span class="text-[10px] font-bold text-slate-400">{{ $followup->follow_up_date?->format('H:i') }}</span>
                    </div>
                @empty
                    <p class="text-[10px] font-bold text-slate-300 uppercase italic">No further calls scheduled</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Action Footer -->
    <div class="mt-8 pt-6 border-t border-slate-200/50">
        <button class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-900/10 hover:bg-slate-800 transition-all active:scale-95">
            Optimize Workflow
        </button>
    </div>
</div>
