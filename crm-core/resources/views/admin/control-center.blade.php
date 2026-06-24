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

            <!-- ─── F. API & EXTERNAL INTEGRATIONS ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden lg:col-span-2">
                <div class="bg-gradient-to-r from-slate-800 to-indigo-900 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">API & External Integrations</h2>
                            <p class="text-indigo-200 text-xs font-medium">Configure credentials for communication and AI services</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Calling API -->
                    <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider">Calling API</span>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Provider</label>
                            <select onchange="saveSetting('calling_provider', this.value)" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-blue-500 py-1.5">
                                <option value="none" {{ ($settings['calling_provider'] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
                                <option value="twilio" {{ ($settings['calling_provider'] ?? '') === 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="exotel" {{ ($settings['calling_provider'] ?? '') === 'exotel' ? 'selected' : '' }}>Exotel</option>
                                <option value="viniculum" {{ ($settings['calling_provider'] ?? '') === 'viniculum' ? 'selected' : '' }}>Viniculum</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">API Key / SID</label>
                            <input type="password" value="{{ $settings['calling_api_key'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-blue-500 py-1.5"
                                   onchange="saveSetting('calling_api_key', this.value)">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">API Secret / Token</label>
                            <input type="password" value="{{ $settings['calling_api_secret'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-blue-500 py-1.5"
                                   onchange="saveSetting('calling_api_secret', this.value)">
                        </div>
                    </div>

                    <!-- SMS API -->
                    <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 bg-amber-100 text-amber-600 rounded-lg"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider">SMS API</span>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Provider</label>
                            <select onchange="saveSetting('sms_provider', this.value)" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 py-1.5">
                                <option value="none" {{ ($settings['sms_provider'] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
                                <option value="textlocal" {{ ($settings['sms_provider'] ?? '') === 'textlocal' ? 'selected' : '' }}>TextLocal</option>
                                <option value="msg91" {{ ($settings['sms_provider'] ?? '') === 'msg91' ? 'selected' : '' }}>MSG91</option>
                                <option value="twilio" {{ ($settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' }}>Twilio</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">API Key</label>
                            <input type="password" value="{{ $settings['sms_api_key'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 py-1.5"
                                   onchange="saveSetting('sms_api_key', this.value)">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Sender ID</label>
                            <input type="text" value="{{ $settings['sms_api_secret'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-amber-500 py-1.5"
                                   placeholder="e.g. STKIDA"
                                   onchange="saveSetting('sms_api_secret', this.value)">
                        </div>
                    </div>

                    <!-- WhatsApp Business -->
                    <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100" x-data="{ provider: '{{ $settings['whatsapp_provider'] ?? 'none' }}' }">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg></div>
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider">WhatsApp Biz</span>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Provider</label>
                            <select @change="provider = $el.value; saveSetting('whatsapp_provider', $el.value)" class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5">
                                <option value="none" :selected="provider === 'none'">None</option>
                                <option value="meta" :selected="provider === 'meta'">Meta (Cloud API)</option>
                                <option value="evolution" :selected="provider === 'evolution'">Evolution API (Docker)</option>
                                <option value="wati" :selected="provider === 'wati'">WATI</option>
                                <option value="interakt" :selected="provider === 'interakt'">Interakt</option>
                            </select>
                        </div>
                        <div x-show="provider !== 'none'">
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">
                                <span x-text="provider === 'evolution' ? 'API Key (apikey)' : 'Permanent Token'">Permanent Token</span>
                            </label>
                            <input type="password" value="{{ $settings['whatsapp_api_key'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5"
                                   onchange="saveSetting('whatsapp_api_key', this.value)">
                        </div>
                        
                        <template x-if="provider === 'meta'">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Phone Number ID</label>
                                    <input type="text" value="{{ $settings['whatsapp_api_phone_id'] ?? '' }}" 
                                           class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5" 
                                           onchange="saveSetting('whatsapp_api_phone_id', this.value)">
                                </div>
                                
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">WABA ID</label>
                                    <input type="text" value="{{ $settings['whatsapp_business_account_id'] ?? '' }}" 
                                           class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5" 
                                           onchange="saveSetting('whatsapp_business_account_id', this.value)">
                                </div>
                                
                                <div class="pt-2">
                                    <button type="button" onclick="syncMetaTemplates(this)" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs transition shadow flex justify-center items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        Sync Meta Templates
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <template x-if="provider === 'evolution'">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Evolution API Base URL</label>
                                    <input type="text" value="{{ $settings['whatsapp_evolution_url'] ?? 'http://localhost:8080' }}" 
                                           class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5" 
                                           onchange="saveSetting('whatsapp_evolution_url', this.value)">
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Instance Name</label>
                                    <input type="text" value="{{ $settings['whatsapp_evolution_instance'] ?? 'shreesvarn' }}" 
                                           class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-emerald-500 py-1.5" 
                                           onchange="saveSetting('whatsapp_evolution_instance', this.value)">
                                </div>
                                <div class="pt-2">
                                    <button type="button" onclick="loadWhatsAppQRCode()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl text-xs transition shadow flex justify-center items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Get QR Code
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <div id="qrCodeContainer" class="mt-4 flex flex-col items-center justify-center hidden">
                                    <div class="p-3 bg-white border border-slate-200 rounded-2xl shadow-inner">
                                        <img id="qrCodeImage" src="" alt="WhatsApp QR Code" class="w-48 h-48">
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-2 font-medium text-center">Scan this code using WhatsApp > Linked Devices to connect the CRM.</p>
                                </div>
                                <div id="qrCodeLoading" class="hidden mt-4 text-xs text-center text-slate-500 font-bold animate-pulse">
                                    Generating QR code...
                                </div>
                                <div id="qrCodeStatus" class="hidden mt-4 text-xs text-center text-emerald-600 font-bold">
                                </div>
                                <div id="qrCodeError" class="hidden mt-4 text-xs text-center text-rose-500 font-bold">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Integration -->
                    <div class="space-y-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="p-1.5 bg-indigo-100 text-indigo-600 rounded-lg"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                            <span class="text-xs font-black text-slate-700 uppercase tracking-wider">AI Integration</span>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Engine</label>
                            <div class="flex gap-1 p-1 bg-white rounded-lg border border-slate-200">
                                <button onclick="saveSetting('ai_provider', 'openai'); updateAiButtons(this)" 
                                        class="flex-1 py-1 text-[10px] font-black uppercase rounded {{ ($settings['ai_provider'] ?? 'openai') === 'openai' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-50' }}">
                                    OpenAI
                                </button>
                                <button onclick="saveSetting('ai_provider', 'perplexity'); updateAiButtons(this)" 
                                        class="flex-1 py-1 text-[10px] font-black uppercase rounded {{ ($settings['ai_provider'] ?? '') === 'perplexity' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-50' }}">
                                    Perplexity
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">API Secret Key</label>
                            <input type="password" value="{{ $settings['ai_api_key'] ?? '' }}" 
                                   class="w-full rounded-xl border-slate-200 text-xs font-bold focus:ring-indigo-500 py-1.5"
                                   onchange="saveSetting('ai_api_key', this.value)">
                        </div>
                        <div class="pt-2">
                            <div class="p-2 border border-indigo-100 bg-indigo-50/50 rounded-xl">
                                <p class="text-[9px] text-indigo-700 font-bold leading-tight">Used for automated lead analysis and sentiment tracking.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ─── E. INTERNAL DATA MANAGEMENT (FLUSH) ─── -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden lg:col-span-2">
                <div class="bg-gradient-to-r from-slate-700 to-slate-900 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <div>
                            <h2 class="text-white font-black text-lg tracking-tight">Internal Data Management</h2>
                            <p class="text-slate-300 text-xs font-medium">Bulk purge leads and declutter the system</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Flush Unassigned -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Flush Unassigned</h3>
                                <p class="text-[10px] text-slate-500 mt-1">Permanently delete all leads that are currently not assigned to any agent.</p>
                            </div>
                            <button onclick="flushLeads('unassigned')" class="mt-4 w-full py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-black shadow-sm hover:bg-rose-50 transition-all">
                                Purge Unassigned
                            </button>
                        </div>

                        <!-- Flush Cold Leads -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Flush Cold Leads</h3>
                                <p class="text-[10px] text-slate-500 mt-1">Permanently delete all leads currently in the 'Cold Lead' status pool.</p>
                            </div>
                            <button onclick="flushLeads('cold')" class="mt-4 w-full py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-black shadow-sm hover:bg-rose-50 transition-all">
                                Purge Cold Pool
                            </button>
                        </div>

                        <!-- Flush Older Leads -->
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Flush Old Data</h3>
                                <p class="text-[10px] text-slate-500 mt-1">Permanently delete all leads created more than 30 days ago.</p>
                            </div>
                            <button onclick="flushLeads('older_than_30')" class="mt-4 w-full py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-black shadow-sm hover:bg-rose-50 transition-all">
                                Purge >30 Days
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-rose-50 rounded-2xl border border-rose-100">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <p class="text-xs font-black text-rose-900 uppercase tracking-widest">Danger Zone: Permanent Deletion</p>
                                <p class="text-[10px] text-rose-700 font-medium">Flushing leads is irreversible. All associated activities, notes, and messages for the deleted leads will also be purged from the database.</p>
                            </div>
                        </div>
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

        function updateAiButtons(active) {
            const parent = active.parentElement;
            parent.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('bg-indigo-600','text-white','shadow-sm');
                btn.classList.add('text-slate-400','hover:bg-slate-50');
            });
            active.classList.remove('text-slate-400','hover:bg-slate-50');
            active.classList.add('bg-indigo-600','text-white','shadow-sm');
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

        function flushLeads(type) {
            const messages = {
                unassigned: "Are you sure you want to permanently delete ALL UNASSIGNED leads?",
                cold: "Are you sure you want to permanently delete ALL COLD leads?",
                older_than_30: "Are you sure you want to permanently delete all leads older than 30 days?"
            };

            if (!confirm(messages[type] || "Are you sure you want to perform this bulk deletion?")) return;

            showSettingsToast('Starting flush operation...', false);

            fetch('{{ route("leads.flush") }}', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ type })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showSettingsToast(data.message);
                } else {
                    showSettingsToast(data.error || 'Flush failed', true);
                }
            })
            .catch(e => showSettingsToast('Error connecting to server', true));
        }

        function loadWhatsAppQRCode() {
            const container = document.getElementById('qrCodeContainer');
            const img = document.getElementById('qrCodeImage');
            const loading = document.getElementById('qrCodeLoading');
            const status = document.getElementById('qrCodeStatus');
            const error = document.getElementById('qrCodeError');

            container.classList.add('hidden');
            status.classList.add('hidden');
            error.classList.add('hidden');
            loading.classList.remove('hidden');

            fetch('{{ route("admin.whatsapp.qrcode") }}')
            .then(r => r.json())
            .then(data => {
                loading.classList.add('hidden');
                if (data.status === 'connected') {
                    status.textContent = data.message;
                    status.classList.remove('hidden');
                } else if (data.status === 'qrcode') {
                    img.src = data.qrcode;
                    container.classList.remove('hidden');
                } else if (data.error) {
                    error.textContent = data.error;
                    error.classList.remove('hidden');
                }
            })
            .catch(e => {
                loading.classList.add('hidden');
                error.textContent = 'Failed to fetch QR code from server.';
                error.classList.remove('hidden');
            });
        }
    </script>
</x-app-layout>
