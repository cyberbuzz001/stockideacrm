<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Profile') }}: {{ $client->name }}
            </h2>
            <div class="flex items-center gap-3">
                <form action="{{ route('leads.escalate', $client) }}" method="POST" class="inline">
                    @csrf
                    @if($client->is_escalated)
                        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black border border-slate-200 transition-all uppercase tracking-widest">
                            ✅ De-escalate
                        </button>
                    @else
                        <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-black border border-rose-200 transition-all uppercase tracking-widest">
                            ⚠️ Escalate
                        </button>
                    @endif
                </form>
                <a href="{{ route('clients.edit', $client) }}"
                    class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-bold hover:bg-indigo-100 transition-all">
                    Edit Profile
                </a>
                <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:underline">← Back to
                    Clients</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sidebar: Stats & Info -->
                <div class="space-y-6">
                    <!-- LTV Card -->
                    <div class="bg-indigo-600 text-white p-6 rounded-lg shadow-lg">
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-1">Lifetime Value (LTV)
                        </p>
                        <p class="text-4xl font-black">INR {{ number_format($ltv) }}</p>
                        <p class="text-indigo-100 text-xs mt-4">Total revenue generated from this client across all
                            verified payments.</p>
                    </div>

                    <!-- Client Details -->
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-4 border-b pb-2">Client Info</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-400">Mobile Number</p>
                                <p class="font-bold">{{ $client->mobile }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Email Address</p>
                                <p class="font-bold underline text-blue-600">{{ $client->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Service Start</p>
                                <p class="font-bold">
                                    {{ $client->service_start_date ? $client->service_start_date->format('d M, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-gray-400 font-bold {{ $client->renewal_date && $client->renewal_date->isPast() ? 'text-red-600' : '' }}">
                                    Renewal Date</p>
                                <p
                                    class="font-bold text-lg {{ $client->renewal_date && $client->renewal_date < now()->addDays(7) ? 'text-orange-600' : '' }}">
                                    {{ $client->renewal_date ? $client->renewal_date->format('d M, Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Update Renewal Form -->
                        <div class="mt-6 pt-6 border-t">
                            <form action="{{ route('clients.renewal', $client) }}" method="POST">
                                @csrf
                                <label class="block text-xs font-bold text-gray-700 mb-2">Update Renewal Date</label>
                                <div class="flex space-x-2">
                                    <input type="date" name="renewal_date"
                                        value="{{ $client->renewal_date ? $client->renewal_date->format('Y-m-d') : '' }}"
                                        class="w-full text-xs rounded border-gray-300">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Internal Notes -->
                    <x-lead-notes-widget :lead="$client" />
                </div>

                <!-- Main Content: History -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Service History / Activity -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold">Service & Call History</h3>
                            <span class="text-xs text-gray-400">{{ count($client->activities) }} Activities</span>
                        </div>
                        <div class="p-6 h-96 overflow-y-auto">
                            @forelse($client->activities as $activity)
                                <div class="mb-6 relative">
                                    <div class="flex items-center mb-1">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 mr-3"></div>
                                        <p class="text-sm font-bold text-gray-800">{{ $activity->activity_type }}</p>
                                        <span
                                            class="ml-auto text-[10px] text-gray-400 italic">{{ $activity->created_at->format('d M, Y H:i') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 ml-5 bg-gray-50 p-2 rounded">
                                        {{ $activity->notes }}
                                        <span class="block mt-1 text-[9px] text-gray-400">Logged by:
                                            {{ $activity->user->name ?? 'System' }}</span>
                                    </p>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 italic">No activity logs found for this client.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Payment History (Section 11) -->
                    <style>
                        [x-cloak] {
                            display: none !important;
                        }
                    </style>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden mt-6">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-800">Financial Ledger</h3>
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-payment-modal'))"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all cursor-pointer relative z-10">
                                + Add New Payment
                            </button>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Mode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($client->payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                            {{ $payment->created_at->format('d M, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-700">
                                            {{ $payment->subscription_plan ?? 'Standard' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                            INR {{ number_format($payment->amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            {{ $payment->payment_mode }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-[10px] font-bold rounded-full 
                                                                            {{ $payment->status === 'Verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $payment->status }}
                                            </span>
                                            @if($payment->status === 'Verified')
                                                <a href="{{ route('payments.invoice', $payment) }}" target="_blank"
                                                    class="ml-2 text-indigo-600 hover:text-indigo-900 border-b border-indigo-600 border-dotted text-[10px] font-bold">
                                                    Invoice
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">No payment
                                            internal records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Payment Modal -->
            <div x-data="{ showModal: false }" x-on:open-payment-modal.window="showModal = true" x-show="showModal"
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

                        <form action="{{ route('clients.payments.store', $client) }}" method="POST">
                            @csrf
                            <input type="hidden" name="lead_id" value="{{ $client->id }}">
                            <div class="bg-white px-8 pt-8 pb-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-2xl font-black text-slate-900">Add Transaction</h3>
                                    <button type="button" x-on:click="showModal = false"
                                        class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-1">Amount
                                            (INR)</label>
                                        <input type="number" name="amount" required step="0.01" min="1"
                                            class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="e.g. 5000">
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
    </div>
</x-app-layout>
