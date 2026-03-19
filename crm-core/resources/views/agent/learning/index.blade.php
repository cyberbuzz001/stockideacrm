<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Learning Center</h1>
                <p class="text-sm text-slate-400 mt-0.5">Enhance your skills with our training modules.</p>
            </div>
            @if(\App\Models\TrainingLog::where('user_id', Auth::id())->where('completion_status', 'completed')->whereHas('module', function($q) { $q->where('schedule_type', 'Monthly'); })->exists())
                <div class="flex items-center gap-2 bg-gradient-to-r from-amber-100 to-yellow-100 border border-yellow-200 px-4 py-2 rounded-xl shadow-sm">
                    <span class="text-xl">🏆</span>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-yellow-700">Certified Expert</p>
                        <p class="text-xs font-bold text-yellow-800">Monthly Training Completed</p>
                    </div>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($modules as $module)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                    <!-- Top accent line -->
                    <div class="h-1 w-full {{ $module->is_completed ? 'bg-emerald-500' : 'bg-indigo-500' }}"></div>
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-xl {{ $module->file_type === 'pdf' ? 'bg-red-50 text-red-600' : ($module->file_type === 'video' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600') }} flex items-center justify-center flex-shrink-0">
                                @if($module->file_type === 'pdf')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @elseif($module->file_type === 'video')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            
                            @if($module->is_completed)
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Done
                                </span>
                            @elseif($module->deadline_date && $module->deadline_date->isPast())
                                <span class="bg-rose-100 text-rose-700 text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-full border border-rose-200">
                                    Overdue
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-full">
                                    Pending
                                </span>
                            @endif
                        </div>
                        
                        <div class="mb-4 flex-1">
                            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{{ $module->schedule_type }} TRAINING</span>
                            <h3 class="font-bold text-slate-900 mt-1 line-clamp-2 text-balance">{{ $module->title }}</h3>
                        </div>
                        
                        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="text-xs text-slate-500 font-medium">
                                @if($module->is_completed)
                                    Completed {{ \Carbon\Carbon::parse($module->completed_at)->format('M d') }}
                                @elseif($module->deadline_date)
                                    <span class="{{ $module->deadline_date->isPast() ? 'text-rose-600 font-bold' : '' }}">
                                        Due {{ $module->deadline_date->format('M d') }}
                                    </span>
                                @else
                                    No deadline
                                @endif
                            </div>
                            
                            <a href="{{ route('agent.learning.show', $module) }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $module->is_completed ? 'bg-slate-50 text-slate-700 hover:bg-slate-100' : 'bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-md' }}">
                                {{ $module->is_completed ? 'Review Again' : 'Start Learning' }}
                                <svg class="w-3.5 h-3.5 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-1 border border-dashed border-slate-200 md:col-span-2 lg:col-span-3 text-center py-12 bg-white rounded-2xl shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <p class="text-slate-500 font-bold text-lg mb-1">No training assigned right now.</p>
                    <p class="text-sm text-slate-400">Great job—you're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
