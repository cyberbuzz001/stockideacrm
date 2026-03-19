<x-app-layout>
    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Admin Control Center</h1>
                <p class="text-sm text-slate-500 mt-0.5">Centralized configuration for all CRM modules</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Changes save instantly</span>
                <span id="save-indicator" class="w-2 h-2 rounded-full bg-emerald-500 pulse-dot"></span>
            </div>
        </div>

        <!-- SUCCESS TOAST (AJAX) -->
        <div id="settings-toast" class="hidden p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold flex items-center gap-3 shadow-sm animate-fadeIn text-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span id="toast-message">Setting updated</span>
        </div>

        <!-- ═══════════ COMPANY BRANDING ═══════════ -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-white font-black text-lg tracking-tight">Company Branding</h2>
                        <p class="text-orange-100 text-xs font-medium">Set your company name and logo for the CRM</p>
                    </div>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Company Name -->
                <div>
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Company Name</label>
                    <input type="text" value="{{ $settings['company_name'] ?? config('app.name') }}" 
                           class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-amber-500 focus:border-amber-500"
                           placeholder="e.g. StockIdea Financial Services"
                           onchange="saveSetting('company_name', this.value)">
                    <p class="text-[10px] text-slate-400 mt-1">This name appears in the top navigation bar and reports.</p>
                </div>
                
                <!-- Company Logo Form -->
                <div>
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Company Logo</label>
                    <form action="{{ route('admin.settings.branding') }}" method="POST" enctype="multipart/form-data" class="flex items-start gap-4">
                        @csrf
                        <div class="w-16 h-16 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center overflow-hidden bg-slate-50 relative shrink-0">
                            @if(!empty($settings['company_logo']))
                                <img src="{{ Storage::url($settings['company_logo']) }}" alt="Logo" class="w-full h-full object-contain p-1">
                            @else
                                <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" name="company_logo" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-colors">Upload Logo</button>
                        </div>
                    </form>
                    @if(session('success'))
                        <p class="text-xs text-emerald-600 font-bold mt-2">{{ session('success') }}</p>
                    @endif
                    @error('company_logo')
                        <p class="text-xs text-rose-600 font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- ═══════════ 4 SWITCHBOARD GRID ═══════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- ─── A. EMPLOYEE & PRODUCTIVITY ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">Employee & Productivity</h2>
                            <p class="text-violet-200 text-xs font-medium">Heartbeat, shifts, and attendance controls</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Heartbeat Idle -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Inactivity Timeout (Idle)</label>
                            <span id="idle-val" class="text-sm font-black text-violet-600">{{ $settings['heartbeat_idle_minutes'] ?? 10 }} min</span>
                        </div>
                        <input type="range" min="5" max="30" step="1" value="{{ $settings['heartbeat_idle_minutes'] ?? 10 }}"
                               class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-violet-600"
                               oninput="document.getElementById('idle-val').textContent=this.value+' min'"
                               onchange="saveSetting('heartbeat_idle_minutes', this.value)">
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1"><span>5 min</span><span>30 min</span></div>
                    </div>

                    <!-- Heartbeat Inactive -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Inactive Threshold</label>
                            <span id="inactive-val" class="text-sm font-black text-violet-600">{{ $settings['heartbeat_inactive_minutes'] ?? 15 }} min</span>
                        </div>
                        <input type="range" min="10" max="60" step="1" value="{{ $settings['heartbeat_inactive_minutes'] ?? 15 }}"
                               class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-violet-600"
                               oninput="document.getElementById('inactive-val').textContent=this.value+' min'"
                               onchange="saveSetting('heartbeat_inactive_minutes', this.value)">
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1"><span>10 min</span><span>60 min</span></div>
                    </div>

                    <!-- Auto Checkout Toggle -->
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div>
                            <p class="text-sm font-bold text-slate-700">Auto-Checkout</p>
                            <p class="text-xs text-slate-400">Automatically end shifts at configured time</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ ($settings['auto_checkout_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                   onchange="saveSetting('auto_checkout_enabled', this.checked ? '1' : '0')">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <!-- Shift & Lunch -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Shift End</label>
                            <input type="time" value="{{ $settings['shift_end_time'] ?? '17:30' }}"
                                   class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-violet-500 focus:border-violet-500"
                                   onchange="saveSetting('shift_end_time', this.value)">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Lunch Start</label>
                            <input type="time" value="{{ $settings['lunch_start'] ?? '13:00' }}"
                                   class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-violet-500 focus:border-violet-500"
                                   onchange="saveSetting('lunch_start', this.value)">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Lunch End</label>
                            <input type="time" value="{{ $settings['lunch_end'] ?? '13:30' }}"
                                   class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-violet-500 focus:border-violet-500"
                                   onchange="saveSetting('lunch_end', this.value)">
                        </div>
                    </div>

                    <!-- Leave Override -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Quick Override Today's Attendance</label>
                        <div class="flex gap-2">
                            <select id="override-employee" class="flex-1 rounded-xl border-slate-200 text-sm font-bold focus:ring-violet-500 focus:border-violet-500">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role }})</option>
                                @endforeach
                            </select>
                            <button onclick="overrideAttendance('Leave')" class="px-3 py-2 bg-rose-50 text-rose-600 rounded-xl text-xs font-black border border-rose-200 hover:bg-rose-100 transition-colors">Leave</button>
                            <button onclick="overrideAttendance('Half Day')" class="px-3 py-2 bg-amber-50 text-amber-600 rounded-xl text-xs font-black border border-amber-200 hover:bg-amber-100 transition-colors">Half Day</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── B. LEAD & ASSIGNMENT ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">Lead & Assignment</h2>
                            <p class="text-blue-200 text-xs font-medium">Assignment mode, weightage, and recycling rules</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Assignment Mode -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-3">Assignment Mode</label>
                        <div class="flex gap-2">
                            @php $currentMode = $settings['assignment_mode'] ?? 'manual'; @endphp
                            @foreach(['manual' => 'Manual', 'round_robin' => 'Round Robin', 'bucket' => 'Bucket (Pull)'] as $val => $label)
                                <button onclick="saveSetting('assignment_mode', '{{ $val }}'); updateModeButtons(this)"
                                        class="flex-1 py-3 px-4 rounded-xl text-xs font-black uppercase tracking-widest border transition-all
                                        {{ $currentMode === $val ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Lead Weightage Table -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Lead Weightage (Seniority)</label>
                        <div class="bg-slate-50 rounded-2xl p-4 max-h-48 overflow-y-auto space-y-2">
                            @foreach($employees->whereIn('role', ['BA', 'SBA']) as $emp)
                            <div class="flex items-center justify-between py-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">{{ substr($emp->name, 0, 1) }}</div>
                                    <span class="text-sm font-bold text-slate-700">{{ $emp->name }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 bg-slate-200 px-1.5 py-0.5 rounded">{{ $emp->role }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-slate-400">Weight:</span>
                                    <input type="number" min="1" max="5" step="1" value="{{ $emp->lead_weight ?? 1 }}"
                                           class="w-14 rounded-lg border-slate-200 text-sm font-black text-center focus:ring-indigo-500 focus:border-indigo-500"
                                           onchange="saveWeight({{ $emp->id }}, this.value)">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- NPC Recycling -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-black text-slate-500 uppercase tracking-widest">NPC Recycle Limit</label>
                            <span id="npc-val" class="text-sm font-black text-indigo-600">{{ $settings['npc_recycle_limit'] ?? 5 }} attempts</span>
                        </div>
                        <input type="range" min="2" max="15" step="1" value="{{ $settings['npc_recycle_limit'] ?? 5 }}"
                               class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                               oninput="document.getElementById('npc-val').textContent=this.value+' attempts'"
                               onchange="saveSetting('npc_recycle_limit', this.value)">
                        <p class="text-[10px] text-slate-400 mt-1">After this many failed NPC attempts, lead returns to the fresh pool.</p>
                    </div>
                </div>
            </div>

            <!-- ─── C. FINANCIAL & SERVICE ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">Financial & Service</h2>
                            <p class="text-emerald-200 text-xs font-medium">Segments, fees, and revenue goals</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Segment Manager -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Trading Segments</label>
                        <textarea id="segments-input" rows="3"
                                  class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-300"
                                  placeholder="Stock Cash, Nifty Option, MCX, Equity"
                                  onchange="saveSetting('trading_segments', this.value)">{{ $settings['trading_segments'] ?? '' }}</textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Comma-separated list of trading segments your firm offers.</p>
                    </div>

                    <!-- Min Registration Fee -->
                    <div>
                        <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Minimum Registration Fee (INR)</label>
                        <input type="number" min="0" step="100" value="{{ $settings['min_registration_fee'] ?? 0 }}"
                               class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="e.g. 5000"
                               onchange="saveSetting('min_registration_fee', this.value)">
                        <p class="text-[10px] text-slate-400 mt-1">Minimum amount before a lead can become "Paid Client".</p>
                    </div>
                </div>
            </div>

            <!-- ─── D. COMPLIANCE & SECURITY ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">Compliance & Security</h2>
                            <p class="text-rose-200 text-xs font-medium">KYC locks, data masking, and audit</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-6">
                    <!-- KYC Mandatory -->
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div>
                            <p class="text-sm font-bold text-slate-700">KYC Mandatory Lock</p>
                            <p class="text-xs text-slate-400">Block "Paid Client" without PAN & Aadhaar</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ ($settings['kyc_mandatory'] ?? '1') === '1' ? 'checked' : '' }}
                                   onchange="saveSetting('kyc_mandatory', this.checked ? '1' : '0')">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        </label>
                    </div>

                    <!-- Data Masking -->
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div>
                            <p class="text-sm font-bold text-slate-700">Mobile Data Masking</p>
                            <p class="text-xs text-slate-400">Hide mobile number digits for BA users</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ ($settings['data_masking_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                   onchange="saveSetting('data_masking_enabled', this.checked ? '1' : '0')">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════ GLOBAL TICKER BROADCAST ═══════════ -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/20">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-white font-black text-lg tracking-tight">Global Broadcast Ticker</h2>
                        <p class="text-slate-400 text-xs font-medium">Message scrolls across all employee dashboards</p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Broadcast Message</label>
                    <textarea id="ticker-message" rows="2"
                              class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-slate-500 focus:border-slate-500 placeholder-slate-300"
                              placeholder="e.g. Nifty is falling, call all Stock Future clients immediately!">{{ $settings['ticker_message'] ?? '' }}</textarea>
                </div>
                <div class="flex items-center gap-4">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest">Urgency:</label>
                    @php $currentUrgency = $settings['ticker_urgency'] ?? 'normal'; @endphp
                    <div class="flex gap-2" id="urgency-group">
                        <button onclick="setUrgency('normal')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border transition-all {{ $currentUrgency === 'normal' ? 'bg-blue-600 text-white border-blue-600' : 'bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100' }}">
                            🔵 Normal
                        </button>
                        <button onclick="setUrgency('urgent')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border transition-all {{ $currentUrgency === 'urgent' ? 'bg-amber-500 text-white border-amber-500' : 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100' }}">
                            🟠 Urgent
                        </button>
                        <button onclick="setUrgency('emergency')" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border transition-all {{ $currentUrgency === 'emergency' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100' }}">
                            🔴 Emergency
                        </button>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="broadcastTicker()" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-black py-3 rounded-xl text-sm uppercase tracking-widest transition-colors shadow-lg shadow-slate-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Broadcast Now
                    </button>
                    <button onclick="clearTicker()" class="px-6 bg-white border border-slate-200 text-slate-500 font-bold py-3 rounded-xl text-sm hover:bg-slate-50 transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        const csrfToken = '{{ csrf_token() }}';
        let selectedUrgency = '{{ $settings["ticker_urgency"] ?? "normal" }}';

        function saveSetting(key, value) {
            fetch('{{ route("admin.settings.update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ key, value })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) showSettingsToast('Setting "' + key + '" updated');
            })
            .catch(e => showSettingsToast('Error saving setting', true));
        }

        function saveWeight(userId, weight) {
            fetch('{{ route("admin.settings.update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ key: 'lead_weight_' + userId, value: weight })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) showSettingsToast('Lead weight updated');
            });
        }

        function overrideAttendance(type) {
            const userId = document.getElementById('override-employee').value;
            const name = document.getElementById('override-employee').selectedOptions[0].text;
            fetch('{{ route("admin.attendance.override") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ user_id: userId, type })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) showSettingsToast(data.message);
            });
        }

        function setUrgency(level) {
            selectedUrgency = level;
            const buttons = document.getElementById('urgency-group').querySelectorAll('button');
            buttons.forEach(btn => {
                btn.className = btn.className.replace(/bg-\S+\s+text-\S+\s+border-\S+/g, '');
                btn.classList.add('bg-white', 'border-slate-200', 'text-slate-500');
            });
            const activeBtn = [...buttons].find(b => b.textContent.toLowerCase().includes(level));
            if (activeBtn) {
                const colors = { normal: ['bg-blue-600','text-white','border-blue-600'], urgent: ['bg-amber-500','text-white','border-amber-500'], emergency: ['bg-rose-600','text-white','border-rose-600'] };
                activeBtn.classList.remove('bg-white','border-slate-200','text-slate-500');
                colors[level].forEach(c => activeBtn.classList.add(c));
            }
        }

        function broadcastTicker() {
            const message = document.getElementById('ticker-message').value;
            fetch('{{ route("admin.ticker.update") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ message, urgency: selectedUrgency })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) showSettingsToast('Ticker broadcast live');
            });
        }

        function clearTicker() {
            document.getElementById('ticker-message').value = '';
            broadcastTicker();
        }

        function updateModeButtons(active) {
            const parent = active.parentElement;
            parent.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('bg-indigo-600','text-white','border-indigo-600','shadow-lg','shadow-indigo-200');
                btn.classList.add('bg-white','text-slate-500','border-slate-200');
            });
            active.classList.remove('bg-white','text-slate-500','border-slate-200');
            active.classList.add('bg-indigo-600','text-white','border-indigo-600','shadow-lg','shadow-indigo-200');
        }

        function showSettingsToast(message, isError = false) {
            const toast = document.getElementById('settings-toast');
            const msgEl = document.getElementById('toast-message');
            msgEl.textContent = message;
            toast.classList.remove('hidden','bg-emerald-50','border-emerald-200','text-emerald-700','bg-rose-50','border-rose-200','text-rose-700');
            if (isError) { toast.classList.add('bg-rose-50','border-rose-200','text-rose-700'); }
            else { toast.classList.add('bg-emerald-50','border-emerald-200','text-emerald-700'); }
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => toast.classList.add('hidden'), 3000);
        }
    </script>
</x-app-layout>
