<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Market Call: <span class="text-indigo-600 font-bold">#{{ $advisoryCall->id }}</span>
            </h2>
            <a href="{{ route('advisory-calls.index') }}"
                class="text-sm font-bold text-slate-500 hover:text-slate-700">← Back to List</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Call Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <span
                                class="px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full font-black text-xs uppercase tracking-widest">
                                {{ $advisoryCall->segment }}
                            </span>
                            <span class="text-slate-400 text-sm">
                                Released on {{ $advisoryCall->call_date->format('d M Y') }} at
                                {{ \Carbon\Carbon::parse($advisoryCall->call_time)->format('h:i A') }}
                            </span>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                            <p class="text-lg font-bold text-slate-900 leading-relaxed whitespace-pre-wrap">
                                {{ $advisoryCall->call_text }}
                            </p>
                        </div>

                        <!-- Update Outcome Section (New) -->
                        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-black text-indigo-900 uppercase tracking-tighter mb-1">Call Outcome</h4>
                                    @if($advisoryCall->outcome)
                                        <span class="px-3 py-1 bg-white rounded-full text-xs font-bold text-indigo-700 shadow-sm border border-indigo-100">
                                            Current: {{ $advisoryCall->outcome }}
                                        </span>
                                    @else
                                        <p class="text-xs text-slate-500 font-medium">Update the call performance status</p>
                                    @endif
                                </div>

                                <form action="{{ route('advisory-calls.outcome', $advisoryCall) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <select name="outcome" class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 font-bold text-slate-700">
                                        <option value="">Update Status...</option>
                                        <option value="Target Achieved" {{ $advisoryCall->outcome == 'Target Achieved' ? 'selected' : '' }}>Target Achieved</option>
                                        <option value="SL Hit" {{ $advisoryCall->outcome == 'SL Hit' ? 'selected' : '' }}>SL Hit</option>
                                        <option value="Exit" {{ $advisoryCall->outcome == 'Exit' ? 'selected' : '' }}>Exit</option>
                                        <option value="Partial Exit" {{ $advisoryCall->outcome == 'Partial Exit' ? 'selected' : '' }}>Partial Exit</option>
                                        <option value="Partial Book" {{ $advisoryCall->outcome == 'Partial Book' ? 'selected' : '' }}>Partial Book</option>
                                        <option value="Trailing SL" {{ $advisoryCall->outcome == 'Trailing SL' ? 'selected' : '' }}>Trailing SL</option>
                                    </select>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-700 transition">
                                        Update
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ substr($advisoryCall->user->name, 0, 1) }}
                                </div>
                                <span class="text-sm text-slate-500 font-medium">Created by
                                    {{ $advisoryCall->user->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Targeted Clients & Broadcasting -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Broadcasting</h3>
                        <p class="text-xs text-slate-500 mb-6 font-medium uppercase tracking-wide">Targeting clients in
                            "{{ $advisoryCall->segment }}"</p>

                        <form action="{{ route('advisory-calls.broadcast') }}" method="POST">
                            @csrf
                            <input type="hidden" name="call_id" value="{{ $advisoryCall->id }}">

                            <div class="max-h-[400px] overflow-y-auto mb-6 pr-2 custom-scrollbar">
                                @forelse($targetClients as $client)
                                    <div
                                        class="flex items-center gap-3 p-3 mb-2 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors cursor-pointer group">
                                        <input type="checkbox" name="client_ids[]" value="{{ $client->id }}" checked
                                            class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-slate-900">{{ $client->name }}</p>
                                            <p class="text-[10px] text-slate-500 font-bold">{{ $client->mobile }} • Agent:
                                                {{ $client->assignee->name ?? 'None' }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-10">
                                        <p class="text-sm text-slate-400 italic">No clients found interested in this
                                            segment.</p>
                                    </div>
                                @endforelse
                            </div>

                            @if($targetClients->count() > 0)
                                <button type="submit"
                                    class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition transform active:scale-95">
                                    🚀 Send Broadcast ({{ $targetClients->count() }})
                                </button>
                                <p class="text-[10px] text-center text-slate-400 mt-4 font-bold leading-tight">
                                    This will send a notification and internal mail to selected clients and their assigned
                                    agents.
                                </p>
                            @endif
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>