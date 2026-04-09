@props(['lead'])

@php
    $progress = $lead->getComplianceProgress();
@endphp

<div x-data="{ 
    sending: false, 
    showEditor: false, 
    currentTemplate: '', 
    editingBody: '',
    
    openEditor(name, body) {
        this.currentTemplate = name;
        this.editingBody = body;
        this.showEditor = true;
    },

    sendTemplate() {
        this.sending = true;
        fetch('{{ route('leads.whatsapp.send', $lead) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                template_name: this.currentTemplate,
                custom_body: this.editingBody
            })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                alert('WhatsApp Sent Successfully!');
                this.showEditor = false;
            } else {
                alert('Error: ' + data.message);
            }
            this.sending = false;
        })
        .catch(() => {
            alert('Request Failed');
            this.sending = false;
        });
    }
}" class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute -right-20 -top-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
    
    <div class="relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight uppercase">WhatsApp Auto-Proof Hub</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Dispute Safety & Verification Pipeline</p>
            </div>
            
            <!-- Safety Meter -->
            <div class="flex items-center gap-6 bg-slate-50 p-4 rounded-2xl border border-slate-100 min-w-[200px]">
                <div class="relative w-12 h-12">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="text-slate-200" />
                        <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4" fill="transparent" class="{{ $progress['is_safe'] ? 'text-emerald-500' : 'text-indigo-600' }}"
                            stroke-dasharray="125.6"
                            stroke-dashoffset="{{ 125.6 * (1 - $progress['percentage']/100) }}"
                            stroke-linecap="round" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-[10px] font-black">{{ $progress['percentage'] }}%</span>
                    </div>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Safety Status</p>
                    <p class="text-xs font-black {{ $progress['is_safe'] ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $progress['is_safe'] ? '✓ SECURE' : '⚠️ AT RISK' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Progress Steps Pipeline -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            @foreach($progress['steps'] as $step)
                <div class="p-3 rounded-2xl border {{ $step['is_completed'] ? 'bg-emerald-50 border-emerald-100 text-emerald-900' : 'bg-slate-50 border-slate-100 text-slate-400' }} transition-all">
                    <div class="flex items-center gap-2 mb-1">
                        @if($step['is_completed'])
                            <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @endif
                        <span class="text-[9px] font-black uppercase tracking-tighter">{{ $step['label'] }}</span>
                    </div>
                    <p class="text-[8px] font-bold">{{ $step['is_completed'] ? 'Verified' : 'Pending' }}</p>
                </div>
            @endforeach
        </div>

        <!-- Rapid Actions -->
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Send System Templates</p>
            <div class="flex flex-wrap gap-2">
                @php
                    $tmpl1 = \App\Models\MessageTemplate::where('name', 'WP_PAYMENT_CONF_AUTO')->first();
                    $tpBody1 = str_replace(
                        ['{{amount}}', '{{package}}', '{{days}}'], 
                        [$lead->expected_payment ?? '0', 'Standard', '30'], 
                        $tmpl1->body ?? ''
                    );
                @endphp
                <button @click="openEditor('WP_PAYMENT_CONF_AUTO', '{{ addslashes($tpBody1) }}')" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition active:scale-95">Step 1: Payment</button>
                
                <button @click="openEditor('WP_SERVICE_ACT_AUTO', '{{ addslashes(str_replace('{{package}}', 'Standard', \App\Models\MessageTemplate::where('name', 'WP_SERVICE_ACT_AUTO')->first()->body ?? '')) }}')" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition active:scale-95">Step 2: Activate</button>
                
                <button @click="openEditor('WP_TERMS_ACCEPT_AUTO', '{{ addslashes(\App\Models\MessageTemplate::where('name', 'WP_TERMS_ACCEPT_AUTO')->first()->body ?? '') }}')" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition active:scale-95">Step 3: Terms</button>
                
                <button @click="openEditor('WP_USAGE_PROOF_AUTO', '{{ addslashes(\App\Models\MessageTemplate::where('name', 'WP_USAGE_PROOF_AUTO')->first()->body ?? '') }}')" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition active:scale-95">Step 5: Proof</button>
            </div>
        </div>
    </div>

    <!-- Template Editor Modal (Real-time Editing) -->
    <div x-show="showEditor" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[2rem] w-full max-w-lg overflow-hidden shadow-2xl animate-fade-in-up">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h4 class="text-xl font-black text-slate-800 uppercase tracking-tighter">Edit Message</h4>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest" x-text="currentTemplate"></p>
                </div>
                <button @click="showEditor = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-8">
                <textarea x-model="editingBody" rows="6" class="w-full rounded-2xl border-slate-200 text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500 p-4 transition-all"></textarea>
                <div class="mt-8 flex justify-end gap-3">
                    <button @click="showEditor = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600">Cancel</button>
                    <button @click="sendTemplate()" :disabled="sending" class="px-8 py-3 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest hover:bg-indigo-700 transition active:scale-95 flex items-center gap-2">
                        <template x-if="sending">
                            <svg class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="sending ? 'Sending...' : 'Send WhatsApp Now'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
