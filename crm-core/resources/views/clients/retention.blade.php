<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Retention Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        {{-- ══════════════════════════════════════════
             SUMMARY KPIs
        ══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
                <p class="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2">Total Active Subscriptions</p>
                <p class="text-4xl font-black text-emerald-600">{{ $totalActive }}</p>
            </div>
            
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
                <p class="text-[10px] font-black tracking-widest uppercase text-slate-400 mb-2">At Risk (Expiring 30 Days)</p>
                <p class="text-4xl font-black text-amber-500">{{ $expiringIn30Days->count() }}</p>
            </div>

            <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-6 shadow-sm text-center text-white">
                <p class="text-[10px] font-black tracking-widest uppercase text-indigo-200 mb-2">Renewal/Retention Rate</p>
                <div class="flex items-baseline justify-center gap-1">
                    <p class="text-4xl font-black text-white">{{ $renewalRate }}</p>
                    <span class="text-xl font-bold text-indigo-300">%</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- ══════════════════════════════════════════
                 EXPIRING SOON (RENEWAL PIPELINE)
            ══════════════════════════════════════════ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-amber-50/30">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Upcoming Renewals (Next 30 Days)
                    </h3>
                </div>
                <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                    @forelse($expiringIn30Days as $client)
                        <div class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-center group">
                            <div>
                                <p class="font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $client->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $client->mobile }}</p>
                            </div>
                            <div class="text-right flex flex-col items-end gap-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border border-slate-200 shadow-sm
                                        {{ $client->churn_risk_level === 'High Risk' ? 'bg-rose-50 text-rose-700 border-rose-100' : ($client->churn_risk_level === 'Medium Risk' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100') }}">
                                        🛡️ AI Risk: {{ $client->churn_risk_score }}%
                                    </span>
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase rounded-lg">
                                        Expires {{ \Carbon\Carbon::parse($client->renewal_date)->diffForHumans() }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="text-[10px] font-bold text-indigo-600 hover:underline">View Profile →</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-medium text-sm">No clients expiring in the next 30 days.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 RECENTLY EXPIRED (CHURN)
            ══════════════════════════════════════════ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-rose-50/30">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Recently Expired
                    </h3>
                </div>
                <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                    @forelse($recentlyExpired as $client)
                        <div class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-center group opacity-80 hover:opacity-100">
                            <div>
                                <p class="font-bold text-slate-900 line-through decoration-slate-300">{{ $client->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $client->mobile }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-700 text-[10px] font-black uppercase rounded-lg">
                                    Expired {{ \Carbon\Carbon::parse($client->renewal_date)->diffForHumans() }}
                                </span>
                                <div class="mt-2 text-[10px] font-bold text-slate-400">
                                    Requires Reactivation
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p class="font-medium text-sm">No recent churns.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
