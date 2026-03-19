<x-app-layout>
    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6" x-data="{ tab: 'sms' }">
        
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Message & Email Templates</h1>
                <p class="text-sm text-slate-500 mt-0.5">Manage reusable drafts for agent communication</p>
            </div>
            <button @click="$dispatch('open-modal', 'create-template')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2 transition-all shadow-lg shadow-indigo-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create New Template
            </button>
        </div>

        @if(session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-sm font-bold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Tabs -->
        <div class="flex border-b border-slate-200 gap-8">
            <button @click="tab = 'sms'" :class="tab === 'sms' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                SMS Templates ({{ $templates->where('type', 'sms')->count() }})
            </button>
            <button @click="tab = 'email'" :class="tab === 'email' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="pb-4 border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                Email Templates ({{ $templates->where('type', 'email')->count() }})
            </button>
        </div>

        <!-- Template Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <template x-for="type in ['sms', 'email']">
                <div x-show="tab === type" class="contents">
                    @foreach($templates as $tpl)
                        <div x-show="tab === '{{ $tpl->type }}'" class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow p-6 space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-black text-slate-900 border-b-4 border-indigo-100 pb-1 mb-1">{{ $tpl->name }}</h3>
                                    @if($tpl->type === 'email')
                                        <p class="text-xs font-bold text-slate-400 italic">Subject: {{ $tpl->subject }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="$dispatch('open-modal', 'edit-template-{{ $tpl->id }}')" class="p-2 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form action="{{ route('message-templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Delete this template?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-sm text-slate-600 font-medium whitespace-pre-wrap leading-relaxed">
                                {{ $tpl->body }}
                            </div>
                        </div>

                        <!-- Edit Modal for {{ $tpl->id }} -->
                        <x-modal name="edit-template-{{ $tpl->id }}" focusable>
                            <form action="{{ route('message-templates.update', $tpl) }}" method="POST" class="p-6">
                                @csrf @method('PATCH')
                                <h2 class="text-lg font-black text-slate-900 border-b-2 border-indigo-600 inline-block mb-6">Edit Template</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Template Name</label>
                                        <input type="text" name="name" value="{{ $tpl->name }}" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Type</label>
                                            <select name="type" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600 cursor-pointer">
                                                <option value="sms" {{ $tpl->type === 'sms' ? 'selected' : '' }}>SMS</option>
                                                <option value="email" {{ $tpl->type === 'email' ? 'selected' : '' }}>Email</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Email Subject (Optional)</label>
                                            <input type="text" name="subject" value="{{ $tpl->subject }}" class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600 placeholder:font-normal" placeholder="For emails only">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Body</label>
                                        <textarea name="body" rows="6" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600">{{ $tpl->body }}</textarea>
                                        <p class="text-[10px] text-slate-400 mt-2 font-bold tracking-tight">VARS: <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{name}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{mobile}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{agent}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{company}</span></p>
                                    </div>
                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="button" @click="$dispatch('close')" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700">Cancel</button>
                                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Update Template</button>
                                    </div>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
                </div>
            </template>
        </div>

        <!-- Create Modal -->
        <x-modal name="create-template" focusable>
            <form action="{{ route('message-templates.store') }}" method="POST" class="p-6">
                @csrf
                <h2 class="text-lg font-black text-slate-900 border-b-2 border-indigo-600 inline-block mb-6">New Template</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Template Name</label>
                        <input type="text" name="name" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600" placeholder="e.g. Standard Introduction">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Type</label>
                            <select name="type" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600 cursor-pointer">
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Email Subject (Optional)</label>
                            <input type="text" name="subject" class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600 placeholder:font-normal" placeholder="For emails only">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Body</label>
                        <textarea name="body" rows="6" required class="w-full rounded-xl border-slate-200 font-bold focus:ring-indigo-600" placeholder="Hi {name}, I'm {agent} from {company}..."></textarea>
                        <p class="text-[10px] text-slate-400 mt-2 font-bold tracking-tight">VARS: <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{name}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{mobile}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{agent}</span> <span class="text-indigo-600 bg-indigo-50 px-1 rounded">{company}</span></p>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="$dispatch('close')" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-700">Cancel</button>
                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Create Template</button>
                    </div>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>
