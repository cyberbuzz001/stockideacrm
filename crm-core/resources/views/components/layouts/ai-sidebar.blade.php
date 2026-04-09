<div x-data="{ 
        open: false, 
        activeTab: 'summary',
        summary: 'Initializing intelligence...',
        advice: '',
        loading: false,
        leadId: null,

        initAI(leadId) {
            this.leadId = leadId;
            this.fetchSummary();
        },

        fetchSummary() {
            if (!this.leadId) return;
            this.loading = true;
            fetch(`/ai/summarize/${this.leadId}`)
                .then(r => r.json())
                .then(data => {
                    this.summary = data.summary || 'Unable to generate summary.';
                    this.loading = false;
                })
                .catch(() => {
                    this.summary = 'Neural connection failed.';
                    this.loading = false;
                });
        },

        getAdvice(objection) {
            this.loading = true;
            fetch(`/ai/get-advice/${this.leadId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ objection: objection })
            })
            .then(r => r.json())
            .then(data => {
                this.advice = data.advice;
                this.loading = false;
                this.activeTab = 'advice';
            });
        }
    }"
    @open-ai-sidebar.window="open = true; initAI($event.detail.leadId)"
    @close-ai-sidebar.window="open = false"
    style="display: none;"
    x-show="open"
    class="fixed inset-y-0 right-0 w-96 z-[100] bg-white/70 backdrop-blur-3xl border-l border-white/50 shadow-2xl overflow-hidden flex flex-col transition-all duration-500"
    x-transition:enter="transform transition ease-in-out duration-500"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transform transition ease-in-out duration-500"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full">

    <!-- Header -->
    <div class="p-6 border-b border-white/40 flex items-center justify-between bg-white/40">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Neural Sales AI</h3>
                <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Powered by Gemini</p>
            </div>
        </div>
        <button @click="open = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-white/40 bg-white/20">
        <button @click="activeTab = 'summary'" 
                :class="activeTab === 'summary' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400'"
                class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all">Summary</button>
        <button @click="activeTab = 'advice'" 
                :class="activeTab === 'advice' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400'"
                class="flex-1 py-3 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all">Objection Hub</button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
        
        <!-- Summary Tab -->
        <div x-show="activeTab === 'summary'" class="space-y-4 animate-fade-in">
            <div class="bg-indigo-50/50 rounded-2xl p-5 border border-indigo-100/50">
                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0012 18.75c-1.03 0-1.9-.4-2.593-1.02l-.547-.548z"/></svg>
                    Contextual Analysis
                </p>
                <div class="space-y-3 prose prose-sm">
                    <div x-show="loading" class="space-y-2">
                        <div class="h-3 bg-slate-200 rounded animate-pulse w-3/4"></div>
                        <div class="h-3 bg-slate-100 rounded animate-pulse w-full"></div>
                        <div class="h-3 bg-slate-100 rounded animate-pulse w-2/3"></div>
                    </div>
                    <div x-show="!loading" class="text-slate-600 text-xs leading-relaxed font-medium whitespace-pre-wrap" x-text="summary"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button @click="getAdvice('The pricing is too high compared to others')" class="p-3 bg-white rounded-xl border border-slate-100 text-left hover:border-indigo-200 hover:shadow-sm transition-all group">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-500 transition-colors">Handle</p>
                    <p class="text-[10px] font-bold text-slate-700 mt-1">High Price</p>
                </button>
                <button @click="getAdvice('I dont have time right now to discuss')" class="p-3 bg-white rounded-xl border border-slate-100 text-left hover:border-indigo-200 hover:shadow-sm transition-all group">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-500 transition-colors">Handle</p>
                    <p class="text-[10px] font-bold text-slate-700 mt-1">No Time</p>
                </button>
            </div>
        </div>

        <!-- Advice Tab -->
        <div x-show="activeTab === 'advice'" class="animate-fade-in space-y-4">
            <template x-if="advice">
                <div class="bg-emerald-50/50 rounded-2xl p-5 border border-emerald-100/50">
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Smart Rebuttal
                    </p>
                    <div class="text-slate-700 text-xs leading-relaxed font-medium italic whitespace-pre-wrap" x-text="advice"></div>
                </div>
            </template>
            <template x-if="!advice && !loading">
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Select an objection to get advice</p>
                </div>
            </template>
            <div x-show="loading" class="space-y-4">
                <div class="h-24 bg-slate-100 rounded-2xl animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="p-6 bg-slate-50 border-t border-white/40">
        <div class="flex items-center gap-3">
            <input type="text" placeholder="Ask AI anything..." class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
            <button class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>
</div>
