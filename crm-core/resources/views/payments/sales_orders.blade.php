<x-app-layout>
    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('payments.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Dashboard
                </a>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sales Orders Ledger</h1>
                <p class="text-sm text-slate-500 mt-0.5">Comprehensive view of all realized revenue</p>
            </div>
            
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 text-white rounded-2xl px-5 py-3 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm border border-white/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-100">Total Realized Revenue</p>
                    <p class="text-2xl font-black tracking-tight">INR {{ number_format(\App\Models\Payment::where('status', 'Verified')->sum('amount')) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest bg-slate-50/50">
                            <th class="px-6 py-4">Transaction Date</th>
                            <th class="px-6 py-4">Client Name</th>
                            <th class="px-6 py-4">Closing Agent</th>
                            <th class="px-6 py-4">Plan Type</th>
                            <th class="px-6 py-4">Mode</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-center">Invoice</th>
                        </tr>
                    </thead>
                    <div class="divide-y divide-slate-50">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50/80 transition-colors group cursor-default border-t border-slate-50">
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                        <span class="font-bold text-slate-700">{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : $payment->created_at->format('d M, Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-black text-slate-900 border-b border-transparent group-hover:border-slate-300 inline-block transition-colors mb-0.5">{{ $payment->lead->name ?? 'Deleted Lead' }}</p>
                                    <p class="text-[10px] font-bold text-slate-400">{{ $payment->lead->mobile ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        {{ $payment->lead->assignee->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">{{ $payment->subscription_plan ?? 'Standard' }}</span>
                                </td>
                                <td class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    {{ $payment->payment_mode }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <p class="font-black text-emerald-600 text-lg">INR {{ number_format($payment->amount) }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('payments.invoice', $payment) }}" target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">No verified payments found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </div>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
        
    </div>
</x-app-layout>
