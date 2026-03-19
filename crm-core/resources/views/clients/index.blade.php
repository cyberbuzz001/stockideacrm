<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Client Directory</h1>
                <p class="text-sm text-slate-400 mt-0.5">All active paid clients and their subscription status</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.index') }}?filter=expiring"
                   class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-sm font-bold hover:bg-amber-100 transition-colors">
                    <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                    Upcoming Renewals
                </a>
                <a href="{{ route('clients.index') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition-colors">
                    All Clients
                </a>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $totalClients    = $clients->total();
                $expiringCount   = \App\Models\Lead::where('status', 'Paid Client')->whereNotNull('renewal_date')->whereBetween('renewal_date', [now(), now()->addDays(7)])->count();
                $expiredCount    = \App\Models\Lead::where('status', 'Service Expired')->count();
                $totalRevenue    = \App\Models\Payment::where('status', 'Verified')->sum('amount');
            @endphp
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clients</p>
                <p class="text-3xl font-black text-indigo-600 mt-1">{{ $totalClients }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Renewing Soon</p>
                <p class="text-3xl font-black text-amber-600 mt-1">{{ $expiringCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Expired</p>
                <p class="text-3xl font-black text-rose-600 mt-1">{{ $expiredCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Revenue</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">INR {{ number_format($totalRevenue) }}</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name or mobile..."
                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="min-w-[160px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</label>
                    <select name="filter" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500">
                        <option value="">All Clients</option>
                        <option value="expiring" {{ request('filter') === 'expiring' ? 'selected' : '' }}>Renewing (7 days)</option>
                        <option value="expired"  {{ request('filter') === 'expired'  ? 'selected' : '' }}>Service Expired</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('clients.index') }}" class="ml-2 text-xs text-slate-400 hover:text-slate-600">Reset</a>
                </div>
            </form>
        </div>

        <!-- Client Table -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50/70 text-slate-500 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Client Details</th>
                            <th class="px-6 py-4 text-center">Subscription Status</th>
                            <th class="px-6 py-4">Plan & Value</th>
                            <th class="px-6 py-4">Plan Validity</th>
                            <th class="px-6 py-4">Assigned Advisor</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($clients as $client)
                            @php
                                $daysLeft    = $client->renewal_date ? now()->diffInDays($client->renewal_date, false) : null;
                                $isExpiring  = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7;
                                $isExpired   = $client->status === 'Service Expired' || ($daysLeft !== null && $daysLeft < 0);
                                $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Renewing Soon' : 'Active');
                                $statusColor = $isExpired
                                    ? 'bg-rose-100 text-rose-700 border-rose-200'
                                    : ($isExpiring
                                        ? 'bg-amber-100 text-amber-700 border-amber-200'
                                        : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                                $dotColor    = $isExpired ? 'bg-rose-500' : ($isExpiring ? 'bg-amber-500' : 'bg-emerald-500');
                                $rowBg       = $isExpired ? 'bg-rose-50/30' : ($isExpiring ? 'bg-amber-50/20' : '');
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors {{ $rowBg }}">
                                <!-- Client Details -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                            {{ substr($client->name ?: '?', 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('clients.show', $client->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                                {{ $client->name ?: 'Unknown Client' }}
                                            </a>
                                            <p class="text-xs text-slate-400 font-mono mt-0.5">📞 {{ $client->mobile }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                        {{ $statusLabel }}
                                        @if($isExpiring && $daysLeft !== null)
                                            <span class="ml-0.5 font-black">({{ $daysLeft }}d)</span>
                                        @endif
                                    </span>
                                </td>

                                <!-- Plan & Value -->
                                <td class="px-6 py-4">
                                    @if($client->payments->isNotEmpty())
                                        @php $lastPayment = $client->payments->first(); @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 mb-1">
                                            {{ $lastPayment->subscription_plan ?? 'Custom Plan' }}
                                        </span>
                                        <div class="text-sm font-black text-emerald-600">INR {{ number_format($lastPayment->amount) }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No verified plan</span>
                                    @endif
                                </td>

                                <!-- Validity -->
                                <td class="px-6 py-4">
                                    <div class="text-xs text-slate-500 mb-1">
                                        <span class="text-slate-400 uppercase tracking-wide text-[10px]">Start: </span>
                                        <span class="font-medium">{{ $client->service_start_date ? $client->service_start_date->format('d M Y') : '—' }}</span>
                                    </div>
                                    <div class="text-xs font-bold {{ $isExpired ? 'text-rose-600' : ($isExpiring ? 'text-amber-600' : 'text-slate-700') }}">
                                        <span class="text-slate-400 uppercase tracking-wide text-[10px]">End: </span>
                                        {{ $client->renewal_date ? $client->renewal_date->format('d M Y') : '—' }}
                                    </div>
                                </td>

                                <!-- Advisor -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-[10px] font-bold shadow-sm">
                                            {{ substr($client->assignee->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-sm font-semibold text-slate-700">{{ $client->assignee->name ?? 'None' }}</span>
                                    </div>
                                </td>

                                <!-- Action -->
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('clients.show', $client->id) }}"
                                       class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all shadow-sm">
                                        View Profile
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <p class="text-slate-500 font-bold text-sm">No paid clients found.</p>
                                    <p class="text-slate-400 text-xs mt-1">Clients appear here once their payments are verified.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($clients->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
