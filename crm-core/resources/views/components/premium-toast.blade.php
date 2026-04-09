<div x-data="{ 
        messages: [],
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        },
        add(message, type = 'info') {
            const id = Date.now();
            this.messages.push({ id, message, type });
            setTimeout(() => this.remove(id), 5000);
        }
    }"
    @toast-notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed top-8 right-8 z-[100] flex flex-col gap-4 pointer-events-none w-80">
    
    <!-- Session Messages (Automatic) -->
    @if(session('success'))
        <div x-init="add('{{ session('success') }}', 'success')" class="hidden"></div>
    @endif
    @if(session('error'))
        <div x-init="add('{{ session('error') }}', 'error')" class="hidden"></div>
    @endif

    <template x-for="msg in messages" :key="msg.id">
        <div x-data="{ show: false }"
             x-init="setTimeout(() => show = true, 50)"
             x-show="show"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="translate-x-full opacity-0 scale-95"
             x-transition:enter-end="translate-x-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="pointer-events-auto bg-white/80 backdrop-blur-2xl border border-white/50 p-5 rounded-[1.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] flex items-center gap-4 group">
            
            <div :class="{
                    'bg-emerald-500': msg.type === 'success',
                    'bg-rose-500': msg.type === 'error',
                    'bg-indigo-600': msg.type === 'info'
                }"
                class="w-10 h-10 rounded-2xl flex items-center justify-center text-white shadow-lg shrink-0">
                <template x-if="msg.type === 'success'"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg></template>
                <template x-if="msg.type === 'error'"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12"/></svg></template>
                <template x-if="msg.type === 'info'"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></template>
            </div>

            <div class="flex-1">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5" x-text="msg.type"></p>
                <p class="text-sm font-bold text-slate-900 leading-tight" x-text="msg.message"></p>
            </div>

            <button @click="show = false" class="text-slate-300 hover:text-slate-500 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
