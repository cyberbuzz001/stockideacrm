<!-- Global Search Command Palette -->
<div x-data="globalSearch()"
     x-show="isOpen"
     x-on:keydown.window.prevent.cmd.k="openSearch()"
     x-on:keydown.window.prevent.ctrl.k="openSearch()"
     x-on:keydown.escape.window="closeSearch()"
     x-on:open-search.window="openSearch()"
     style="display: none;"
     class="fixed inset-0 z-[100] overflow-y-auto p-4 sm:p-6 md:p-20"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeSearch()"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

    <!-- Command Palette Modal -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4 sm:translate-y-0"
         class="mx-auto max-w-2xl transform divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">

        <!-- Search Input -->
        <div class="relative">
            <svg class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            <input x-ref="searchInput"
                   x-model="query"
                   @input.debounce.300ms="fetchResults()"
                   @keydown.arrow-down.prevent="focusNext()"
                   @keydown.arrow-up.prevent="focusPrev()"
                   @keydown.enter.prevent="selectCurrent()"
                   type="text"
                   class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-slate-900 placeholder:text-slate-400 focus:ring-0 sm:text-sm outline-none"
                   placeholder="Search leads, clients, pages... (Ctrl+K)"
                   role="combobox"
                   aria-expanded="false"
                   aria-controls="options">
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="px-6 py-14 text-center text-sm sm:px-14">
            <svg class="mx-auto h-6 w-6 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <p class="mt-4 font-semibold text-slate-900">Searching CRM...</p>
        </div>

        <!-- Results List -->
        <ul x-show="!isLoading && results.length > 0" class="max-h-96 scroll-py-2 overflow-y-auto p-2" id="options" role="listbox">
            <template x-for="(item, index) in results" :key="index">
                <li class="cursor-pointer select-none rounded-xl px-3 py-2.5 hover:bg-slate-50 transition-colors"
                    :class="{ 'bg-indigo-50 ring-1 ring-indigo-200': selectedIndex === index }"
                    @click.stop="safeNavigate(item.url)"
                    @mouseenter="selectedIndex = index"
                    role="option"
                    tabindex="-1">
                    <div class="flex items-center gap-3">
                        
                        <!-- Dynamic Icon -->
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg"
                             :class="{
                                'bg-indigo-50 text-indigo-600': item.type === 'page' || item.type === 'action',
                                'bg-emerald-50 text-emerald-600': item.type === 'client',
                                'bg-violet-50 text-violet-600': item.type === 'lead'
                             }">
                            <template x-if="item.icon === 'home'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </template>
                            <template x-if="item.icon === 'users'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </template>
                           <template x-if="item.icon === 'plus-circle'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                            <template x-if="item.icon === 'briefcase'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </template>
                            <template x-if="item.icon === 'currency-rupee'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                            <template x-if="item.icon === 'chart-bar'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </template>
                            <template x-if="item.icon === 'phone'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </template>
                            <template x-if="item.icon === 'user'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </template>
                            <template x-if="item.icon === 'star'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </template>
                            <template x-if="item.icon === 'document-chart-bar'">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </template>
                        </div>
                        
                        <!-- Result Content -->
                        <div class="flex-auto min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-bold text-slate-900 truncate" x-text="item.title"></p>
                                <!-- Status badge only for leads/clients -->
                                <template x-if="item.type === 'lead' || item.type === 'client'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide"
                                          :class="{
                                            'bg-emerald-100 text-emerald-700': item.type === 'client',
                                            'bg-indigo-100 text-indigo-700': item.status === 'Interested' || item.status === 'Follow Up' || item.status === 'Call Back',
                                            'bg-amber-100 text-amber-700': item.status === 'Free Trial' || item.status === 'Make Payment' || item.status === 'Expected Payment',
                                            'bg-slate-100 text-slate-600': !['Interested','Follow Up','Call Back','Free Trial','Make Payment','Expected Payment','Paid Client'].includes(item.status)
                                          }"
                                          x-text="item.status || 'Lead'">
                                    </span>
                                </template>
                            </div>
                            <!-- Mobile + Agent for leads -->
                            <template x-if="item.type === 'lead' || item.type === 'client'">
                                <div class="flex items-center gap-3 mt-0.5">
                                    <span class="text-[11px] font-mono text-slate-500" x-text="'📱 ' + (item.mobile || '')" ></span>
                                    <span class="text-[11px] font-semibold text-violet-600" x-text="'👤 ' + (item.agent || 'Unassigned')"></span>
                                </div>
                            </template>
                            <!-- Subtitle for pages/actions -->
                            <template x-if="item.type === 'page' || item.type === 'action'">
                                <p class="text-[11px] font-semibold text-slate-500" x-text="item.subtitle"></p>
                            </template>
                        </div>

                        <!-- Arrow Indicator -->
                        <div class="flex-none text-indigo-400" x-show="selectedIndex === index">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    </div>
                </li>
            </template>
        </ul>

        <!-- Empty State -->
        <div x-show="!isLoading && query !== '' && results.length === 0" class="px-6 py-14 text-center text-sm sm:px-14">
            <svg class="mx-auto h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-4 font-semibold text-slate-900">No results found</p>
            <p class="mt-2 text-slate-500">We couldn't find anything matching "<span x-text="query"></span>".</p>
        </div>

        <!-- Footer / Shortcuts Guide -->
        <div class="flex flex-wrap items-center justify-between border-t border-slate-100 bg-slate-50 px-4 py-3 sm:px-6">
            <div class="flex items-center gap-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <div class="flex items-center gap-1.5">
                    <kbd class="flex h-5 w-5 items-center justify-center rounded bg-white font-sans text-xs shadow ring-1 ring-slate-200">↑</kbd>
                    <kbd class="flex h-5 w-5 items-center justify-center rounded bg-white font-sans text-xs shadow ring-1 ring-slate-200">↓</kbd>
                    <span>to navigate</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <kbd class="flex h-5 px-1.5 items-center justify-center rounded bg-white font-sans text-[10px] shadow ring-1 ring-slate-200">↵</kbd>
                    <span>to select</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <kbd class="flex h-5 px-1.5 items-center justify-center rounded bg-white font-sans text-[10px] shadow ring-1 ring-slate-200">ESC</kbd>
                    <span>to close</span>
                </div>
            </div>
            <a href="{{ route('leads.index') }}" class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-800">Advanced Search &rarr;</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('globalSearch', () => ({
        isOpen: false,
        query: '',
        results: [],
        isLoading: false,
        selectedIndex: 0,
        abortController: null,

        openSearch() {
            this.isOpen = true;
            this.query = '';
            this.results = [];
            this.selectedIndex = 0;
            setTimeout(() => this.$refs.searchInput.focus(), 100);
        },
        
        closeSearch() {
            this.isOpen = false;
        },

        async fetchResults() {
            if (this.query.trim() === '') {
                this.results = [];
                return;
            }
            this.isLoading = true;
            this.selectedIndex = 0;
            try {
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                const res = await fetch(`/search?q=${encodeURIComponent(this.query)}`, { signal: this.abortController.signal });
                this.results = await res.json();
            } catch(e) {
                if (e && e.name === 'AbortError') {
                    this.isLoading = false;
                    return;
                }
                console.error("Search failed", e);
                this.results = [];
            }
            this.isLoading = false;
        },

        focusNext() {
            if (this.results.length > 0) {
                this.selectedIndex = (this.selectedIndex + 1) % this.results.length;
                this.scrollToSelected();
            }
        },

        focusPrev() {
            if (this.results.length > 0) {
                this.selectedIndex = this.selectedIndex - 1 < 0 ? this.results.length - 1 : this.selectedIndex - 1;
                this.scrollToSelected();
            }
        },

        scrollToSelected() {
            this.$nextTick(() => {
                const container = document.getElementById('options');
                if(!container) return;
                const activeItem = container.children[this.selectedIndex];
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        selectCurrent() {
            if (this.results.length > 0 && this.results[this.selectedIndex]) {
                this.safeNavigate(this.results[this.selectedIndex].url);
            }
        },

        safeNavigate(url) {
            if (typeof url !== 'string' || !url) return;
            this.isOpen = false;
            window.location.href = url;
        }
    }));
});
</script>
