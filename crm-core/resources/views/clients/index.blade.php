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
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
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
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="$dispatch('open-payment-modal', { id: {{ $client->id }}, name: '{{ addslashes($client->name) }}', assignee_id: {{ $client->assignee->id ?? 'null' }}, assignee_name: '{{ addslashes($client->assignee->name ?? 'None') }}' })"
                                                class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-xl px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all shadow-sm">
                                            Log Payment
                                        </button>
                                        <a href="{{ route('clients.show', $client->id) }}"
                                           class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all shadow-sm">
                                            View Profile
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        @if(auth()->user()->role === 'Admin')
                                            <!-- Revert to Lead -->
                                            <form action="{{ route('clients.revert', $client->id) }}" method="POST" onsubmit="return confirm('UN-APPROVE CLIENT: This will move them back to the Leads section. Payment records will remain associated with the Lead but the account will no longer be marked as a Paid Client. Proceed?');" class="inline">
                                                @csrf
                                                <button type="submit" title="Revert to Lead" class="inline-flex items-center justify-center bg-white border border-amber-100 text-amber-500 hover:text-amber-600 hover:bg-amber-50 hover:border-amber-200 w-9 h-9 rounded-xl transition-colors shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                </button>
                                            </form>
                                            <!-- Delete Client -->
                                            <form action="{{ route('leads.destroy', $client->id) }}" method="POST" onsubmit="return confirm('CRITICAL: This will permanently delete this client and ALL their ledger history. Proceed?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete Client" class="inline-flex items-center justify-center bg-white border border-rose-100 text-rose-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 w-9 h-9 rounded-xl transition-colors shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-slate-100">
                @forelse($clients as $client)
                    @php
                        $daysLeft    = $client->renewal_date ? now()->diffInDays($client->renewal_date, false) : null;
                        $isExpiring  = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7;
                        $isExpired   = $client->status === 'Service Expired' || ($daysLeft !== null && $daysLeft < 0);
                        $statusLabel = $isExpired ? 'Expired' : ($isExpiring ? 'Renewing Soon' : 'Active');
                        $statusColor = $isExpired 
                            ? 'bg-rose-100 text-rose-700 border-rose-200' 
                            : ($isExpiring ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                        $dotColor    = $isExpired ? 'bg-rose-500' : ($isExpiring ? 'bg-amber-500' : 'bg-emerald-500');
                    @endphp
                    <div class="p-4 space-y-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                    {{ substr($client->name ?: '?', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('clients.show', $client->id) }}" class="font-bold text-slate-900 block truncate">{{ $client->name ?: 'Unknown' }}</a>
                                    <p class="text-xs text-slate-400 font-mono">📞 {{ $client->mobile }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusColor }} flex-shrink-0">
                                <span class="w-1 h-1 rounded-full {{ $dotColor }}"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div class="bg-indigo-50/50 p-2 rounded-xl border border-indigo-100/50">
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Plan Value</p>
                                @if($client->payments->isNotEmpty())
                                    <div class="font-black text-emerald-600">INR {{ number_format($client->payments->first()->amount) }}</div>
                                    <div class="text-[9px] text-indigo-600 truncate">{{ $client->payments->first()->subscription_plan }}</div>
                                @else
                                    <div class="text-slate-400 italic">No plan</div>
                                @endif
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Renewal</p>
                                <div class="font-bold {{ $isExpired ? 'text-rose-600' : ($isExpiring ? 'text-amber-600' : 'text-slate-700') }}">
                                    {{ $client->renewal_date ? $client->renewal_date->format('d M Y') : '—' }}
                                </div>
                                @if($isExpiring && $daysLeft !== null)
                                    <div class="text-[9px] font-bold text-amber-500">In {{ $daysLeft }} days</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-2">
                             <a href="{{ route('clients.show', $client->id) }}"
                               class="flex-1 flex justify-center items-center gap-1.5 bg-white border border-slate-200 rounded-xl py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 transition-all shadow-sm text-center">
                                Profile
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <button @click="$dispatch('open-payment-modal', { id: {{ $client->id }}, name: '{{ addslashes($client->name) }}', assignee_id: {{ $client->assignee->id ?? 'null' }}, assignee_name: '{{ addslashes($client->assignee->name ?? 'None') }}' })"
                                    class="flex-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-xl py-2.5 text-[10px] font-black uppercase tracking-widest hover:bg-emerald-100 transition-all shadow-sm">
                                Log Payment
                            </button>
                            @if(auth()->user()->role === 'Admin')
                                <form action="{{ route('clients.revert', $client->id) }}" method="POST" onsubmit="return confirm('Revert to Lead?');" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 bg-amber-50 text-amber-600 rounded-lg border border-amber-100">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('leads.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Delete Client Forever?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-lg border border-rose-100">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-400 text-sm font-bold">No clients found.</div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($clients->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>

        </div>

        <!-- Add Payment Modal (shared logic with clients.show) -->
        <div x-data="{ 
                showModal: false, 
                isSplitPayment: false,
                clientId: '',
                clientName: '',
                assigneeId: null,
                assigneeName: 'None',
                totalAmount: 0
            }" 
            x-on:open-payment-modal.window="
                showModal = true; 
                clientId = $event.detail.id; 
                clientName = $event.detail.name;
                assigneeId = $event.detail.assignee_id;
                assigneeName = $event.detail.assignee_name;
            " 
            x-show="showModal"
            class="fixed inset-0 z-[9999] overflow-y-auto" x-on:keydown.escape.window="showModal = false" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-on:click="showModal = false"
                    class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" aria-hidden="true">
                </div>

                <div x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-on:click.away="showModal = false"
                    class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative z-[10000]">

                    <form :action="'/clients/' + clientId + '/payments'" method="POST">
                        @csrf
                        <input type="hidden" name="lead_id" :value="clientId">
                        <div class="bg-white px-8 pt-8 pb-6">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-2xl font-black text-slate-900">Add Transaction</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1" x-text="'For Client: ' + clientName"></p>
                                </div>
                                <button type="button" x-on:click="showModal = false"
                                    class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <!-- Split Payment Toggle -->
                                <div class="mb-2">
                                    <label class="flex items-center space-x-2 text-sm text-green-800 font-bold cursor-pointer">
                                        <input type="checkbox" name="is_split_payment" x-model="isSplitPayment" class="rounded text-green-600 focus:ring-green-500">
                                        <span class="ml-2">Split Payment between SBA/TL and BA?</span>
                                    </label>
                                </div>

                                <div x-show="!isSplitPayment">
                                    <label class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">Amount (INR)</label>
                                    <input type="number" name="amount" step="0.01" min="1"
                                        class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="e.g. 5000">
                                </div>

                                <!-- Split Payment Mode -->
                                <div x-show="isSplitPayment" class="space-y-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl shadow-inner">
                                    <div class="mb-3 pb-2 border-b border-slate-200/50 flex items-center justify-between">
                                        <div class="flex-1 mr-4">
                                            <label class="text-[9px] font-black uppercase text-slate-400 block mb-1">Total to Split</label>
                                            <input type="number" x-model="totalAmount" placeholder="Total Amount" class="w-full border-slate-200 rounded-xl p-1.5 text-xs font-bold focus:ring-indigo-500">
                                        </div>
                                        <button type="button" @click="$el.closest('form').split_1_amount.value = (totalAmount/2).toFixed(2); $el.closest('form').split_2_amount.value = (totalAmount/2).toFixed(2);" 
                                                class="bg-indigo-600 text-white px-3 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-700 transition-all shadow-md">
                                            ⚖️ Split 50/50
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-[10px] font-black uppercase text-indigo-600 mb-1 block">Split 1</label>
                                            <select name="split_1_user_id" class="w-full border-slate-200 rounded-xl p-2 text-xs text-gray-700 mb-2 focus:ring-indigo-500">
                                                <template x-if="assigneeId">
                                                    <option :value="assigneeId" x-text="assigneeName + ' (Advisor)'"></option>
                                                </template>
                                                <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }} (Logged by)</option>
                                            </select>
                                            <input type="number" name="split_1_amount" placeholder="Amount 1 (e.g. 13900)" class="w-full border-slate-200 rounded-xl p-2 text-xs font-bold text-indigo-700 placeholder-indigo-200 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black uppercase text-rose-600 mb-1 block">Split 2</label>
                                            <select name="split_2_user_id" class="w-full border-slate-200 rounded-xl p-2 text-xs text-gray-700 mb-2 focus:ring-rose-500">
                                                <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }} (Logged by)</option>
                                                <template x-if="assigneeId && assigneeId != {{ auth()->id() }}">
                                                    <option :value="assigneeId" x-text="assigneeName + ' (Advisor)'"></option>
                                                </template>
                                            </select>
                                            <input type="number" name="split_2_amount" placeholder="Amount 2 (e.g. 13900)" class="w-full border-slate-200 rounded-xl p-2 text-xs font-bold text-rose-700 placeholder-rose-200 focus:ring-rose-500">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">Payment
                                        Mode</label>
                                    <select name="payment_mode" required
                                        class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="UPI/PhonePe/GPay">UPI/PhonePe/GPay</option>
                                        <option value="Bank Transfer (IMPS/NEFT)">Bank Transfer
                                            (IMPS/NEFT)</option>
                                        <option value="Debit/Credit Card">Debit/Credit Card</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">Payment
                                        Type / For</label>
                                    <input type="text" name="subscription_plan" required
                                        class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="e.g., Second Installment, Upsell: Nifty Option">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-8 py-6 flex flex-row-reverse gap-3">
                            <button type="submit"
                                class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">
                                Record Payment
                            </button>
                            <button type="button" x-on:click="showModal = false"
                                class="px-6 py-3 bg-white text-slate-600 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
