<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Activity Logs') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'lead' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tabs -->
            <div class="flex space-x-4 mb-6">
                <button @click="tab = 'lead'"
                    :class="{ 'bg-indigo-600 text-white': tab === 'lead', 'bg-white text-slate-600 hover:bg-slate-50': tab !== 'lead' }"
                    class="px-6 py-2 rounded-xl font-bold transition-colors shadow-sm">
                    Lead Activities
                </button>
                @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                    <button @click="tab = 'system'"
                        :class="{ 'bg-indigo-600 text-white': tab === 'system', 'bg-white text-slate-600 hover:bg-slate-50': tab !== 'system' }"
                        class="px-6 py-2 rounded-xl font-bold transition-colors shadow-sm">
                        System Logs
                    </button>
                @endif
            </div>

            <!-- Lead Activities Tab -->
            <div x-show="tab === 'lead'" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-lg text-slate-900">Lead Activity Stream</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Lead</th>
                                <th class="px-6 py-4">Activity</th>
                                <th class="px-6 py-4">Details</th>
                                <th class="px-6 py-4">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($leadActivities as $activity)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        {{ $activity->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('leads.show', $activity->lead_id) }}"
                                            class="text-indigo-600 hover:underline font-medium">
                                            {{ $activity->lead->name ?? 'Deleted Lead' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold">{{ $activity->activity_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="{{ $activity->notes }}">
                                        {{ $activity->notes }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 text-xs text-right">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination (Lead) -->
                <div class="p-4 border-t border-slate-100">
                    {{ $leadActivities->links() }}
                </div>
            </div>

            <!-- System Activities Tab -->
            @if(in_array(auth()->user()->role, ['Admin', 'Manager']))
                <div x-show="tab === 'system'" x-cloak
                    class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h3 class="font-bold text-lg text-slate-900">System Logs (Login/Logout)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50/50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">User</th>
                                    <th class="px-6 py-4">Event</th>
                                    <th class="px-6 py-4">IP Address</th>
                                    <th class="px-6 py-4">Description</th>
                                    <th class="px-6 py-4 text-right">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @if($systemActivities instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                    @foreach($systemActivities as $activity)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-slate-900">
                                                {{ $activity->user->name ?? 'Unknown' }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-3 py-1 
                                                        {{ $activity->activity_type === 'Login' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} 
                                                        rounded-lg text-xs font-bold">
                                                    {{ $activity->activity_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                                {{ $activity->ip_address }}
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">
                                                {{ $activity->description }}
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-xs text-right">
                                                {{ $activity->created_at->format('M d, H:i:s') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination (System) -->
                    <div class="p-4 border-t border-slate-100">
                        @if($systemActivities instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $systemActivities->links() }}
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>