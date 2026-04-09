<div x-data="{ open: false }" 
     @open-mobile-actions.window="open = true"
     class="lg:hidden">
    
    <!-- Floating Action Button -->
    <button @click="open = true" 
            class="fixed bottom-8 right-8 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-[0_10px_30px_rgba(79,70,229,0.4)] flex items-center justify-center z-[85] active:scale-95 transition-transform">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    </button>

    <!-- Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false" 
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-[90]"></div>

    <!-- Bottom Sheet -->
    <div x-show="open" 
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed inset-x-0 bottom-0 bg-white rounded-t-[2.5rem] shadow-2xl z-[100] p-8 pb-12">
        
        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-8"></div>

        <h3 class="text-xl font-black text-slate-900 mb-6 text-center">Quick Actions</h3>

        <div class="grid grid-cols-3 gap-6">
            <a href="{{ route('leads.index') }}?action=create" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-active:bg-indigo-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">New Lead</span>
            </a>
            
            <button @click="window.dispatchEvent(new CustomEvent('open-search')); open = false" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 group-active:bg-amber-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">Quick Search</span>
            </button>

            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-active:bg-emerald-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">Home</span>
            </a>

            <a href="{{ route('advisory-calls.index') }}" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 group-active:bg-rose-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">Call Pool</span>
            </a>

            <a href="{{ route('payments.index') }}" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center text-violet-600 group-active:bg-violet-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">Sales</span>
            </a>

            <button @click="open = false" class="flex flex-col items-center gap-2 group">
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-500 group-active:bg-slate-600 group-active:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-500">Close</span>
            </button>
        </div>
    </div>
</div>
