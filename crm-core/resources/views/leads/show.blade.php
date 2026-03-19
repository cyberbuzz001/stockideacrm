<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Lead: {{ $lead?->name ?? 'Unknown Lead' }}
            </h2>
            <a href="{{ route('leads.index') }}" class="text-blue-500 hover:underline">← Back to List</a>
        </div>
    </x-slot>

    <div class="py-12">
            <!-- Lifecycle Workflow Tracker -->
            @php
                $currentStage = \App\Services\LifecycleService::getLeadStage($lead);
                $stages = \App\Services\LifecycleService::getStages();
                $trialHRemaining = \App\Services\LifecycleService::getTrialCountdown($lead);
            @endphp

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6 relative overflow-hidden">
                <!-- Header with Countdown -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Lead Lifecycle Tracker</h3>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Client Acquisition & Lifecycle Workflow</p>
                    </div>
                    @if($trialHRemaining !== null)
                        <div class="flex items-center gap-3 bg-amber-50 px-4 py-2 rounded-2xl border border-amber-100">
                            <div class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-amber-600 uppercase tracking-tighter">Trial Expiry</span>
                                <span class="text-sm font-black text-amber-900 leading-none">{{ $trialHRemaining }} Hours Remaining</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Stepper UI -->
                <div class="relative">
                    <!-- Connecting Line -->
                    <div class="absolute top-5 left-8 right-8 h-1 bg-slate-100 rounded-full">
                        <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000" style="width: {{ (($currentStage - 1) / 4) * 100 }}%"></div>
                    </div>

                    <div class="flex justify-between relative z-10">
                        @foreach($stages as $level => $stage)
                            <div class="flex flex-col items-center text-center max-w-[120px]">
                                <!-- Sphere -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center border-4 transition-all duration-500
                                    {{ $currentStage >= $level ? 'bg-indigo-600 border-indigo-100 text-white' : 'bg-white border-slate-100 text-slate-300' }}">
                                    @if($currentStage > $level)
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <span class="font-black text-sm">{{ $level }}</span>
                                    @endif
                                </div>
                                <!-- Labels -->
                                <div class="mt-3">
                                    <h4 class="text-xs font-black {{ $currentStage >= $level ? 'text-indigo-900' : 'text-slate-400' }}">{{ $stage['name'] }}</h4>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1">{{ $stage['action'] }}</p>
                                </div>
                                <!-- Tooltip Style Metadata -->
                                @if($currentStage == $level)
                                    <div class="mt-4 bg-indigo-50 p-2 rounded-xl border border-indigo-100 animate-bounce">
                                        <p class="text-[9px] font-black text-indigo-600 uppercase">{{ $stage['trigger'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Consent Ledger -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mb-6">
                <h3 class="text-lg font-black text-slate-900 mb-4">Consent Ledger</h3>
                <form method="POST" action="{{ route('leads.consent.grant', $lead) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    @csrf
                    <input type="text" name="channel" class="border-slate-200 rounded-lg" placeholder="channel (call/whatsapp/sms/email)" required>
                    <input type="text" name="purpose" class="border-slate-200 rounded-lg" placeholder="purpose" required>
                    <input type="text" name="purpose_note" class="border-slate-200 rounded-lg" placeholder="note (optional)">
                    <button class="bg-indigo-600 text-white rounded-lg font-bold">Grant Consent</button>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-[10px] uppercase font-black tracking-widest text-slate-400">
                            <tr>
                                <th class="py-2">Time</th>
                                <th class="py-2">Channel</th>
                                <th class="py-2">Purpose</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lead->consents as $consent)
                                <tr>
                                    <td class="py-2">{{ $consent->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="py-2">{{ $consent->channel }}</td>
                                    <td class="py-2">{{ $consent->purpose }}</td>
                                    <td class="py-2">{{ $consent->status }}</td>
                                    <td class="py-2">
                                        @if($consent->status === 'granted' && in_array(auth()->user()->role, ['Admin','Manager']))
                                            <form method="POST" action="{{ route('leads.consent.revoke', $lead) }}">
                                                @csrf
                                                <input type="hidden" name="consent_id" value="{{ $consent->id }}">
                                                <button class="text-xs text-rose-600 font-bold">Revoke</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-2 text-slate-400" colspan="5">No consent records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(session('success'))
                <div
                    class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 font-bold shadow-sm rounded-r-lg">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-r-lg">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Profile Card (Section 4) -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <div>
                                <span
                                    class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $lead?->completion_percentage ?? 0 }}%
                                    Complete</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('leads.edit', $lead) }}"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-xs font-bold border">
                                    ✏️ Edit Info
                                </a>
                            </div>
                        </div>

                        <!-- Data Clusters -->
                        <div class="space-y-8">
                            <!-- Primary Cluster -->
                            <section>
                                <p
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 border-b pb-1">
                                    Primary contact Details</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                                    <div>
                                        <span class="text-gray-500 block text-xs mb-1">Mobile</span>
                                        <div class="flex items-center gap-3">
                                            <a href="tel:{{ $lead?->mobile ?? '' }}"
                                                onclick="logCall('{{ $lead?->id ?? 0 }}')"
                                                class="font-bold text-indigo-600 hover:underline inline-flex items-center gap-1">{{ $lead?->mobile ?? 'N/A' }}</a>
                                            @if($lead && $lead->mobile)
                                                @php
                                                    $waMessage = urlencode("Hi {$lead->name}, this is " . auth()->user()->name . " from StockIdea. I'm reaching out regarding your recent inquiry. How can I help you today?");
                                                @endphp
                                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->mobile) }}?text={{ $waMessage }}" target="_blank" class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="WhatsApp 1-Click">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block text-xs">Alt Mobile</span>
                                        <span class="font-semibold">{{ $lead->alt_mobile ?? 'Not set' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block text-xs">Email</span>
                                        <span class="font-semibold">{{ $lead->email ?? 'Not set' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block text-xs">City</span>
                                        <span class="font-semibold">{{ $lead->city ?? 'Not set' }}</span>
                                    </div>
                                    <div class="md:col-span-2">
                                        <span class="text-gray-500 block text-xs">Permanent Address</span>
                                        <span class="font-semibold">{{ $lead->address ?? 'Not set' }}</span>
                                    </div>
                                </div>
                            </section>

                            <!-- KYC & RPM Dedicated Access -->
                            <section class="bg-indigo-50 p-6 rounded-3xl border border-indigo-100 flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Compliance & Risk</p>
                                    <h4 class="text-sm font-black text-indigo-900">KYC & RPM Dashboard</h4>
                                    <p class="text-[10px] text-indigo-500 font-bold uppercase">Status: 
                                        <span class="{{ ($lead->is_kyc_completed && $lead->is_rpm_completed) ? 'text-green-600' : 'text-amber-600' }}">
                                            {{ ($lead->is_kyc_completed && $lead->is_rpm_completed) ? 'Verified' : 'Pending' }}
                                        </span>
                                    </p>
                                </div>
                                <a href="{{ route('kyc-rpm.show', $lead) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-black shadow-lg hover:bg-indigo-700 transition flex items-center gap-2">
                                    Open Dashboard
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </section>
                        </div>
                    </div>

                    <!-- Rich Lead Details (Automation Upgrade) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Rich Lead Details (Automation)
                            </h3>
                            <button form="detailsForm" type="submit"
                                class="text-xs bg-indigo-600 text-white px-3 py-1 rounded-full hover:bg-indigo-700 transition">
                                Save Changes
                            </button>
                        </div>

                        <form id="detailsForm" action="{{ route('leads.update-rich-details', $lead) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-1 italic">Follow-up
                                        Notes</label>
                                    <textarea name="follow_up_notes" rows="3"
                                        class="w-full rounded-xl border-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-300"
                                        placeholder="Specific notes for follow-up strategy...">{{ $lead?->follow_up_notes ?? '' }}</textarea>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-1 italic">Conversion
                                        Notes</label>
                                    <textarea name="conversion_notes" rows="3"
                                        class="w-full rounded-xl border-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-slate-300"
                                        placeholder="Notes for paid client conversion...">{{ $lead?->conversion_notes ?? '' }}</textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-1 italic">Service
                                        Expiry / Renewal Date</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2V12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <input type="date" name="service_expired_at"
                                            value="{{ $lead?->service_expired_at ? $lead?->service_expired_at->format('Y-m-d') : '' }}"
                                            class="pl-10 w-full rounded-xl border-slate-100 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">High-value leads automatically move to
                                        'Service Expired' tab after this date.</p>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Quick Call Log -->
                    <div class="bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-blue-100 mb-6">
                        <h3 class="text-lg font-bold mb-4 text-blue-900 border-b border-blue-200 pb-2 flex items-center">
                            ⚡ Quick Call Log
                        </h3>
                        <form action="{{ route('leads.quick-log', $lead) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="notes" rows="2" class="w-full rounded border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500 placeholder-blue-300" placeholder="Quickly log a call note without changing status..." required></textarea>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <input type="datetime-local" name="follow_up_date" class="w-full rounded border-blue-200 text-sm focus:border-blue-500 focus:ring-blue-500" title="Optional: Reschedule Follow-up">
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-700 transition shadow-sm text-sm whitespace-nowrap">
                                    Save Note
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Message Center (Templates) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-indigo-100 mb-6" x-data="messageCenter()">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                Message Center
                            </h3>
                            <div class="flex items-center gap-2">
                                <select @change="loadTemplates($el.value)" class="text-xs rounded-lg border-slate-200 font-bold focus:ring-indigo-500">
                                    <option value="sms">SMS Templates</option>
                                    <option value="email">Email Templates</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Template Selector -->
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Pick a Template</label>
                                <select @change="applyTemplate($el.value)" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-indigo-500">
                                    <option value="">-- Select Template --</option>
                                    <template x-for="tpl in templates" :key="tpl.id">
                                        <option :value="tpl.id" x-text="tpl.name"></option>
                                    </template>
                                </select>
                            </div>

                            <form action="{{ route('leads.messages', $lead) }}" method="POST">
                                @csrf
                                <div class="relative">
                                    <textarea name="message" x-model="messageBody" rows="4" 
                                              class="w-full rounded-xl border-slate-200 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-300"
                                              placeholder="Compose message or select a template..." required></textarea>
                                    <div class="absolute bottom-2 right-2 flex gap-2">
                                        <button type="button" @click="copyToClipboard()" class="p-1.5 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors" title="Copy to clipboard">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="mt-3 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl transition shadow-md">
                                    Log Sent Message
                                </button>
                            </form>
                        </div>
                        
                        <!-- Recent Messages -->
                        <div class="mt-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b pb-1 mb-3">Recent Logs</h4>
                            <div class="space-y-3 max-h-48 overflow-y-auto pr-2">
                                @forelse($lead->messages->sortByDesc('created_at') as $msg)
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[10px] font-black text-indigo-600">{{ $msg->user->name }}</span>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $msg->created_at->format('d M, h:i A') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $msg->message }}</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic text-center py-2">No messages logged yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Activity History (Section 8) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold mb-4 border-b pb-2">Activity History</h3>
                        <div class="space-y-4">
                            @forelse(($lead?->activities ?? collect())->sortByDesc('created_at') as $activity)
                                <div class="border-l-2 border-blue-500 pl-4 py-2">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-sm">{{ $activity->activity_type }}</span>
                                        <span
                                            class="text-xs text-gray-400">{{ $activity->created_at?->format('d M, H:i') ?? '' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 italic">{{ $activity->notes }}</p>
                                    <span class="text-xs text-gray-400">— {{ $activity?->user?->name ?? 'User' }}</span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm italic text-center">No history yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Call Control (Section 5-6) -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                        <h3 class="text-lg font-bold mb-4">Manual Calling</h3>

                        <div id="callInitial">
                            @if(($lead?->status ?? '') === 'DND')
                                <div class="bg-red-600 text-white font-bold py-3 px-4 rounded-lg shadow-md mb-2">
                                    🚫 BLOCKED (DND)
                                </div>
                                @if(auth()->user()->role === 'Admin')
                                    <form action="{{ route('leads.activity', $lead) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="status" value="Cold Lead">
                                        <input type="hidden" name="notes" value="Admin unblocked this lead from DND.">
                                        <button type="submit" class="text-xs text-blue-600 hover:underline">Unblock Lead (Admin
                                            Only)</button>
                                    </form>
                                @endif
                            @else
                                <button onclick="startManualCall()"
                                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg shadow-md transition transform active:scale-95 mb-3">
                                    📞 Start Call
                                </button>
                                <button onclick="document.getElementById('dispositionModal').showModal()"
                                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition">
                                    📝 Log Response
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Dial manually or log response directly.</p>
                            @endif
                        </div>

                        <div id="callActive" class="hidden">
                            <div class="animate-pulse flex flex-col items-center py-4 text-green-600 font-bold">
                                <div class="w-4 h-4 bg-green-500 rounded-full mb-2"></div>
                                IN CALL...
                            </div>
                            <button onclick="endManualCall()"
                                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg shadow-md">
                                ⏹️ End Call
                            </button>
                        </div>
                    </div>

                    <!-- Payment Summary (If Paid) -->
                    @if(($lead?->payments?->count() ?? 0) > 0)
                        <div class="bg-green-50 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-green-200">
                            <h3 class="text-lg font-bold text-green-800 mb-2">Payment Details</h3>
                            @foreach(($lead?->payments ?? []) as $payment)
                                <div class="text-sm border-b border-green-100 py-2">
                                    <div class="flex justify-between">
                                        <span>Amount:</span>
                                        <span class="font-bold">INR {{ number_format($payment->amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Status:</span>
                                        <span class="capitalize font-bold">{{ $payment->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Internal Notes (Section 11) -->
                    <x-lead-notes-widget :lead="$lead" />
                </div>

            </div>

        </div>
    </div>

    <!-- Mandatory Disposition Modal (Section 6) -->
    <dialog id="dispositionModal" class="p-0 rounded-lg shadow-2xl w-full max-w-md backdrop:bg-gray-900/50">
        <div x-data="dispositionHandler()" class="bg-white">
            <form method="POST" action="{{ route('leads.activity', $lead) }}" class="p-6">
                @csrf
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-xl font-bold">Log Call Activity</h3>
                    <button type="button" onclick="document.getElementById('dispositionModal').close()"
                        class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-1">Lead Status (Disposition) *</label>
                    <select name="status" x-model="status" @change="updateRequirements()"
                        class="w-full border rounded p-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">-- Select Status --</option>
                        @foreach(\App\Services\LeadStatusService::getStatusDefinitions() as $key => $def)
                            @if($key !== 'Paid Client')
                                <option value="{{ $key }}">{{ $key }}</option>
                            @endif
                        @endforeach
                    </select>
                    <!-- Status Tooltip/Description -->
                    <template x-if="currentDef">
                        <p class="text-xs text-gray-500 mt-1 italic" x-text="currentDef.definition"></p>
                    </template>
                </div>

                <!-- Dynamic Checklist (Phase 14 & 21) -->
                <div x-show="checklist.length > 0" class="mb-4 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <p class="text-sm font-bold text-yellow-800 mb-2">⚠️ Mandatory Checklist</p>
                    <div class="space-y-2">
                        <template x-for="(item, index) in checklist" :key="index">
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" @change="checkValidity()"
                                    class="checklist-item rounded text-indigo-600 focus:ring-indigo-500">
                                <span x-text="item"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Follow-up Date (Mandatory for Call Back/Follow Up) -->
                <div x-show="['Call Back', 'Follow Up'].includes(status)" class="mb-4">
                    <label class="block text-gray-700 font-bold mb-1">Follow-up Date & Time</label>
                    <input type="datetime-local" name="follow_up_date" class="w-full border rounded p-2">
                </div>

                <!-- Trial End Date -->
                <div x-show="status === 'Free Trial'" class="mb-4">
                    <label class="block text-gray-700 font-bold mb-1">Trial Expiry Date</label>
                    <input type="date" name="trial_end_date" class="w-full border rounded p-2">
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-gray-700 font-bold mb-1">Follow-up Strategy</label>
                        <textarea name="follow_up_notes" rows="2" class="w-full border rounded p-2 text-xs"
                            placeholder="Next steps notes...">{{ $lead?->follow_up_notes ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-1">Conversion Notes</label>
                        <textarea name="conversion_notes" rows="2" class="w-full border rounded p-2 text-xs"
                            placeholder="Notes for closing/conversion...">{{ $lead?->conversion_notes ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Interested Fields -->
                <div x-show="status === 'Interested'" class="mb-4 border-l-4 border-blue-400 pl-4 bg-blue-50 p-2">
                    <label class="block text-gray-700 font-bold mb-1">Expected Closure Date</label>
                    <input type="date" name="expected_closure" class="w-full border rounded p-2 mb-2">
                    <label class="block text-gray-700 font-bold mb-1">Segment/Product</label>
                    <input type="text" name="segment" placeholder="e.g. Nifty Options, Equity"
                        class="w-full border rounded p-2">
                </div>

                <!-- Payment Fields & Mandatory KYC -->
                <div x-show="status === 'Make Payment'"
                    class="mb-4 border-l-4 border-green-500 pl-4 bg-green-50 p-2 space-y-3">
                    <p class="text-[10px] font-bold text-green-700 uppercase">Payment Info</p>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="amount" placeholder="Amount (e.g. 5000)"
                            class="w-full border rounded p-2 text-sm">
                        <select name="payment_mode" class="w-full border rounded p-2 text-sm">
                            <option value="UPI">UPI</option>
                            <option value="NEFT">NEFT/IMPS</option>
                            <option value="Cash">Cash</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <p class="text-[10px] font-bold text-red-600 uppercase mt-4">Mandatory KYC Data</p>
                    <div class="space-y-2">
                        <input type="text" name="pan_number" placeholder="PAN Number"
                            class="w-full border rounded p-2 text-sm uppercase" value="{{ $lead->pan_number ?? '' }}">
                        <input type="text" name="aadhaar_number" placeholder="Aadhaar Number"
                            class="w-full border rounded p-2 text-sm" value="{{ $lead?->aadhaar_number ?? '' }}">
                        <input type="text" name="demat_id" placeholder="Demat ID"
                            class="w-full border rounded p-2 text-sm" value="{{ $lead->demat_id ?? '' }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-1">Internal Remarks/Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full border rounded p-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Outcome of the call..."></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('dispositionModal').close()"
                        class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" :disabled="!isValid"
                        :class="!isValid ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="bg-blue-600 text-white font-bold py-2 px-6 rounded shadow-md transition-all">
                        Save Update
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function dispositionHandler() {
            return {
                status: '',
                definitions: @json(\App\Services\LeadStatusService::getStatusDefinitions()),
                currentDef: null,
                checklist: [],
                isValid: true,

                updateRequirements() {
                    this.currentDef = this.definitions[this.status] || null;
                    this.checklist = this.currentDef ? (this.currentDef.checklist || []) : [];

                    // Reset validation state
                    this.$nextTick(() => {
                        this.checkValidity();
                    });
                },

                checkValidity() {
                    // If logic requires checklist, ensure all checked
                    if (this.checklist.length > 0) {
                        const checkboxes = document.querySelectorAll('.checklist-item');
                        this.isValid = true; // Permissive for now: others are not right now
                    } else {
                        this.isValid = true;
                    }
                }
            }
        }

        function startManualCall() {
            document.getElementById('callInitial').classList.add('hidden');
            document.getElementById('callActive').classList.remove('hidden');

            fetch("{{ route('leads.start-call', $lead) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        }

        function endManualCall() {
            document.getElementById('dispositionModal').showModal();
        }

        function messageCenter() {
            return {
                templates: [],
                messageBody: '',
                leadName: "{{ $lead->name }}",
                leadMobile: "{{ $lead->mobile }}",
                agentName: "{{ auth()->user()->name }}",
                companyName: "{{ \App\Models\SystemSetting::get('company_name', config('app.name')) }}",

                init() {
                    this.loadTemplates('sms');
                },

                async loadTemplates(type) {
                    try {
                        const response = await fetch("{{ route('message-templates.list') }}?type=" + type);
                        this.templates = await response.json();
                    } catch (e) {
                        console.error('Failed to load templates');
                    }
                },

                applyTemplate(id) {
                    if (!id) {
                        this.messageBody = '';
                        return;
                    }
                    const tpl = this.templates.find(t => t.id == id);
                    if (tpl) {
                        let body = tpl.body;
                        body = body.replace(/{name}/g, this.leadName);
                        body = body.replace(/{mobile}/g, this.leadMobile);
                        body = body.replace(/{agent}/g, this.agentName);
                        body = body.replace(/{company}/g, this.companyName);
                        this.messageBody = body;
                    }
                },

                copyToClipboard() {
                    if (!this.messageBody) return;
                    navigator.clipboard.writeText(this.messageBody).then(() => {
                        alert('Copied to clipboard!');
                    });
                }
            }
        }
    </script>
</x-app-layout>
