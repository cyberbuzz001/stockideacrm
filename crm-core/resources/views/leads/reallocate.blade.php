<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6 animate-fadeIn">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="bg-indigo-600 text-white w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </span>
                    Bulk Lead Re-allocation
                </h1>
                <p class="text-slate-500 mt-2 font-medium">Transfer active leads from one agent (e.g., absent or inactive) to another.</p>
            </div>
            
            <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-bold rounded-xl transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Leads
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-2xl flex items-center gap-3 font-bold shadow-sm animate-fadeIn">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-6 py-4 rounded-2xl flex items-center gap-3 font-bold shadow-sm animate-fadeIn">
                <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Information Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-200">
                    <h3 class="text-xl font-black mb-2">How it works</h3>
                    <p class="text-indigo-100 text-sm leading-relaxed mb-6">
                        Select an agent who is currently absent or underperforming. Choose which leads to move, and assign them to an active agent. "All Active Leads" ensures that Expired or "Not Picked" (disposed) leads are ignored, keeping the recipient's pipeline clean.
                    </p>
                    
                    <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-sm border border-white/20">
                        <div class="text-xs font-black uppercase tracking-widest text-indigo-200 mb-1">Agents with active leads</div>
                        <div class="text-3xl font-black">{{ $agentsWithLeads->count() }}</div>
                    </div>
                </div>
            </div>

            <!-- Transfer Form -->
            <div class="lg:col-span-2">
                <form action="{{ route('leads.transfer') }}" method="POST" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    @csrf
                    
                    <h3 class="text-xl font-black text-slate-900 mb-6 border-b border-slate-100 pb-4">Transfer Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        
                        <!-- From Agent -->
                        <div class="space-y-3 relative">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">
                                1. Re-allocate From <span class="text-rose-500">*</span>
                            </label>
                            <select name="from_agent_id" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3 text-slate-700 font-bold bg-slate-50">
                                <option value="">-- Select Source Agent --</option>
                                @foreach($agentsWithLeads as $agent)
                                    <option value="{{ $agent->id }}">
                                        {{ $agent->name }} ({{ $agent->leads_count }} leads)
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Arrow UI connecting the selects on desktop -->
                            <div class="hidden md:flex absolute top-1/2 -right-6 translate-y-[25%] items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-400 z-10 border border-white">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </div>
                        </div>

                        <!-- To Agent -->
                        <div class="space-y-3 pl-0 md:pl-4">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">
                                2. Transfer Leads To <span class="text-rose-500">*</span>
                            </label>
                            <select name="to_agent_id" required class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3 font-bold bg-indigo-50/50 text-indigo-900">
                                <option value="">-- Select Receiving Agent --</option>
                                @foreach($activeAgents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Which Leads? -->
                    <div class="space-y-3 mb-10">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">
                            3. Which Leads to Transfer? <span class="text-rose-500">*</span>
                        </label>
                        <select name="lead_status" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-3 text-slate-700 font-bold">
                            <option value="All">🚀 All Active Leads (excludes NPC/Expired/Not Interested)</option>
                            <option value="Cold Lead">❄️ Only 'Cold Leads'</option>
                            <option value="New">🆕 Only 'New' Leads</option>
                            <option value="Follow Up">📞 Only 'Follow Up' Leads</option>
                            <option value="Call Back">🕒 Only 'Call Back' Leads</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <div class="border-t border-slate-100 pt-6 flex justify-end">
                        <button type="submit" onclick="return confirm('Are you sure you want to perform this bulk transfer? This action cannot be easily undone.')" class="bg-slate-900 hover:bg-black text-white px-8 py-3.5 rounded-xl font-black shadow-lg shadow-slate-200 transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            Execute Transfer
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
