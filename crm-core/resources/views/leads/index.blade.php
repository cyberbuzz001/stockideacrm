<x-app-layout>
    <div class="p-4 sm:p-6 max-w-[1600px] mx-auto space-y-6">

        <!-- Page Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Leads Pipeline</h1>
                <p class="text-sm text-slate-500 mt-1">Manage, rank, and convert your leads</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->role === 'Admin')
                    <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 bg-white px-2 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition-colors cursor-pointer w-[180px]" required>
                        <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold py-1.5 px-3 rounded-lg transition-colors">Import CSV</button>
                    </form>
                    <button onclick="document.getElementById('bulkTextModal').classList.remove('hidden')" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-bold py-2.5 px-4 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Paste Data
                    </button>
                    <button onclick="document.getElementById('exportModal').classList.remove('hidden')" class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 px-4 rounded-xl shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export
                    </button>
                @endif
                <button onclick="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-5 rounded-xl shadow-md shadow-indigo-200 transition-all hover:scale-105 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Lead
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center gap-3 shadow-sm animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl font-bold flex items-center gap-3 shadow-sm animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                {{ session('error') }}
            </div>
        @endif

        <!-- Segmented Tabs & Fetch -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex overflow-x-auto w-full sm:w-auto scrollbar-hide space-x-1 p-1">
                @php
                    $tabs = [
                        'new' => ['label' => 'New Leads', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                        'unassigned' => ['label' => 'Unassigned', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'], // NEW TAB
                        'followup' => ['label' => 'Follow-up', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'trial' => ['label' => 'Free Trial', 'icon' => 'M2 10h20M2 14h20M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z'],
                        'paid' => ['label' => 'Paid Client', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'expired' => ['label' => 'Expired', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    ];
                @endphp
                @foreach($tabs as $key => $tab)
                    <a href="{{ route('leads.index', ['tab' => $key]) }}"
                       class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all
                       {{ $activeTab === $key ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        <svg class="w-4 h-4 {{ $activeTab === $key ? 'text-indigo-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                        {{ $tab['label'] }}
                        @if($key === 'unassigned' && auth()->user()->role === 'Admin')
                            <span class="ml-1 bg-red-100 text-red-700 py-0.5 px-2 rounded-full text-[10px]">{{ $unassignedCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if(auth()->user()->role !== 'Admin')
                <form action="{{ route('leads.fetch') }}" method="POST" class="w-full sm:w-auto pr-2">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-bold py-2.5 px-6 rounded-xl transition-colors flex justify-center items-center gap-2 border border-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Fetch 10 Leads
                    </button>
                </form>
            @endif
        </div>

        <!-- Search & Quick Filters -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Search Leads</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or mobile..." class="w-full pl-10 rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Disposition</label>
                    <select name="status" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 font-semibold text-slate-700">
                        <option value="">All Statuses</option>
                        @foreach(['Call Back', 'Follow Up', 'NPC', 'Switch Off', 'Not Reachable', 'Free Trial', 'Trading', 'Make Payment', 'Expected Payment', 'Paid Client'] as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Source Filter -->
                <div class="w-full sm:w-48">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Source</label>
                    <select name="source" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 font-semibold text-slate-700">
                        <option value="">All Sources</option>
                        @foreach(['Direct', 'Social Media', 'Referral', 'Website', 'CSV Upload'] as $src)
                            <option value="{{ $src }}" {{ request('source') == $src ? 'selected' : '' }}>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- AI Score Sort -->
                <div class="w-full sm:w-48">
                    <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1.5">AI Sort 🔥</label>
                    <select name="ai_sort" class="w-full rounded-xl border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm py-2 bg-indigo-50/50 text-sm font-bold text-indigo-900">
                        <option value="">Default Sort</option>
                        <option value="desc" {{ request('ai_sort') == 'desc' ? 'selected' : '' }}>Highest AI Score First</option>
                        <option value="asc" {{ request('ai_sort') == 'asc' ? 'selected' : '' }}>Lowest AI Score First</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:bg-slate-800 transition-colors">Apply Filters</button>
                    @if(request()->hasAny(['search', 'status', 'source']))
                        <a href="{{ route('leads.index', ['tab' => $activeTab]) }}" class="px-4 py-2.5 text-xs font-bold text-slate-400 hover:text-slate-700 transition-colors">Clear</a>
                    @endif
                </div>
            </form>
            
            <!-- Quick Chips -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-2">
                @php
                    $quickStatuses = [
                        'Call Back' => 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100',
                        'Follow Up' => 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100',
                        'Not Reachable' => 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100'
                    ];
                @endphp
                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest flex items-center mr-2">Quick:</span>
                @foreach($quickStatuses as $qs => $classes)
                    <a href="{{ route('leads.index', ['tab' => $activeTab, 'status' => $qs]) }}" class="px-3 py-1.5 rounded-lg border text-xs font-bold transition-colors {{ request('status') === $qs ? 'ring-2 ring-indigo-500 ring-offset-1 ' . $classes : $classes }}">
                        {{ $qs }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Data Table -->
        <form action="{{ route('leads.assign') }}" method="POST" id="assignForm" class="relative">
            @csrf
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/70 border-b border-slate-100">
                            <tr>
                                @if(auth()->user()->role === 'Admin')
                                    <th class="px-5 py-4 w-12 text-center">
                                        <input type="checkbox" onclick="toggleAll(this)" class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                    </th>
                                @endif
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Lead Profile</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Profile Health</th>
                                <th class="px-6 py-4 text-[10px] font-black text-indigo-400 uppercase tracking-widest text-center">AI Score</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 relative">
                            @forelse($leads as $lead)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    @if(auth()->user()->role === 'Admin')
                                        <td class="px-5 py-4 text-center">
                                            <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="lead-checkbox rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                                        </td>
                                    @endif
                                    
                                    <!-- Lead Profile -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm flex-shrink-0">
                                                {{ substr($lead->name ?: '?', 0, 2) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors text-base">
                                                    {{ $lead->name ?: 'Unknown Lead' }}
                                                </a>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <a href="tel:{{ $lead->mobile }}" title="Call" class="text-xs text-slate-500 font-mono hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                        📱 {{ $lead->mobile }}
                                                    </a>
                                                    @php
                                                        $waMessage = urlencode("Hi {$lead->name}, this is " . auth()->user()->name . " from StockIdea. I'm reaching out regarding your recent inquiry. How can I help you today?");
                                                    @endphp
                                                    <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->mobile) }}?text={{ $waMessage }}" target="_blank" class="text-emerald-500 hover:text-emerald-600 transition-colors flex items-center gap-1 bg-emerald-50 px-1.5 py-0.5 rounded text-[10px] font-bold border border-emerald-100" title="WhatsApp 1-Click">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                                        Message
                                                    </a>
                                                    @if($lead->city)
                                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                        <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $lead->city }}</span>
                                                    @endif
                                                    @if($lead->last_seen_at)
                                                        <div class="ml-2 inline-flex items-center gap-1 bg-amber-50 border border-amber-200 text-amber-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-amber-100" title="Duplicate lead insertion attempt detected">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                            Duplicate
                                                        </div>
                                                    @endif
                                                    @if($lead->status === 'Cold Lead' && $lead->created_at->diffInHours(now()) >= 2)
                                                        <div class="ml-2 inline-flex items-center gap-1 bg-rose-50 border border-rose-100 text-rose-600 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest animate-pulse" title="Lead untouched for > 2 hours">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            Aging
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $col = \App\Services\LeadStatusService::getStatusColor($lead->status);
                                            $icn = \App\Services\LeadStatusService::getStatusIcon($lead->status);
                                            $badgeClass = "bg-{$col}-50 text-{$col}-700 border-{$col}-200";
                                            if($col === 'gray' || $col === 'slate') $badgeClass = "bg-slate-100 text-slate-700 border-slate-200";
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $badgeClass }}">
                                            <span>{{ $icn }}</span>
                                            {{ $lead->status }}
                                        </span>
                                        @if($lead->follow_up_date && in_array($lead->status, ['Call Back', 'Follow Up']))
                                            <div class="text-[10px] font-black text-slate-400 mt-1.5">
                                                🗓️ {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M, h:i A') }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Health / Completion -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest mb-1">
                                            <span class="text-slate-400">Completion</span>
                                            <span class="{{ $lead->completion_percentage == 100 ? 'text-emerald-500' : 'text-indigo-600' }}">{{ $lead->completion_percentage }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all duration-1000 {{ $lead->completion_percentage == 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $lead->completion_percentage }}%"></div>
                                        </div>
                                    </td>

                                    <!-- AI Score -->
                                    <td class="px-6 py-4 text-center">
                                        @if($lead->lead_score > 80)
                                            <div class="inline-flex items-center justify-center gap-1 bg-rose-50 border border-rose-100 text-rose-600 px-3 py-1.5 rounded-xl font-black text-sm relative overflow-hidden group-hover:scale-105 transition-transform">
                                                <div class="absolute inset-0 bg-rose-400/20 animate-pulse"></div>
                                                🔥 {{ $lead->lead_score }}
                                            </div>
                                        @elseif($lead->lead_score > 50)
                                            <div class="inline-flex items-center justify-center gap-1 bg-amber-50 border border-amber-100 text-amber-600 px-3 py-1.5 rounded-xl font-black text-sm">
                                                ⭐ {{ $lead->lead_score }}
                                            </div>
                                        @else
                                            <div class="inline-flex items-center justify-center gap-1 bg-slate-50 border border-slate-200 text-slate-500 px-3 py-1.5 rounded-xl font-black text-sm">
                                                ❄️ {{ $lead->lead_score }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('leads.show', $lead->id) }}#activities" title="View Disposition History" class="inline-flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 w-9 h-9 rounded-xl transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <button type="button" onclick='openQuickResponse(@json($lead))' class="inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                Connect
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-100 shadow-sm">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                        <p class="text-slate-500 font-bold text-sm">No leads found in this view.</p>
                                        <p class="text-slate-400 text-xs mt-1">Try adjusting your tabs or filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($leads instanceof \Illuminate\Pagination\AbstractPaginator && $leads->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $leads->links() }}
                    </div>
                @endif
            </div>

            <!-- Sticky Bulk Actions Bar (Admin Only) -->
            @if(auth()->user()->role === 'Admin')
                <div id="bulkActionBar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 border border-slate-700 shadow-2xl p-3 rounded-2xl flex items-center gap-4 transition-all duration-300 translate-y-24 opacity-0 pointer-events-none z-40">
                    <div class="bg-indigo-500 text-white w-8 h-8 rounded-full flex justify-center items-center text-xs font-black shadow-inner" id="selectedCountCircle">0</div>
                    <div class="h-6 w-px bg-slate-700"></div>
                    
                    <div class="flex items-center gap-2">
                        <select id="bulk_user_id" class="bg-slate-800 border-slate-700 text-slate-200 text-sm rounded-xl py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500 font-semibold">
                            <option value="">Reassign to Agent...</option>
                            @foreach(App\Models\User::where('role', '!=', 'Admin')->get() as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="submitBulkReassign()" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md transition-all">Assign</button>
                        <button type="button" onclick="submitAutoDistribute()" class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md transition-all" title="Divide equally among all roles">Auto Divide</button>
                    </div>

                    <div class="h-6 w-px bg-slate-700 mx-1"></div>

                    <div class="flex items-center gap-2">
                        <select id="bulk_status" class="bg-slate-800 border-slate-700 text-slate-200 text-sm rounded-xl py-2 pl-3 pr-8 focus:ring-emerald-500 focus:border-emerald-500 font-semibold">
                            <option value="">Change Status...</option>
                            @foreach(['Call Back', 'Follow Up', 'NPC', 'Switch Off', 'Not Reachable', 'Free Trial', 'Trading', 'Make Payment', 'Expected Payment', 'Paid Client'] as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="submitBulkStatus()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md transition-all">Set Status</button>
                    </div>

                    <div class="h-6 w-px bg-slate-700 mx-1"></div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="submitBulkDelete()" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Bulk Delete
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>

    <!-- Create Lead Modal (Modernized) -->
    <div id="createLeadModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeCreateModal()"></div>
        <div class="absolute inset-x-4 top-[10vh] sm:inset-auto sm:top-1/2 sm:left-1/2 sm:transform sm:-translate-x-1/2 sm:-translate-y-1/2 bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden scale-95 transition-transform duration-300" id="createLeadModalContent">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Add New Lead</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">Enter prospect details manually.</p>
                </div>
                <button type="button" onclick="closeCreateModal()" class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex justify-center items-center hover:bg-slate-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('leads.store') }}" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Full Name *</label>
                            <input type="text" name="name" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800" required placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Mobile No *</label>
                            <input type="text" name="mobile" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800" required placeholder="9876543210">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Email Address</label>
                        <input type="email" name="email" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800" placeholder="john@example.com">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">City / Location</label>
                            <input type="text" name="location" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800" placeholder="Mumbai">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Source</label>
                            <select name="source" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                                <option value="Direct">Direct</option>
                                <option value="Social Media">Social Media</option>
                                <option value="Referral">Referral</option>
                                <option value="Website">Website</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Initial Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800 p-3" placeholder="Met at conference..."></textarea>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 px-4 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">Cancel</button>
                    <button type="submit" class="flex-[2] px-4 py-3 bg-indigo-600 text-white rounded-xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 shadow-md shadow-indigo-200 transition-colors">Save & Rank via AI</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Response Sidebar (Polished) -->
    <div id="quickResponseSidebar" class="fixed inset-y-0 right-0 w-full max-w-[480px] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out border-l border-slate-100 flex flex-col">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex-shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Quick Connect
                    </h3>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 bg-slate-200 rounded-full h-1.5 w-32">
                            <div id="completionBar" class="bg-indigo-600 h-1.5 rounded-full transition-all duration-700" style="width: 0%"></div>
                        </div>
                        <span id="completionText" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest whitespace-nowrap">0% Complete</span>
                    </div>
                </div>
                <button onclick="closeQuickResponse()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 flex justify-center items-center hover:bg-slate-100 hover:text-slate-600 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></path></svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 scrollbar-hide">
            <form id="quickResponseForm" method="POST" action="" class="space-y-8">
                @csrf
                <input type="hidden" name="active_tab" value="{{ $activeTab }}">

                <!-- Header / Name -->
                <div class="relative">
                    <label class="absolute -top-2.5 left-3 bg-white px-1.5 text-[10px] font-black text-indigo-400 uppercase tracking-widest z-10">Lead Name</label>
                    <input type="text" name="name" id="qName" class="w-full text-lg font-black text-slate-800 border-2 border-indigo-100 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-0 transition-colors bg-white shadow-sm" placeholder="Name">
                </div>

                <!-- Primary Action Section (Disposition) -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Disposition Status *
                    </label>
                    <select name="status" id="quickStatusSelect" onchange="toggleQuickFields()" class="w-full border-slate-200 rounded-xl p-3 font-bold text-slate-800 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors cursor-pointer" required>
                        <option value="">-- Choose Update --</option>
                        @foreach(\App\Services\LeadStatusService::getStatusDefinitions() as $st => $def)
                            @if($st !== 'Paid Client')
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endif
                        @endforeach
                        <option value="Interested">Interested</option>
                        <option value="DND">DND (Do Not Disturb)</option>
                    </select>

                    <!-- Dynamic Fields -->
                    <div id="qFollowupGroup" class="mt-4 hidden animate-fadeIn bg-indigo-50 p-3 rounded-xl border border-indigo-100/50">
                        <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1.5">Scheduled Follow-up Date/Time</label>
                        <input type="datetime-local" name="follow_up_date" class="w-full border-white/50 bg-white rounded-lg p-2.5 text-sm font-bold text-indigo-900 focus:ring-indigo-500">
                    </div>

                    <div id="qTrialGroup" class="mt-4 hidden animate-fadeIn bg-cyan-50 p-3 rounded-xl border border-cyan-100/50">
                        <label class="block text-[10px] font-black text-cyan-700 uppercase tracking-widest mb-1.5">Trial Expiry Date</label>
                        <input type="date" name="trial_end_date" class="w-full border-white/50 bg-white rounded-lg p-2.5 text-sm font-bold text-cyan-900 focus:ring-cyan-500">
                    </div>

                    <div id="qPaymentGroup" class="mt-4 hidden animate-fadeIn bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1.5">Expected Amount (INR)</label>
                                <input type="number" name="amount" placeholder="5000" class="w-full border-white rounded-lg p-2.5 text-sm font-bold text-emerald-900 focus:ring-emerald-500 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1.5">Expected Mode</label>
                                <select name="payment_mode" class="w-full border-white rounded-lg p-2.5 text-sm font-bold text-emerald-900 focus:ring-emerald-500 shadow-sm">
                                    <option value="UPI">UPI</option>
                                    <option value="NEFT">NEFT/RTGS</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                        </div>
                        <div class="px-3 py-2 bg-emerald-100/50 rounded-lg border border-emerald-200/50 flex align-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[10px] font-bold text-emerald-800 leading-snug">Ensure KYC specifics (PAN, Adhaar) are captured below before converting to Paid Client.</p>
                        </div>
                    </div>
                </div>

                <!-- Call Notes -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Discussion Notes
                    </label>
                    <textarea name="notes" id="qNotes" rows="4" class="w-full border-slate-200 rounded-xl p-4 text-sm font-medium text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all shadow-inner bg-slate-50/50" placeholder="Summarize your conversation..."></textarea>
                </div>

                <!-- Enrichment Accordion -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest w-1/3 border-r border-slate-200">Field</th>
                                <th class="px-4 py-3 text-[10px] font-black text-slate-500 uppercase tracking-widest">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30">Alt Mobile</td>
                                <td class="p-0"><input type="text" name="alt_mobile" id="qAltMobile" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-medium" placeholder="Optional"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30">City</td>
                                <td class="p-0"><input type="text" name="city" id="qCity" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-medium" placeholder="City Name"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30 text-rose-600 flex gap-1 items-center">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    PAN No.
                                </td>
                                <td class="p-0"><input type="text" name="pan_number" id="qPan" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-bold uppercase" placeholder="ABCDE1234F"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30 text-rose-600">Aadhaar No.</td>
                                <td class="p-0"><input type="text" name="aadhaar_number" id="qAadhaar" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-bold" placeholder="1234 5678 9012"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30 text-indigo-600">Demat ID</td>
                                <td class="p-0"><input type="text" name="demat_id" id="qDemat" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-bold" placeholder="16 Digit ID"></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-xs font-bold text-slate-500 border-r border-slate-100 bg-slate-50/30">Invest Cap</td>
                                <td class="p-0">
                                    <select name="investment_cap" id="qInvestCap" class="w-full border-0 focus:ring-0 text-sm p-3 bg-transparent font-medium text-slate-700">
                                        <option value="">Select Capacity...</option>
                                        <option value="Below 1L">Below 1L</option>
                                        <option value="1L - 5L">1L - 5L</option>
                                        <option value="5L - 10L">5L - 10L</option>
                                        <option value="Above 10L">Above 10L</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </form>
        </div>

        <!-- Sticky Footer -->
        <div class="p-6 border-t border-slate-100 bg-white flex space-x-3 flex-shrink-0 z-10">
            <button type="button" onclick="closeQuickResponse()" class="flex-1 px-4 py-3.5 text-slate-500 font-bold text-xs uppercase tracking-widest bg-slate-50 hover:bg-slate-100 rounded-xl transition-colors border border-slate-200">Cancel</button>
            <button type="button" onclick="document.getElementById('quickResponseForm').submit()" class="flex-[2] bg-indigo-600 text-white font-black py-3.5 px-6 rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-colors text-sm uppercase tracking-widest flex justify-center items-center gap-2">
                Save & Update
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <!-- Backdrop for Sidebar -->
    <div id="sidebarBackdrop" onclick="closeQuickResponse()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300"></div>

    <script>
        // Modal Logic
        function openCreateModal() {
            const modal = document.getElementById('createLeadModal');
            const content = document.getElementById('createLeadModalContent');
            modal.classList.remove('hidden');
            // triggers reflow
            void modal.offsetWidth; 
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }

        function closeCreateModal() {
            const modal = document.getElementById('createLeadModal');
            const content = document.getElementById('createLeadModalContent');
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // Checkbox & Bulk Bar Logic
        function toggleAll(source) {
            const checkboxes = document.getElementsByClassName('lead-checkbox');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.lead-checkbox:checked').length;
            const bar = document.getElementById('bulkActionBar');
            const circle = document.getElementById('selectedCountCircle');
            
            if (!bar) return; // Non-admins

            circle.innerText = count;

            if (count > 0) {
                bar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
            } else {
                bar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
            }
        }

        document.querySelectorAll('.lead-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function submitBulkReassign() {
            const count = document.querySelectorAll('.lead-checkbox:checked').length;
            const userId = document.getElementById('bulk_user_id').value;
            if (count === 0) return alert('Select leads to reassign.');
            if (!userId) return alert('Select an agent.');
            
            const agentName = document.getElementById('bulk_user_id').options[document.getElementById('bulk_user_id').selectedIndex].text;
            if (confirm(`Assign ${count} leads to ${agentName}?`)) {
                const form = document.getElementById('assignForm');
                form.action = "{{ route('leads.assign') }}";
                form.submit();
            }
        }

        function submitBulkStatus() {
            const count = document.querySelectorAll('.lead-checkbox:checked').length;
            const status = document.getElementById('bulk_status').value;
            if (count === 0) return alert('Select leads.');
            if (!status) return alert('Select status.');
            
            if (confirm(`Set status of ${count} leads to "${status}"?`)) {
                const form = document.getElementById('assignForm');
                form.action = "{{ route('leads.bulk-status') }}";
                form.submit();
            }
        }

        function submitBulkDelete() {
            const form = document.getElementById('assignForm');
            const checkboxes = form.querySelectorAll('.lead-checkbox:checked');
            if (checkboxes.length === 0) return alert('Select leads first.');

            if (confirm(`WARNING: Are you sure you want to permanently delete ${checkboxes.length} leads? This action cannot be undone.`)) {
                // Collect IDs
                const ids = Array.from(checkboxes).map(cb => cb.value);
                
                // Submit via AJAX
                fetch("{{ route('leads.bulk-delete') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ lead_ids: ids })
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.error || 'Something went wrong');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Server error occurred during bulk delete.');
                });
            }
        }

        function submitAutoDistribute() {
            const count = document.querySelectorAll('.lead-checkbox:checked').length;
            if (count === 0) return alert('Select leads.');

            const agentIds = Array.from(document.getElementById('bulk_user_id').options)
                            .filter(opt => opt.value !== "")
                            .map(opt => opt.value);
            
            if (agentIds.length === 0) return alert('No agents found.');
            if (!confirm(`Distribute ${count} leads equally among ${agentIds.length} agents?`)) return;

            const form = document.getElementById('assignForm');
            form.action = "{{ route('leads.auto-distribute') }}";
            
            agentIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'agent_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            form.submit();
        }

        // Quick Response Sidebar Logic
        function openQuickResponse(lead) {
            const sidebar = document.getElementById('quickResponseSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const form = document.getElementById('quickResponseForm');

            // Set Action
            form.action = `/leads/${lead.id}/activity`;

            // Core fields
            document.getElementById('qName').value = lead.name;
            document.getElementById('quickStatusSelect').value = lead.status;
            
            // Details
            document.getElementById('qAltMobile').value = lead.alt_mobile || '';
            document.getElementById('qCity').value = lead.city || '';
            document.getElementById('qPan').value = lead.pan_number || '';
            document.getElementById('qAadhaar').value = lead.aadhaar_number || '';
            document.getElementById('qDemat').value = lead.demat_id || '';
            document.getElementById('qInvestCap').value = lead.investment_cap || '';
            document.getElementById('qNotes').value = '';

            // Health Bar
            const comp = lead.completion_percentage || 0;
            document.getElementById('completionBar').style.width = comp + '%';
            document.getElementById('completionBar').className = `h-1.5 rounded-full transition-all duration-700 ${comp === 100 ? 'bg-emerald-500' : 'bg-indigo-600'}`;
            document.getElementById('completionText').innerText = comp + '% Complete';
            document.getElementById('completionText').className = `text-[10px] font-black uppercase tracking-widest whitespace-nowrap ${comp === 100 ? 'text-emerald-600' : 'text-indigo-600'}`;

            toggleQuickFields();

            backdrop.classList.remove('hidden');
            // reflow
            void backdrop.offsetWidth;
            backdrop.classList.add('opacity-100');
            sidebar.classList.remove('translate-x-full');
        }

        function closeQuickResponse() {
            const sidebar = document.getElementById('quickResponseSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            sidebar.classList.add('translate-x-full');
            backdrop.classList.remove('opacity-100');
            setTimeout(() => backdrop.classList.add('hidden'), 300);
        }

        function toggleQuickFields() {
            const status = document.getElementById('quickStatusSelect').value;
            const followup = document.getElementById('qFollowupGroup');
            const trial = document.getElementById('qTrialGroup');
            const payment = document.getElementById('qPaymentGroup');

            followup.classList.add('hidden');
            trial.classList.add('hidden');
            payment.classList.add('hidden');

            if (status === 'Call Back' || status === 'Follow Up') {
                followup.classList.remove('hidden');
            } else if (status === 'Free Trial') {
                trial.classList.remove('hidden');
            } else if (status === 'Make Payment' || status === 'Expected Payment') {
                payment.classList.remove('hidden');
            }
        }
    </script>
    <x-bulk-import-modal />
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>
