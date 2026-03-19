<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Compliance Overview (KYC/RPM)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tighter">Pending Verifications</h3>
                    <div class="flex gap-2">
                        <a href="?status=pending" class="px-4 py-2 rounded-xl text-xs font-black {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }}">Pending</a>
                        <a href="{{ route('kyc-rpm.index') }}" class="px-4 py-2 rounded-xl text-xs font-black {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }}">All Leads</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-4 py-2">Lead Name</th>
                                <th class="px-4 py-2">PAN Status</th>
                                <th class="px-4 py-2">KYC</th>
                                <th class="px-4 py-2">RPM</th>
                                <th class="px-4 py-2">Assigned To</th>
                                <th class="px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($leads as $lead)
                            <tr class="bg-slate-50 hover:bg-slate-100 transition-colors rounded-2xl">
                                <td class="px-4 py-4 font-black text-slate-900 rounded-l-2xl">
                                    {{ $lead->name }}
                                    <span class="block text-[10px] font-bold text-slate-400">{{ $lead->mobile }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-mono text-xs font-bold text-slate-600">{{ $lead->masked_pan ?? 'Missing' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $lead->is_kyc_completed ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $lead->is_kyc_completed ? 'Done' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $lead->is_rpm_completed ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $lead->is_rpm_completed ? 'Done' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-bold text-slate-600">
                                    {{ $lead->assignedUser->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-4 py-4 rounded-r-2xl">
                                    <a href="{{ route('kyc-rpm.show', $lead) }}" class="text-indigo-600 font-black hover:text-indigo-800 transition">View Compliance</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
