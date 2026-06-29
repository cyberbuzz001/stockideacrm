<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Lead: {{ $lead?->name ?? 'Unknown Lead' }}
            </h2>
            <a href="{{ route('leads.index') }}" class="text-blue-500 hover:underline">??? Back to List</a>
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
            
            <!-- WhatsApp Auto-Proof Compliance Hub -->
            <x-leads.compliance-hub :lead="$lead" />

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
                    ??? {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 font-bold shadow-sm rounded-r-lg">
                    ??? {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Profile Card (Section 4) -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Quick Engagement Hub (Premium Upgrade) -->
                    <div class="glass-card p-8 rounded-[2rem] bg-indigo-600 text-white relative overflow-hidden group mb-6">
                        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h3 class="text-xl font-bold tracking-tight">Quick Engagement</h3>
                                    <p class="text-[10px] text-indigo-200 font-extrabold uppercase tracking-widest mt-1 italic">Single-Click Interaction Audit</p>
                                </div>
                                <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- WhatsApp Log & Link -->
                                <button onclick="handleCommEngagement('whatsapp', '{{ $lead->id }}', 'https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->mobile) }}?text={{ urlencode('Hi ' . $lead->name . ', reaching out from ' . config('app.name') . ' regarding your inquiry.') }}')"
                                        class="flex items-center gap-3 bg-white/10 hover:bg-white text-indigo-900 group/btn transition-all p-4 rounded-3xl border border-white/10 hover:border-transparent group-hover:shadow-2xl">
                                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[9px] font-black uppercase text-indigo-300 group-hover/btn:text-indigo-400">WhatsApp</p>
                                        <p class="text-xs font-black group-hover/btn:text-indigo-950 text-white">Log & Link</p>
                                    </div>
                                </button>

                                <!-- Telegram Log & Link -->
                                <button onclick="handleCommEngagement('telegram', '{{ $lead->id }}', 'https://t.me/+91{{ preg_replace('/[^0-9]/', '', $lead->mobile) }}')"
                                        class="flex items-center gap-3 bg-white/10 hover:bg-white text-indigo-900 group/btn transition-all p-4 rounded-3xl border border-white/10 hover:border-transparent group-hover:shadow-2xl">
                                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20 group-hover/btn:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[9px] font-black uppercase text-indigo-300 group-hover/btn:text-indigo-400">Telegram</p>
                                        <p class="text-xs font-black group-hover/btn:text-indigo-950 text-white">Log & Open</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

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
                                    ?????? Edit Info
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

                            <!-- AI Qualification Bot Simulator -->
                            <section class="bg-gradient-to-br from-indigo-500 to-purple-600 p-6 rounded-3xl border border-indigo-400 flex items-center justify-between text-white shadow-lg mt-6 relative overflow-hidden">
                                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/20 rounded-full blur-2xl"></div>
                                <div class="relative z-10">
                                    <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        AI Features
                                    </p>
                                    <h4 class="text-sm font-black">AI Lead Qualification Bot</h4>
                                    <p class="text-[10px] text-indigo-100 mt-1 max-w-[200px]">Deploy our automated agent to chat with {{ $lead?->name }} and pre-qualify their risk appetite.</p>
                                </div>
                                <button onclick="document.getElementById('aiBotModal').showModal()" class="relative z-10 bg-white text-indigo-600 px-4 py-2 rounded-xl text-xs font-black shadow transition-transform hover:scale-105 flex items-center gap-2">
                                    Deploy Bot ????
                                </button>
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
                            ??? Quick Call Log
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

                    <!-- Tabbed Message & Chat Center -->
                    <div x-data="{ currentTab: 'quick', ...messageCenter() }" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-indigo-100 mb-6">
                        <!-- Tabs -->
                        <div class="flex border-b border-indigo-100 mb-4">
                            <button @click="currentTab = 'quick'" :class="{'border-b-2 border-indigo-600 text-indigo-800 font-bold': currentTab === 'quick', 'text-slate-500 hover:text-indigo-600 font-medium': currentTab !== 'quick'}" class="pb-2 px-4 transition-colors">
                                Quick Action
                            </button>
                            <button @click="currentTab = 'chat'; if(currentTab === 'chat' && typeof fetchChatHistory === 'function') fetchChatHistory();" :class="{'border-b-2 border-indigo-600 text-indigo-800 font-bold': currentTab === 'chat', 'text-slate-500 hover:text-indigo-600 font-medium': currentTab !== 'chat'}" class="pb-2 px-4 transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                Live Chat
                            </button>
                        </div>

                        <!-- Quick Action Content -->
                        <div x-show="currentTab === 'quick'" x-transition>
                            <div class="flex justify-between items-center mb-4 pb-2">
                                <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    Message Center
                                </h3>
                                <div class="flex items-center gap-2">
                                    <select @change="selectedType = $el.value; loadTemplates($el.value)" class="text-xs rounded-lg border-slate-200 font-bold focus:ring-indigo-500">
                                        <option value="sms">SMS Templates</option>
                                        <option value="email">Email Templates</option>
                                        <option value="whatsapp">WhatsApp Templates</option>
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

                            <!-- AI Drafting Input -->
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100/80">
                                <label class="text-[10px] font-black text-indigo-600 uppercase tracking-widest block mb-1">AI Smart Draft ????</label>
                                <div class="flex gap-2">
                                    <input type="text" x-model="aiPrompt" placeholder="Write about... (e.g. ask for demat account details, follow up on callback)" class="flex-1 text-xs border-slate-200 rounded-lg focus:ring-indigo-500">
                                    <button type="button" @click="draftWithAi()" :disabled="aiLoading" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs px-3 py-2 rounded-lg font-bold transition flex items-center gap-1 shadow-sm">
                                        <template x-if="aiLoading">
                                            <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </template>
                                        <span x-text="aiLoading ? 'Drafting...' : 'Draft'"></span>
                                    </button>
                                </div>
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
                                <div class="mt-3 flex flex-col sm:flex-row gap-3">
                                    @if(App\Models\SystemSetting::get('whatsapp_provider', 'none') !== 'none')
                                        <button type="submit" name="send_via_whatsapp" value="1" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-xl transition shadow-md flex justify-center items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Send via CRM WhatsApp
                                        </button>
                                    @endif
                                    <button type="button" x-show="selectedType === 'whatsapp' && messageBody" 
                                            @click="sendWhatsAppAPI()" 
                                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2 rounded-xl transition shadow-xl flex justify-center items-center gap-2 uppercase tracking-widest text-[10px]">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Fire Meta API
                                    </button>
                                    <a :href="'https://wa.me/91' + leadMobile.replace(/[^0-9]/g, '') + '?text=' + encodeURIComponent(messageBody)" target="_blank" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl transition shadow flex justify-center items-center gap-2 border border-slate-200">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                        Send Manually
                                    </a>
                                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl transition shadow-md">
                                        Log Sent Message
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Recent Messages -->
                        <div class="mt-6">
                            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b pb-1 mb-3">Recent Logs</h4>
                            <div class="space-y-3 max-h-48 overflow-y-auto pr-2">
                                @forelse($lead->messages->sortByDesc('created_at') as $msg)
                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                        <div class="flex justify-between items-center mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-black text-indigo-600">{{ $msg->user->name }}</span>
                                                @if($msg->sentiment === 'positive')
                                                    <span class="text-[8px] font-bold bg-green-50 text-green-700 px-1 py-0.2 rounded border border-green-200">???? Positive</span>
                                                @elseif($msg->sentiment === 'negative')
                                                    <span class="text-[8px] font-bold bg-rose-50 text-rose-700 px-1 py-0.2 rounded border border-rose-200">???? Negative</span>
                                                @elseif($msg->sentiment === 'neutral')
                                                    <span class="text-[8px] font-bold bg-slate-50 text-slate-600 px-1 py-0.2 rounded border border-slate-200">???? Neutral</span>
                                                @endif
                                                
                                                @if($msg->urgency === 'high')
                                                    <span class="text-[8px] font-bold bg-amber-50 text-amber-700 px-1 py-0.2 rounded border border-amber-200 animate-pulse">?????? High</span>
                                                @endif
                                            </div>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $msg->created_at->format('d M, h:i A') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $msg->message }}</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 italic text-center py-2">No messages logged yet.</p>
                                @endforelse
                            </div>
                        </div>
                        
                        <!-- Live Chat Content -->
                        <div x-show="currentTab === 'chat'" x-cloak x-transition>
                            <div class="flex flex-col h-[500px] border border-slate-200 rounded-xl overflow-hidden bg-[#e5ddd5] relative shadow-inner">
                                <!-- Chat Header -->
                                <div class="bg-[#075e54] text-white px-4 py-3 flex items-center justify-between z-10 shadow-md">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center font-bold text-lg">
                                            {{ substr($lead->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold leading-tight">{{ $lead->name }}</h4>
                                            <p class="text-xs text-white/70">{{ $lead->mobile }}</p>
                                        </div>
                                    </div>
                                    <button @click="fetchChatHistory()" class="p-2 hover:bg-white/10 rounded-full transition-colors" title="Refresh">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    </button>
                                </div>

                                <!-- Chat Messages Area -->
                                <div id="chatMessagesArea" class="flex-1 p-4 overflow-y-auto space-y-3 relative z-0" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');">
                                    <div x-show="chatLoading" class="flex justify-center p-4">
                                        <span class="bg-white/80 px-4 py-1.5 rounded-full text-xs font-bold text-slate-500 shadow-sm">Loading messages...</span>
                                    </div>
                                    <div x-show="!chatLoading && chatMessages.length === 0" class="flex justify-center p-4">
                                        <span class="bg-white/80 px-4 py-1.5 rounded-full text-xs font-bold text-slate-500 shadow-sm">No messages yet.</span>
                                    </div>
                                    
                                    <template x-for="msg in chatMessages" :key="msg.id">
                                        <div class="flex w-full" :class="msg.direction === 'outbound' ? 'justify-end' : 'justify-start'">
                                            <div :class="msg.direction === 'outbound' ? 'bg-[#dcf8c6] rounded-tl-lg rounded-bl-lg rounded-br-lg' : 'bg-white rounded-tr-lg rounded-br-lg rounded-bl-lg'" class="max-w-[85%] px-3 py-2 shadow-sm relative group">
                                                <!-- Tail SVG -->
                                                <svg x-show="msg.direction === 'outbound'" class="absolute top-0 -right-2 w-2 h-3 text-[#dcf8c6]" fill="currentColor" viewBox="0 0 8 13"><path d="M0 0v13L8 0H0z"/></svg>
                                                <svg x-show="msg.direction === 'inbound'" class="absolute top-0 -left-2 w-2 h-3 text-white" fill="currentColor" viewBox="0 0 8 13"><path d="M8 0v13L0 0h8z"/></svg>
                                                
                                                <p class="text-sm text-slate-800 break-words" x-text="msg.content"></p>
                                                
                                                <div class="flex justify-end items-center gap-1 mt-1">
                                                    <span class="text-[10px] text-slate-400" x-text="msg.created_at"></span>
                                                    <template x-if="msg.direction === 'outbound'">
                                                        <span>
                                                            <svg x-show="msg.status === 'sent'" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                            <svg x-show="msg.status === 'delivered'" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7" /></svg>
                                                            <svg x-show="msg.status === 'read'" class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7M5 13l4 4L19 7" /></svg>
                                                            <svg x-show="msg.status === 'failed'" class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Chat Input Area -->
                                <div class="bg-[#f0f0f0] p-3 flex flex-col gap-2 z-10">
                                    <div class="flex justify-start gap-2 relative" x-data="{ showMetaDropdown: false, metaTemplates: [] }" 
                                         x-init="fetch('{{ route('message-templates.list') }}?type=whatsapp').then(r => r.json()).then(d => metaTemplates = d)">
                                        <button @click="showMetaDropdown = !showMetaDropdown" class="text-xs font-bold text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-[#00a884]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            Send Meta Template (Open 24h Window)
                                        </button>
                                        
                                        <!-- Dropdown -->
                                        <div x-show="showMetaDropdown" @click.away="showMetaDropdown = false" style="display: none;" class="absolute bottom-full mb-2 left-0 w-64 bg-white rounded-xl shadow-lg border border-slate-200 z-50 overflow-hidden">
                                            <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 font-bold text-xs text-slate-500">Select Template</div>
                                            <div class="max-h-48 overflow-y-auto">
                                                <template x-if="metaTemplates.length === 0">
                                                    <div class="p-3 text-xs text-slate-500 text-center">No templates synced. Go to Control Center to sync.</div>
                                                </template>
                                                <template x-for="tpl in metaTemplates" :key="tpl.id">
                                                    <button @click="sendMetaTemplate(tpl.name, tpl.language); showMetaDropdown = false" class="w-full text-left px-3 py-2 text-sm hover:bg-[#f0f0f0] border-b border-slate-50 transition-colors last:border-0">
                                                        <div class="font-bold text-slate-700" x-text="tpl.name"></div>
                                                        <div class="text-[10px] text-slate-500 truncate" x-text="tpl.body"></div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
                                            <textarea x-model="chatInput" @keydown.enter.prevent="sendChatMessage()" rows="1" class="w-full border-0 focus:ring-0 resize-none py-3 px-4 text-sm max-h-32" placeholder="Type a message..."></textarea>
                                        </div>
                                        <button @click="sendChatMessage()" :disabled="!chatInput.trim() || chatSending" class="bg-[#00a884] hover:bg-[#008f6f] disabled:opacity-50 disabled:cursor-not-allowed text-white p-3 rounded-full shadow-sm transition-colors flex-shrink-0">
                                            <template x-if="chatSending">
                                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </template>
                                            <template x-if="!chatSending">
                                                <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                                            </template>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unified Activity Timeline -->
                    @php
                        $timeline = collect()
                            ->merge(
                                ($lead->activities ?? collect())->map(fn($item) => [
                                    'type' => 'activity',
                                    'title' => $item->activity_type,
                                    'notes' => $item->notes,
                                    'user' => $item->user->name ?? 'System',
                                    'date' => $item->created_at,
                                    'icon' => match(strtolower($item->activity_type)) {
                                        'call started' => '????',
                                        'duplicate attempt' => '??????',
                                        'lead assigned' => '????',
                                        'status updated', 'call outcome' => '????',
                                        default => '??????'
                                    },
                                ])
                            )
                            ->merge(
                                ($lead->messages ?? collect())->map(fn($item) => [
                                    'type' => 'message',
                                    'title' => 'Sent Message',
                                    'notes' => $item->message,
                                    'user' => $item->user->name ?? 'User',
                                    'date' => $item->created_at,
                                    'icon' => '??????',
                                    'sentiment' => $item->sentiment,
                                    'urgency' => $item->urgency
                                ])
                            )
                            ->merge(
                                ($lead->payments ?? collect())->map(fn($item) => [
                                    'type' => 'payment',
                                    'title' => "Payment: " . ($item->status ?? 'Pending'),
                                    'notes' => "Amount: INR " . number_format((float) $item->amount) . " | Mode: " . ($item->payment_mode ?? 'N/A') . " - " . ($item->remarks ?? ''),
                                    'user' => $item->user->name ?? 'User',
                                    'date' => $item->payment_date ?? $item->created_at,
                                    'icon' => '????',
                                ])
                            )
                            ->sortByDesc('date');
                    @endphp

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100">
                        <div class="border-b pb-3 mb-6">
                            <h3 class="text-lg font-bold text-slate-800">Unified Activity Timeline</h3>
                            <p class="text-xs text-slate-500">Chronological history of all communications, calls, and payments.</p>
                        </div>
                        
                        <div class="relative border-l-2 border-slate-100 ml-4 pl-6 space-y-6">
                            @forelse($timeline as $event)
                                <div class="relative">
                                    <!-- Icon sphere -->
                                    <span class="absolute -left-[37px] top-0.5 bg-white border-2 border-slate-100 w-8 h-8 rounded-full flex items-center justify-center text-sm shadow-sm z-10">
                                        {{ $event['icon'] }}
                                    </span>
                                    
                                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100/80 hover:border-indigo-100 transition shadow-sm">
                                        <div class="flex justify-between items-start flex-wrap gap-2 mb-1.5">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-bold text-sm text-slate-900">{{ $event['title'] }}</h4>
                                                @if($event['type'] === 'message' && !empty($event['sentiment']))
                                                    @if($event['sentiment'] === 'positive')
                                                        <span class="text-[9px] font-bold bg-green-50 text-green-700 px-1.5 py-0.5 rounded border border-green-200">???? Positive</span>
                                                    @elseif($event['sentiment'] === 'negative')
                                                        <span class="text-[9px] font-bold bg-rose-50 text-rose-700 px-1.5 py-0.5 rounded border border-rose-200">???? Negative</span>
                                                    @elseif($event['sentiment'] === 'neutral')
                                                        <span class="text-[9px] font-bold bg-slate-50 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200">???? Neutral</span>
                                                    @endif
                                                @endif
                                                
                                                @if($event['type'] === 'message' && ($event['urgency'] ?? '') === 'high')
                                                    <span class="text-[9px] font-bold bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200 animate-pulse">?????? High Urgency</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $event['date']?->format('d M Y, h:i A') ?? '' }}</span>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed font-medium italic mb-2">"{{ $event['notes'] }}"</p>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-400 font-bold">
                                            <span>???? By: {{ $event['user'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-400 text-sm italic text-center py-4">No events or activities logged yet.</p>
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
                                    ???? BLOCKED (DND)
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
                                    ???? Start Call
                                </button>
                                <button onclick="document.getElementById('dispositionModal').showModal()"
                                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition">
                                    ???? Log Response
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
                                ?????? End Call
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

                    <!-- Assign Lead to Agent (Admin / SBA Only) -->
                    @if(in_array(auth()->user()->role, ['Admin', 'SBA']))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-indigo-100">
                        <h3 class="text-base font-black text-slate-800 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Assign Lead to Agent
                        </h3>
                        @if($lead->assignee)
                            <p class="text-xs text-slate-500 mb-3 font-semibold">
                                Currently assigned to: <span class="text-indigo-700 font-black">{{ $lead->assignee->name }}</span>
                            </p>
                        @else
                            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3 font-bold">
                                ⚠️ Unassigned — no agent yet.
                            </p>
                        @endif
                        <form action="{{ route('leads.assign') }}" method="POST">
                            @csrf
                            <input type="hidden" name="lead_ids[]" value="{{ $lead->id }}">
                            <div class="mb-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Select Agent</label>
                                <select name="agent_id" required
                                    class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Choose Agent --</option>
                                    @foreach(\App\Models\User::where('role', '!=', 'Admin')->orderBy('name')->get() as $agent)
                                        <option value="{{ $agent->id }}" {{ $lead->assigned_to == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }} ({{ $agent->role }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-2.5 rounded-xl transition shadow-md text-sm flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Assign Now
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

            <!-- The Closer's Playbook (Battle Cards Sidebar) -->
            <div x-data="{ open: false }" class="fixed right-0 top-1/4 z-50 flex items-start transition-transform duration-300 translate-x-full" :class="open ? 'translate-x-0' : 'translate-x-[calc(100%-40px)]'">
                <!-- Toggle Tab -->
                <button @click="open = !open" 
                        class="bg-indigo-600 text-white p-3 rounded-l-2xl shadow-2xl flex flex-col items-center gap-2 hover:bg-indigo-700 transition-all border-y border-l border-indigo-400">
                    <span class="[writing-mode:vertical-lr] font-black text-[10px] uppercase tracking-[0.2em]">The Closer's Playbook</span>
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Sidebar Content -->
                <div class="bg-white/95 backdrop-blur-xl w-80 h-[60vh] shadow-2xl border-l border-indigo-100 overflow-hidden flex flex-col rounded-bl-3xl">
                    <div class="p-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <h4 class="font-black text-sm uppercase tracking-widest">Battle Cards</h4>
                        <p class="text-[9px] font-bold opacity-80 uppercase mt-1 italic">Click an objection to see the rebuttal</p>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-4 space-y-3" x-data="{ activeScript: null }">
                        @foreach($objectionScripts as $script)
                            <div class="group">
                                <button @click="activeScript = (activeScript === '{{ $script->tag }}' ? null : '{{ $script->tag }}')" 
                                        class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-indigo-50 hover:border-indigo-200 transition-all text-left">
                                    <span class="text-xs font-black text-slate-700 group-hover:text-indigo-700">{{ $script->title }}</span>
                                    <svg class="w-3 h-3 text-slate-400 group-hover:text-indigo-400 transition-transform" :class="activeScript === '{{ $script->tag }}' ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="activeScript === '{{ $script->tag }}'" x-collapse class="mt-2 text-[11px] leading-relaxed text-indigo-900 bg-indigo-50/50 p-3 rounded-xl border border-indigo-100/50 italic font-medium">
                                    {{ $script->script }}
                                    <button @click="navigator.clipboard.writeText('{{ addslashes($script->script) }}'); alert('Script copied!')" 
                                            class="mt-2 flex items-center gap-1 text-[9px] font-black uppercase text-indigo-600 hover:text-indigo-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        Copy Script
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-3 bg-slate-50 border-t border-slate-100">
                        <p class="text-[9px] text-center font-black text-slate-400 uppercase tracking-tighter">Powered by Shreesvarn Sales Engine</p>
                    </div>
                </div>
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
                    <p class="text-sm font-bold text-yellow-800 mb-2">?????? Mandatory Checklist</p>
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

                    <!-- Split Payment Toggle -->
                    @if(in_array(auth()->user()->role, ['Admin', 'Manager', 'SBA', 'TL', 'BA']))
                    <div class="mb-2">
                        <label class="flex items-center space-x-2 text-sm text-green-800 font-bold cursor-pointer">
                            <input type="checkbox" name="is_split_payment" x-model="isSplitPayment" class="rounded text-green-600 focus:ring-green-500">
                            <span>Split Payment (e.g. between SBA/TL and BA)?</span>
                        </label>
                    </div>
                    @endif

                    <!-- Single Payment Mode (Default) -->
                    <div x-show="!isSplitPayment" class="grid grid-cols-2 gap-2">
                        <input type="number" name="amount" placeholder="Amount (e.g. 5000)"
                            class="w-full border rounded p-2 text-sm">
                        <select name="payment_mode" class="w-full border rounded p-2 text-sm">
                            <option value="UPI">UPI</option>
                            <option value="NEFT">NEFT/IMPS</option>
                            <option value="Cash">Cash</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Split Payment Mode -->
                    <div x-show="isSplitPayment" x-data="{ totalAmount: 0 }" class="space-y-3 p-3 bg-white border border-green-200 rounded-lg shadow-sm">
                        <div class="mb-3 pb-2 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex-1 mr-4">
                                <label class="text-[9px] font-black uppercase text-slate-400 block mb-1">Total to Split</label>
                                <input type="number" x-model="totalAmount" placeholder="Total Amount" class="w-full border rounded p-1.5 text-xs font-bold">
                            </div>
                            <button type="button" @click="$el.closest('form').split_1_amount.value = (totalAmount/2).toFixed(2); $el.closest('form').split_2_amount.value = (totalAmount/2).toFixed(2);" 
                                    class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-100 transition-all">
                                ⚖️ Split 50/50
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-black uppercase text-indigo-600 mb-1 block">Split 1</label>
                                <select name="split_1_user_id" class="w-full border rounded p-1.5 text-xs text-gray-700 mb-1">
                                    @if($lead->assignee)
                                        <option value="{{ $lead->assignee->id }}">Assigned BA: {{ $lead->assignee->name }}</option>
                                    @endif
                                    <option value="{{ auth()->user()->id }}" {{ (!$lead->assignee || $lead->assignee->id === auth()->user()->id) ? 'selected' : '' }}>Myself: {{ auth()->user()->name }}</option>
                                </select>
                                <input type="number" name="split_1_amount" placeholder="Amount 1 (e.g. 13900)" class="w-full border rounded p-1.5 text-xs font-bold text-indigo-700 placeholder-indigo-200">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-rose-600 mb-1 block">Split 2</label>
                                <select name="split_2_user_id" class="w-full border rounded p-1.5 text-xs text-gray-700 mb-1">
                                    <option value="{{ auth()->user()->id }}" selected>Myself: {{ auth()->user()->name }}</option>
                                    @if($lead->assignee && $lead->assignee->id !== auth()->user()->id)
                                        <option value="{{ $lead->assignee->id }}">Assigned BA: {{ $lead->assignee->name }}</option>
                                    @endif
                                </select>
                                <input type="number" name="split_2_amount" placeholder="Amount 2 (e.g. 13900)" class="w-full border rounded p-1.5 text-xs font-bold text-rose-700 placeholder-rose-200">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-500 whitespace-nowrap">Source:</label>
                            <select name="split_payment_mode" class="w-full border-none bg-gray-50 rounded p-1.5 text-xs focus:ring-0">
                                <option value="UPI">UPI</option>
                                <option value="NEFT">NEFT/IMPS</option>
                                <option value="Cash">Cash</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
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

    <!-- AI Qualification Bot Modal -->
    <dialog id="aiBotModal" class="p-0 rounded-2xl shadow-2xl backdrop:bg-slate-900/50 w-full max-w-2xl border-0 overflow-hidden" x-data="aiBotSimulator()">
        <div class="h-[500px] flex flex-col bg-slate-50 relative">
            <!-- Modal Header -->
            <div class="p-4 bg-indigo-600 text-white flex justify-between items-center shadow-md z-10 relative">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-xl shadow-inner border border-indigo-400">????</div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-indigo-600 rounded-full animate-pulse"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-none">{{ $company_name }} AI</h3>
                        <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mt-1">Pre-Qualification Sequence</p>
                    </div>
                </div>
                <button onclick="document.getElementById('aiBotModal').close()" class="text-indigo-200 hover:text-white transition bg-indigo-700/50 p-2 rounded-xl">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Chat History -->
            <div class="flex-1 p-6 overflow-y-auto w-full flex flex-col gap-4 font-sans" id="aiChatBox">
                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.role === 'bot' ? 'items-start' : 'items-end'" class="flex flex-col w-full animate-fade-in-up">
                        <div class="flex gap-2 max-w-[85%]" :class="msg.role === 'bot' ? 'flex-row' : 'flex-row-reverse justify-end'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1 shadow-sm text-sm" 
                                 :class="msg.role === 'bot' ? 'bg-indigo-600 text-white' : 'bg-emerald-500 text-white'">
                                <span x-text="msg.role === 'bot' ? '????' : '????'"></span>
                            </div>
                            <div class="p-3 rounded-2xl text-sm shadow-sm relative group" 
                                 :class="msg.role === 'bot' ? 'bg-white border border-slate-100 text-slate-700 rounded-tl-none' : 'bg-indigo-600 text-white rounded-tr-none'">
                                <p x-text="msg.content" class="leading-relaxed"></p>
                                <span class="text-[9px] opacity-50 mt-1 block absolute -bottom-4" 
                                      :class="msg.role === 'bot' ? 'text-slate-400 left-1' : 'text-slate-500 right-1'" 
                                      x-text="msg.time"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="isTyping" class="flex gap-2 items-start animate-fade-in">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 mt-1 shadow-sm text-sm">????</div>
                    <div class="bg-white p-3 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm flex items-center gap-1 min-w-[60px] h-[44px]">
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>

            <!-- Action Area / Mock Trigger -->
            <div class="p-4 bg-white border-t border-slate-200 shadow-sm z-10 relative">
                <div x-show="!sequenceStarted" class="flex justify-center w-full">
                    <button @click="startSequence()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-black text-sm shadow-lg transition-transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                        Start Qualification Sequence for {{ explode(' ', $lead?->name)[0] ?? 'Lead' }}
                    </button>
                </div>
                <div x-show="sequenceStarted && !qualificationComplete" class="text-center w-full">
                    <p class="text-xs font-bold text-slate-400 animate-pulse">Running Autonomous Assessment...</p>
                </div>
                <div x-show="qualificationComplete" class="flex items-center justify-between w-full bg-emerald-50 border border-emerald-100 p-3 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-emerald-500 text-white rounded-lg flex items-center justify-center font-bold">???</div>
                        <div>
                            <p class="text-xs font-black text-emerald-800 uppercase tracking-widest">Assessment Complete</p>
                            <p class="text-sm font-bold text-emerald-600">Lead Qualified: Aggressive Equity</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('detailsForm').submit()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition">Apply Tags to Profile</button>
                </div>
            </div>
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
                isSplitPayment: false,
                splitTotalAmount: '',

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
            const leadId = '{{ $lead?->id ?? 0 }}';
            fetch(`/leads/${leadId}/start-call`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('callInitial').classList.add('hidden');
                    document.getElementById('callActive').classList.remove('hidden');
                    
                    // Simple simulated ringing
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
                    audio.play().catch(e => console.log('Audio disabled'));
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
                aiPrompt: '',
                aiLoading: false,
                selectedType: 'sms',

                // Live Chat Variables
                chatMessages: [],
                chatInput: '',
                chatLoading: false,
                chatSending: false,
                chatPollingInterval: null,

                init() {
                    this.loadTemplates('sms');
                    // We only start polling if they switch to the chat tab
                    this.$watch('currentTab', value => {
                        if (value === 'chat') {
                            this.fetchChatHistory();
                            if (!this.chatPollingInterval) {
                                this.chatPollingInterval = setInterval(() => this.fetchChatHistory(true), 5000);
                            }
                        } else {
                            if (this.chatPollingInterval) {
                                clearInterval(this.chatPollingInterval);
                                this.chatPollingInterval = null;
                            }
                        }
                    });
                },

                async fetchChatHistory(silent = false) {
                    if (!silent) this.chatLoading = true;
                    try {
                        const response = await fetch("{{ route('leads.chat-history', $lead) }}");
                        const data = await response.json();
                        this.chatMessages = data.messages || [];
                        this.$nextTick(() => {
                            const container = document.getElementById('chatMessagesArea');
                            if (container) container.scrollTop = container.scrollHeight;
                        });
                    } catch (e) {
                        console.error('Failed to load chat history', e);
                    } finally {
                        if (!silent) this.chatLoading = false;
                    }
                },

                async sendChatMessage() {
                    const msg = this.chatInput.trim();
                    if (!msg || this.chatSending) return;
                    
                    this.chatSending = true;
                    try {
                        const response = await fetch("{{ route('leads.chat-send', $lead) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ message: msg })
                        });
                        const data = await response.json();
                        if (data.success && data.log) {
                            this.chatMessages.push(data.log);
                            this.chatInput = '';
                            this.$nextTick(() => {
                                const container = document.getElementById('chatMessagesArea');
                                if (container) container.scrollTop = container.scrollHeight;
                            });
                        } else {
                            alert(data.message || 'Failed to send message');
                        }
                    } catch (e) {
                        alert('Failed to send message: ' + e.message);
                    } finally {
                        this.chatSending = false;
                    }
                },

                async sendMetaTemplate(templateName, languageCode = 'en_US') {
                    if (!templateName || !templateName.trim()) return;

                    this.chatSending = true;
                    try {
                        const response = await fetch("{{ route('leads.chat-send-template', $lead) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                template_name: templateName.trim(),
                                language_code: languageCode
                            })
                        });
                        const data = await response.json();
                        if (data.success && data.log) {
                            this.chatMessages.push(data.log);
                            this.$nextTick(() => {
                                const container = document.getElementById('chatMessagesArea');
                                if (container) container.scrollTop = container.scrollHeight;
                            });
                            alert("Template sent successfully! The 24-hour window will open once they reply.");
                        } else {
                            alert(data.message || 'Failed to send template');
                        }
                    } catch (e) {
                        alert('Failed to send template: ' + e.message);
                    } finally {
                        this.chatSending = false;
                    }
                },

                async loadTemplates(type) {
                    this.selectedType = type;
                    try {
                        const url = "{{ route('message-templates.list') }}?type=" + type;
                        const response = await fetch(url);
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

                async sendWhatsAppAPI() {
                    const tplId = document.querySelector('select[@change="applyTemplate($el.value)"]').value;
                    if (!tplId) return alert('Please select a template first');
                    
                    if (!confirm('Fire WhatsApp API for this template?')) return;

                    try {
                        const response = await fetch("{{ route('leads.send-whatsapp', $lead) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            },
                            body: JSON.stringify({ template_id: tplId })
                        });
                        const res = await response.json();
                        if (res.success) {
                            alert('WhatsApp API triggered successfully!');
                            window.location.reload(); // Refresh to see activity
                        } else {
                            alert('API Error: ' + res.message);
                        }
                    } catch (e) {
                        alert('Failed to send WhatsApp via API');
                    }
                },

                async draftWithAi() {
                    if (!this.aiPrompt.trim()) {
                        alert('Please describe what you want the AI to write.');
                        return;
                    }
                    this.aiLoading = true;
                    try {
                        const response = await fetch("{{ route('leads.ai-draft', $lead) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                prompt: this.aiPrompt,
                                mode: this.selectedType
                            })
                        });
                        const data = await response.json();
                        if (data.draft) {
                            this.messageBody = data.draft;
                            this.aiPrompt = '';
                        } else if (data.error) {
                            alert(data.error);
                        }
                    } catch (e) {
                        alert('Failed to draft message: ' + e.message);
                    } finally {
                        this.aiLoading = false;
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

        function aiBotSimulator() {
            return {
                baseMessages: [
                    { id: 1, role: 'bot', content: 'Initiating connection sequence...', time: 'Just now' },
                    { id: 2, role: 'bot', content: 'Connection established via WhatsApp API.', time: 'Just now' }
                ],
                messages: [],
                isTyping: false,
                sequenceStarted: false,
                qualificationComplete: false,
                leadName: "{{ explode(' ', $lead?->name)[0] ?? 'there' }}",
                
                init() {
                    this.messages = [...this.baseMessages];
                },
                
                async startSequence() {
                    this.sequenceStarted = true;
                    this.isTyping = true;
                    
                    await this.delay(1500);
                    this.addBotMsg(`Hi ${this.leadName}! I'm Sarah, an AI Assistant from ${this.companyName}. I see you recently showed interest in our services.`);
                    
                    await this.delay(2000);
                    this.addUserMsg(`Yes, I want to know about your accurate stock picks.`);
                    
                    await this.delay(1800);
                    this.addBotMsg("Great! To ensure we recommend the best advisory plan, could you confirm your typical investment capital size?");
                    
                    await this.delay(2500);
                    this.addUserMsg("Around 5-10 lakhs right now.");
                    
                    await this.delay(1500);
                    this.addBotMsg("Got it. And what's your primary goal? Safe long-term growth or aggressive short-term trading?");
                    
                    await this.delay(2000);
                    this.addUserMsg("I want high returns, mostly options and intraday.");
                    
                    await this.delay(2500);
                    this.addBotMsg("Understood. Based on a capital of 5-10L and an aggressive risk appetite for Options, you qualify for our Premium F&O Advisory package.");
                    
                    await this.delay(1000);
                    this.isTyping = false;
                    this.qualificationComplete = true;
                },
                
                addBotMsg(text) {
                    this.messages.push({ id: Date.now(), role: 'bot', content: text, time: 'Now' });
                    this.scrollToBottom();
                },
                
                addUserMsg(text) {
                    this.messages.push({ id: Date.now(), role: 'user', content: text, time: 'Now' });
                    this.scrollToBottom();
                },
                
                scrollToBottom() {
                    setTimeout(() => {
                        const box = document.getElementById('aiChatBox');
                        if (box) box.scrollTop = box.scrollHeight;
                    }, 50);
                },
                
                delay(ms) {
                    return new Promise(res => setTimeout(res, ms));
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Auto-trigger AI Analysis Sidebar
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('open-ai-sidebar', { 
                    detail: { leadId: '{{ $lead->id }}' } 
                }));
            }, 1000);
        });
    </script>
</x-app-layout>

