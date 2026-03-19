<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Consent Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
                <form method="GET" action="{{ route('admin.consents') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Channel</label>
                        <select name="channel" class="mt-1 w-full border-slate-200 rounded-lg">
                            <option value="">All</option>
                            @foreach(['call','whatsapp','sms','email'] as $channel)
                                <option value="{{ $channel }}" @selected(request('channel') === $channel)>{{ $channel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Purpose</label>
                        <input type="text" name="purpose" value="{{ request('purpose') }}" class="mt-1 w-full border-slate-200 rounded-lg" placeholder="e.g. onboarding">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Status</label>
                        <select name="status" class="mt-1 w-full border-slate-200 rounded-lg">
                            <option value="">All</option>
                            @foreach(['granted','revoked'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
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
                        <a href="{{ route('admin.consents') }}" class="text-sm text-slate-500">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                        <tr>
                            <th class="px-4 py-3">Time</th>
                            <th class="px-4 py-3">Lead</th>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">Purpose</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($consents as $consent)
                            <tr>
                                <td class="px-4 py-3 text-slate-600">{{ $consent->created_at->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3 text-slate-600">#{{ $consent->lead_id }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $consent->channel }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $consent->purpose }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $consent->status }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $consent->user->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-400">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $consents->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
