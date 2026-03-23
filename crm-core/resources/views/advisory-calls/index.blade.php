<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Market Calls') }}
            </h2>
            @if(auth()->user()->hasPermission('market_calls', 'publish'))
                <a href="{{ route('advisory-calls.create') }}"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                    + Publish Signal
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 mb-6 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-bold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Segment Tabs (Section 4) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
                <div class="flex overflow-x-auto border-b border-slate-100 bg-slate-50/50 p-2 gap-2">
                    @foreach($segments as $seg)
                        <a href="{{ route('advisory-calls.index', ['segment' => $seg]) }}"
                            class="px-6 py-3 text-xs font-bold whitespace-nowrap transition-all rounded-xl {{ $segment === $seg ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'text-slate-500 hover:bg-slate-100' }}">
                            {{ $seg }}
                        </a>
                    @endforeach
                </div>

                <!-- Snapshot Widget -->
                @if($performanceStats->total > 0)
                <div class="px-8 py-6 bg-white border-b border-slate-100 flex items-center justify-between gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">{{ $segment }} Focus</p>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Performance Snapshot</h3>
                    </div>
                    <div class="flex gap-4">
                        <div class="bg-emerald-50 text-emerald-700 px-5 py-4 rounded-2xl flex items-center gap-4 min-w-[160px] border border-emerald-100/50">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-xl shadow-inner">🎯</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-60">Targets Hit</p>
                                <p class="font-bold text-2xl tracking-tighter">{{ $performanceStats->hits }}</p>
                            </div>
                        </div>
                        <div class="bg-rose-50 text-rose-700 px-5 py-4 rounded-2xl flex items-center gap-4 min-w-[160px] border border-rose-100/50">
                            <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center text-xl shadow-inner">📉</div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-60">SL Hit</p>
                                <p class="font-bold text-2xl tracking-tighter">{{ $performanceStats->sl }}</p>
                            </div>
                        </div>
                        <div class="bg-slate-900 text-white px-5 py-4 rounded-2xl flex items-center gap-4 min-w-[180px] border border-white/10">
                            <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center border border-white/10">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Success Rate</p>
                                <p class="font-bold text-2xl tracking-tighter {{ $performanceStats->ratio >= 70 ? 'text-emerald-400' : ($performanceStats->ratio >= 40 ? 'text-amber-400' : 'text-rose-400') }}">{{ $performanceStats->ratio }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Call History Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 text-[10px] uppercase font-bold tracking-widest bg-slate-50/20 border-b border-slate-50">
                                <th class="px-8 py-5">Segment</th>
                                <th class="px-8 py-5">Date</th>
                                <th class="px-8 py-5">Time</th>
                                <th class="px-8 py-5">Call Details</th>
                                <th class="px-8 py-5">Outcome</th>
                                <th class="px-8 py-5">By</th>
                                <th class="px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($calls as $call)
                                <tr class="hover:bg-slate-50/80 transition-all group">
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-indigo-100">
                                            {{ $call->segment }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 font-bold text-slate-500 text-xs">
                                        {{ $call->call_date->format('d M Y') }}
                                    </td>
                                    <td class="px-8 py-5 font-bold text-slate-900">
                                        {{ \Carbon\Carbon::parse($call->call_time)->format('H:i') }}
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-slate-700 font-bold leading-relaxed max-w-md">{{ $call->call_text }}</p>
                                    </td>
                                    <td class="px-8 py-5">
                                        @if($call->outcome)
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest {{ $call->outcome === 'Target Achieved' ? 'bg-emerald-100 text-emerald-700' : ($call->outcome === 'SL Hit' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                                {{ $call->outcome }}
                                            </span>
                                        @else
                                            <form action="{{ route('advisory-calls.outcome', $call) }}" method="POST" class="flex gap-2">
                                                @csrf
                                                <button type="submit" name="outcome" value="Target Achieved" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 shadow-sm shadow-emerald-50">
                                                    Target
                                                </button>
                                                <button type="submit" name="outcome" value="SL Hit" class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all border border-rose-100 shadow-sm shadow-rose-50">
                                                    SL
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase">{{ substr($call->user->name, 0, 1) }}</div>
                                            <span class="text-xs font-bold text-slate-500">{{ $call->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('advisory-calls.show', $call) }}"
                                                class="px-4 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-100 transition-all">
                                                View
                                            </a>
                                            @if(auth()->user()->hasPermission('market_calls', 'edit_delete'))
                                                <form action="{{ route('advisory-calls.destroy', $call) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Permanently delete this signal?')"
                                                        class="px-4 py-1.5 bg-rose-50 text-rose-600 border border-rose-100 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm shadow-rose-50">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                        No market calls found for {{ $segment }}. Create your first call above!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($calls->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $calls->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>