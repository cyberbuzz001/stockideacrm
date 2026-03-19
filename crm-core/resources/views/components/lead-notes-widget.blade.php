@props(['lead'])

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden" x-data="{
    notes: {{ json_encode($lead->internal_notes ?? '') }},
    savedNotes: {{ json_encode($lead->internal_notes ?? '') }},
    isSaving: false,
    pendingSave: false,
    lastSaved: null,
    leadId: {{ $lead->id }},
    
    async saveNotes() {
        if (this.isSaving) { this.pendingSave = true; return; }
        if (this.notes === this.savedNotes) return;
        
        this.isSaving = true;
        try {
            const response = await fetch(`/leads/${this.leadId}/notes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    internal_notes: this.notes
                })
            });
            if (!response.ok) throw new Error('Save failed');
            const result = await response.json();
            if (result.success) {
                this.savedNotes = this.notes;
                this.lastSaved = new Date();
                setTimeout(() => this.lastSaved = null, 3000);
            }
        } catch (error) {
            console.error('Failed to save notes:', error);
        } finally {
            this.isSaving = false;
            if (this.pendingSave) {
                this.pendingSave = false;
                this.saveNotes();
            }
        }
    },
    
    formatLastSaved() {
        if (!this.lastSaved) return '';
        return 'Saved at ' + this.lastSaved.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
}">

    <!-- Header -->
    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                </path>
            </svg>
            <h3 class="text-sm font-bold text-slate-900">Internal Notes</h3>
        </div>
        <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">Private</span>
    </div>

    <!-- Notes Textarea -->
    <div class="p-4">
        <textarea x-model="notes" @input.debounce.1000ms="saveNotes()"
            placeholder="Add internal notes about this lead... (auto-saves)" rows="8"
            class="w-full px-3 py-2 text-sm rounded-xl border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
            :disabled="isSaving"></textarea>

        <!-- Save Status -->
        <div class="mt-2 flex items-center justify-between">
            <p class="text-[10px] text-slate-400 italic">
                Auto-saves as you type
            </p>
            <div class="flex items-center gap-2">
                <template x-if="isSaving">
                    <span class="text-[10px] text-indigo-600 font-bold flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Saving...
                    </span>
                </template>
                <template x-if="lastSaved && !isSaving">
                    <span class="text-[10px] text-green-600 font-bold flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span x-text="formatLastSaved()"></span>
                    </span>
                </template>
            </div>
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="px-4 pb-4 pt-2 border-t border-slate-100 bg-slate-50/30">
        <p class="text-[10px] text-slate-500 italic">
            💡 <strong>Tip:</strong> Use this space to document call outcomes, objections, preferences, or any context
            that helps your team convert this lead.
        </p>
    </div>
</div>
