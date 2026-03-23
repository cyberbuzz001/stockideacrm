<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto font-sans">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-black text-[#111827]">Roles & Access Control</h1>
            <p class="text-sm text-[#6B7280] mt-1">Define exactly what each role can see and do across the {{ $company_name }} CRM platform.</p>
        </div>

        <!-- Tab Bar -->
        <div class="flex border-b border-[#E5E7EB] mb-8 gap-8 overflow-x-auto pb-px">
            <a href="#" class="pb-4 text-sm font-bold text-[#4F46E5] border-b-2 border-[#4F46E5] whitespace-nowrap">Permissions Matrix</a>
            <a href="#" class="pb-4 text-sm font-medium text-[#9CA3AF] hover:text-[#6B7280] whitespace-nowrap">Role Definitions</a>
            <a href="#" class="pb-4 text-sm font-medium text-[#9CA3AF] hover:text-[#6B7280] whitespace-nowrap">Data Boundaries</a>
            <a href="#" class="pb-4 text-sm font-medium text-[#9CA3AF] hover:text-[#6B7280] whitespace-nowrap">Audit Log</a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.roles.update') }}" method="POST">
            @csrf
            <div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-[#9CA3AF] min-w-[200px] sticky left-0 bg-[#F9FAFB] z-10">Module / Feature</th>
                                @foreach($roles as $role)
                                    <th class="p-5 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase 
                                            {{ $role === 'Admin' ? 'bg-[#EEF2FF] text-[#4F46E5]' : 
                                               ($role === 'Manager' ? 'bg-[#F0FDFA] text-[#0F6E56]' : 
                                               ($role === 'Team Leader' ? 'bg-[#FFFBEB] text-[#BA7517]' : 
                                               ($role === 'SBA' ? 'bg-[#EFF6FF] text-[#185FA5]' : 'bg-[#F3F4F6] text-[#5F5E5A]'))) }}">
                                            {{ $role }}
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3F4F6]">
                            @foreach($modules as $moduleName => $features)
                                <tr class="bg-[#F8F9FB]/50">
                                    <td colspan="{{ count($roles) + 1 }}" class="p-4 text-[11px] font-black uppercase tracking-widest text-[#4F46E5] bg-[#EEF2FF]/30">
                                        {{ $moduleName }}
                                    </td>
                                </tr>
                                @foreach($features as $key => $description)
                                    <tr class="group hover:bg-[#F9FAFB] transition-colors">
                                        <td class="p-5 sticky left-0 bg-white group-hover:bg-[#F9FAFB] z-10 min-w-[250px]">
                                            <p class="text-sm font-bold text-[#111827]">{{ $description }}</p>
                                            <p class="text-[10px] text-[#9CA3AF] uppercase">{{ $key }}</p>
                                        </td>
                                        @foreach($roles as $role)
                                            <td class="p-5 text-center">
                                                <div class="flex justify-center">
                                                    <label class="cursor-pointer group/label">
                                                        <input type="checkbox" name="matrix[{{ strtolower(str_replace(' ', '_', $moduleName)) }}][{{ $key }}][]" value="{{ $role }}"
                                                            class="hidden peer"
                                                            {{ (isset($matrix[strtolower(str_replace(' ', '_', $moduleName))][$key]) && in_array($role, $matrix[strtolower(str_replace(' ', '_', $moduleName))][$key])) ? 'checked' : '' }}>
                                                        
                                                        <!-- UI representation of the Permission -->
                                                        <div class="w-9 h-9 rounded-xl border-2 border-slate-200 flex items-center justify-center transition-all peer-checked:bg-[#4F46E5] peer-checked:border-[#4F46E5] peer-checked:text-white group-hover/label:border-[#4F46E5] text-[#D1D5DB]">
                                                            <svg class="w-5 h-5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                <path class="peer-checked:block" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </div>
                                                    </label>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Save Footer -->
                <div class="p-5 bg-[#F9FAFB] border-t border-[#E5E7EB] flex justify-between items-center">
                    <p class="text-xs text-[#6B7280]">
                        <span class="font-bold">Note:</span> Permissions are cached for performance. Some changes may require re-login.
                    </p>
                    <div class="flex gap-3">
                        <button type="button" onclick="window.location.reload()" class="px-4 py-2 text-xs font-bold text-[#6B7280] bg-white border border-[#E5E7EB] rounded-lg hover:bg-[#F3F4F6] transition-colors">Discard Changes</button>
                        <button type="submit" class="px-5 py-2 text-xs font-black text-white bg-[#4F46E5] rounded-lg shadow-sm hover:bg-[#4338CA] transition-colors">Save Permissions Matrix</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
