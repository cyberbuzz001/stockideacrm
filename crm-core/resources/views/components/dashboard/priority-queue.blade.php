@props(['leads', 'title' => 'My Priority Queue'])

<div class="glass-card p-6 rounded-3xl flex flex-col h-full animate-fade-in-up">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $title }}</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Automated Action Ranking</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-full">
                {{ $leads->count() }} Leads
            </span>
        </div>
    </div>

    <div class="flex-1 overflow-x-auto dashboard-scroll">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Lead / Status</th>
                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Urgency</th>
                    <th class="pb-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Next Action</th>
                    <th class="pb-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                @forelse($leads as $lead)
                    @php
                        $priorityClass = '';
                        $priorityLabel = '';
                        $now = now();
                        
                        if ($lead->status === 'Call Back' || $lead->status === 'Follow Up') {
                            if ($lead->follow_up_date <= $now) {
                                $priorityClass = 'queue-row-overdue';
                                $priorityLabel = '🔴 OVERDUE';
                            } else {
                                $priorityClass = 'queue-row-today';
                                $priorityLabel = '🟠 TODAY';
                            }
                        } elseif ($lead->created_at >= $now->startOfDay()) {
                            $priorityClass = 'queue-row-new';
                            $priorityLabel = '🔵 NEW';
                        } elseif ($lead->status === 'Free Trial') {
                            $priorityClass = 'queue-row-trial';
                            $priorityLabel = '🟢 TRIAL';
                        } else {
                            $priorityClass = 'queue-row-cold';
                            $priorityLabel = '🧊 COLD';
                        }
                    @endphp
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ $priorityClass }}">
                        <td class="py-4 pl-3">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">{{ $lead->name }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $lead->status }}</span>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="text-[9px] font-black uppercase tracking-tighter">{{ $priorityLabel }}</span>
                        </td>
                        <td class="py-4">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    {{ $lead->follow_up_date ? $lead->follow_up_date->format('d M, H:i') : 'No action set' }}
                                </span>
                                <span class="text-[9px] text-slate-400">{{ $lead->follow_up_date ? $lead->follow_up_date->diffForHumans() : '' }}</span>
                            </div>
                        </td>
                        <td class="py-4 text-right">
                                <button onclick="handleCommEngagement('whatsapp', '{{ $lead->id }}', 'https://wa.me/91{{ preg_replace('/[^0-9]/', '', $lead->mobile) }}')" 
                                        class="p-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="WhatsApp Log">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.385 0 0 5.385 0 12.031c0 2.115.549 4.131 1.594 5.928L0 24l6.233-1.576c1.728 1 3.682 1.536 5.765 1.536 6.638 0 12.022-5.382 12.022-12.031zM12.031 22.022c-1.802 0-3.564-.485-5.111-1.402l-.366-.217-3.793.961.981-3.69-.239-.379c-.997-1.591-1.523-3.425-1.523-5.282 0-5.558 4.524-10.082 10.082-10.082s10.081 4.524 10.081 10.082c-.001 5.558-4.525 10.081-10.082 10.081zm5.534-7.555c-.303-.152-1.795-.886-2.073-.988-.278-.103-.48-.152-.683.153-.203.303-.783.987-.959 1.189-.176.202-.352.227-.655.076-1.532-.765-2.791-1.638-3.901-3.535-.114-.194-.012-.303.141-.453.138-.135.303-.353.454-.531.152-.178.202-.303.303-.505.101-.202.051-.379-.025-.53-.076-.152-.682-1.644-.935-2.253-.247-.591-.497-.509-.682-.519-.176-.008-.379-.011-.581-.011s-.53.076-.808.379c-.278.303-1.06 1.036-1.06 2.527 0 1.491 1.086 2.932 1.238 3.134.152.202 2.138 3.264 5.176 4.576.721.312 1.284.498 1.725.638.723.23 1.382.197 1.898.119.579-.088 1.795-.733 2.047-1.44.253-.708.253-1.315.177-1.442-.075-.126-.277-.201-.58-.352z"/></svg>
                                </button>
                                <a href="tel:{{ $lead->mobile }}" class="p-2 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </a>
                                <a href="{{ route('leads.show', $lead->id) }}" class="p-2 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <span class="text-4xl mb-3">🍃</span>
                                <p class="text-xs font-black uppercase tracking-widest">No Leads in queue</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
