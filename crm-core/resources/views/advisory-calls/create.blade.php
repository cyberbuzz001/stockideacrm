<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Market Call') }}
            </h2>
            <a href="{{ route('advisory-calls.index') }}" class="text-sm text-gray-500 hover:text-gray-800">
                ← Back to Calls
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <form action="{{ route('advisory-calls.store') }}" method="POST" id="callForm">
                    @csrf

                    <!-- Segment Selection -->
                    <div class="mb-6">
                        <label for="segment"
                            class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Market Segment
                        </label>
                        <select name="segment" id="segment" required onchange="buildTip()"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            @foreach($segments as $seg)
                                <option value="{{ $seg }}">{{ $seg }}</option>
                            @endforeach
                        </select>
                        @error('segment')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ═══════════════════════════════════════
                         TRADE BUILDER (Structured Fields)
                    ═══════════════════════════════════════ -->
                    <div class="mb-6 p-5 bg-slate-50 border border-slate-200 rounded-2xl">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">📊 Trade Details</p>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Stock / Index Name</label>
                                <input type="text" id="stock_name" oninput="buildTip()"
                                    placeholder="e.g. NIFTY, RELIANCE, HDFC"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Action</label>
                                <select id="action" onchange="buildTip()"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                                    <option value="BUY">📈 BUY</option>
                                    <option value="SELL">📉 SELL</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Buy / Entry Price (INR)</label>
                                <input type="number" id="entry_price" step="0.01" oninput="buildTip()"
                                    placeholder="e.g. 2450"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Stop Loss (INR)</label>
                                <input type="number" id="stop_loss" step="0.01" oninput="buildTip()"
                                    placeholder="e.g. 2400"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wider">Target (INR)</label>
                                <input type="number" id="target" step="0.01" oninput="buildTip()"
                                    placeholder="e.g. 2550"
                                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview of the formatted tip -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            📋 Tip Preview (Editable)
                        </label>
                        <textarea name="call_text" id="call_text" rows="4" required
                            placeholder="Fill in the details above OR type a custom tip here..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">This message is auto-built from your inputs above but is fully editable.</p>
                        @error('call_text')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date and Time -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="call_date"
                                class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                                Call Date
                            </label>
                            <input type="date" name="call_date" id="call_date" required value="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            @error('call_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="call_time"
                                class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                                Call Time
                            </label>
                            <input type="time" name="call_time" id="call_time" required value="{{ date('H:i') }}"
                                class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            @error('call_time')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('advisory-calls.index') }}"
                            class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                            🚀 Broadcast Market Call
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function buildTip() {
            const segment  = document.getElementById('segment')?.value || '';
            const stock    = document.getElementById('stock_name')?.value?.trim().toUpperCase() || '';
            const action   = document.getElementById('action')?.value || 'BUY';
            const entry    = document.getElementById('entry_price')?.value || '';
            const sl       = document.getElementById('stop_loss')?.value || '';
            const target   = document.getElementById('target')?.value || '';

            if (!stock) return; // Don't overwrite if nothing typed

            let parts = [`${action} ${stock}`];
            if (entry) parts.push(`@ INR ${entry}`);
            if (sl)    parts.push(`| SL: INR ${sl}`);
            if (target) parts.push(`| TGT: INR ${target}`);

            document.getElementById('call_text').value = parts.join(' ');
        }
    </script>
</x-app-layout>
