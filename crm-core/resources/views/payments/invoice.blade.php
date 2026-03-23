<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }} — {{ $company_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }

            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-8 print:p-0 print:bg-white text-slate-800">

    <div class="max-w-3xl mx-auto bg-white p-12 shadow-md rounded-xl print:shadow-none print:w-full">

        <!-- Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <h1 class="text-3xl font-black text-indigo-700 tracking-tight">{{ $company_name }}</h1>
                <p class="text-sm text-slate-500 mt-1">Premium Market Advisory Services</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-slate-900">INVOICE</h2>
                <p class="text-slate-500 font-mono mt-1">
                    #INV-{{ $payment->created_at->format('Y') }}-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p class="text-sm text-slate-400 mt-2">
                    Date: {{ $payment->payment_date->format('M d, Y') }}<br>
                    Status: <span class="font-bold text-green-600 uppercase">{{ $payment->status }}</span>
                </p>
            </div>
        </div>

        <!-- Bill To -->
        <div class="border-t border-b border-slate-100 py-8 mb-8 grid grid-cols-2 gap-8">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Billed To</h3>
                <p class="font-bold text-lg text-slate-900">{{ $payment->lead->name ?? 'Deleted Lead' }}</p>
                @if($payment->lead && $payment->lead->company)
                <p class="text-slate-600">{{ $payment->lead->company }}</p>@endif
                <p class="text-slate-600">{{ $payment->lead->email ?? 'N/A' }}</p>
                <p class="text-slate-600">{{ $payment->lead->mobile ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Payment Details</h3>
                <p class="text-slate-600"><span class="font-medium">Method:</span> {{ $payment->payment_mode }}</p>
                <p class="text-slate-600"><span class="font-medium">Transaction ID:</span>
                    {{ $payment->transaction_id ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Line Items -->
        <div class="mb-12">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-black tracking-widest">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Description</th>
                        <th class="px-4 py-3 text-right">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700">
                    <tr>
                        <td class="px-4 py-4 border-b border-slate-50">
                            <p class="font-bold text-slate-900 text-base mb-1">
                                {{ $payment->subscription_plan ?? 'Advisory Subscription' }}</p>
                            <p class="text-slate-500">Service Plan Subscription</p>
                        </td>
                        <td class="px-4 py-4 border-b border-slate-50 text-right font-mono font-bold text-base">
                            INR {{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot class="text-slate-900">
                    <tr>
                        <td class="px-4 py-4 font-bold text-right">Total</td>
                        <td class="px-4 py-4 text-right font-black text-2xl text-indigo-700">
                            INR {{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-center pt-8 border-t border-slate-100">
            <p class="text-slate-500 text-sm mb-4">Thank you for choosing {{ $company_name }}. For support, contact {{ config('mail.from.address') }}.</p>
            <p class="text-xs text-slate-400 uppercase tracking-widest">Authorized By {{ $company_name }} Admin</p>
        </div>

        <!-- Print Button -->
        <div class="fixed bottom-8 right-8 no-print">
            <button onclick="window.print()"
                class="bg-indigo-600 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-indigo-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Invoice
            </button>
        </div>

    </div>
</body>

</html>
