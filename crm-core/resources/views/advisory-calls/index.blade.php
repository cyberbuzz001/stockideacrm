<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Market Calls') }}
            </h2>
            <a href="{{ route('advisory-calls.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700">
                + New Market Call
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm">
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Segment Tabs (Section 4) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
                <div class="flex overflow-x-auto border-b border-slate-100 bg-slate-50/50">
                    @foreach($segments as $seg)
                        <a href="{{ route('advisory-calls.index', ['segment' => $seg]) }}"
                            class="px-6 py-4 text-sm font-bold whitespace-nowrap transition-colors border-b-2 {{ $segment === $seg ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                            {{ $seg }}
                        </a>
                    @endforeach
                </div>

                <!-- Call History Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400 text-[10px] uppercase font-black tracking-widest bg-slate-50/50">
                                <th class="px-6 py-4">Segment</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Time</th>
                                <th class="px-6 py-4">Call</th>
                                <th class="px-6 py-4">Outcome</th>
                                <th class="px-6 py-4">Created By</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($calls as $call)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                                            {{ $call->segment }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-700">
                                        {{ $call->call_date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        {{ \Carbon\Carbon::parse($call->call_time)->format('H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-slate-900 font-medium line-clamp-2">{{ $call->call_text }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($call->outcome)
                                            <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ $call->outcome === 'Target Achieved' ? 'bg-emerald-100 text-emerald-700' : ($call->outcome === 'SL Hit' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">
                                                {{ $call->outcome }}
                                            </span>
                                        @else
                                            <form action="{{ route('advisory-calls.outcome', $call) }}" method="POST" class="flex gap-1">
                                                @csrf
                                                <button type="submit" name="outcome" value="Target Achieved" class="px-2 py-1 text-[10px] font-bold rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors border border-emerald-100">
                                                    🎯 Hit
                                                </button>
                                                <button type="submit" name="outcome" value="SL Hit" class="px-2 py-1 text-[10px] font-bold rounded-md bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors border border-rose-100">
                                                    📉 SL
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-slate-500">{{ $call->user->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('advisory-calls.show', $call) }}"
                                                class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-200">
                                                View
                                            </a>
                                            <form action="{{ route('advisory-calls.destroy', $call) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this call?')"
                                                    class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                        No market calls found for {{ $segment }}. Create your first call above!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($calls->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $calls->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>