<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Access Report
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.data-access') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">User</label>
                        <select name="user_id" class="mt-1 w-full border-slate-200 rounded-lg">
                            <option value="">All</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                    {{ $u->name }} ({{ $u->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Field</label>
                        <select name="field" class="mt-1 w-full border-slate-200 rounded-lg">
                            <option value="">All</option>
                            @foreach(['pan_number','aadhaar_number'] as $field)
                                <option value="{{ $field }}" @selected(request('field') === $field)>{{ $field }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Action</label>
                        <select name="action" class="mt-1 w-full border-slate-200 rounded-lg">
                            <option value="">All</option>
                            @foreach(['view','export','print','api'] as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full border-slate-200 rounded-lg">
                    </div>
                    <div class="md:col-span-5 flex items-center gap-3">
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold">Filter</button>
                        <a href="{{ route('admin.data-access') }}" class="text-sm text-slate-500">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Lead</th>
                            <th class="px-4 py-3">Field</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-4 py-3 text-slate-600">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3 text-slate-900">{{ $log->user->name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3 text-slate-600">#{{ $log->lead_id }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->field }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $logs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
