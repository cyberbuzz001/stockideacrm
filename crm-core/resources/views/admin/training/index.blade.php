<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Training Manager</h1>
                <p class="text-sm text-slate-400 mt-0.5">Upload and manage training modules for your team.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upload Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="font-bold text-slate-900 text-base mb-4">Upload New Module</h2>
                    <form action="{{ route('admin.training.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mt-2 mb-1">Module Title</label>
                            <input type="text" name="title" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Option Hedging 101">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mt-2 mb-1">File (PDF, Image, Video)</label>
                            <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.mp4,.webm" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[10px] text-slate-400 mt-1">Max size: 50MB</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mt-2 mb-1">Target Team</label>
                                <select name="target_team" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="All">All Agents</option>
                                    <option value="SBA">SBA Only</option>
                                    <option value="BA">BA Only</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mt-2 mb-1">Schedule</label>
                                <select name="schedule_type" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mt-2 mb-1">Deadline Date (Optional)</label>
                            <input type="date" name="deadline_date" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white font-bold text-sm px-4 py-2.5 rounded-xl hover:bg-indigo-700 transition-colors mt-2">
                            Upload & Assign Module
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modules List -->
            <div class="lg:col-span-2 space-y-4">
                @forelse($modules as $module)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $module->file_type === 'pdf' ? 'bg-red-50 text-red-600' : ($module->file_type === 'video' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600') }} flex items-center justify-center">
                            @if($module->file_type === 'pdf')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @elseif($module->file_type === 'video')
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-900 truncate text-sm">{{ $module->title }}</h3>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ $module->target_team }}</span>
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ $module->schedule_type }}</span>
                                @if($module->deadline_date)
                                    <span class="text-[10px] text-rose-500 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Due {{ $module->deadline_date->format('M d, Y') }}
                                    </span>
                                @endif
                                <span class="text-[10px] text-slate-400">· Added {{ $module->created_at->diffForHumans() }}</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mt-3">
                                <div class="flex justify-between items-center text-[10px] mb-1">
                                    <span class="text-slate-500 font-bold">Completion</span>
                                    <span class="font-black text-indigo-600">{{ $module->completion_percentage }}% ({{ $module->completed_count }}/{{ $module->total_assigned }})</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-500 h-full rounded-full transition-all duration-700" style="width: {{ $module->completion_percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                             <form action="{{ route('admin.training.destroy', $module) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this module?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:bg-rose-50 p-2 rounded-lg transition-colors" title="Delete Module">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                            <a href="{{ Storage::url($module->file_path) }}" target="_blank" class="bg-slate-50 hover:bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                View File
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white rounded-2xl border border-slate-100 border-dashed">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <p class="text-slate-500 text-sm font-bold">No training modules uploaded yet.</p>
                        <p class="text-xs text-slate-400 mt-1">Upload your first module using the form on the left.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
