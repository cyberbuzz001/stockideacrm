<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('KYC & RPM Compliance') }} - <span class="text-indigo-600">{{ $lead->name }}</span>
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('leads.show', $lead) }}" class="bg-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold text-sm hover:bg-slate-300 transition">
                    Back to Lead
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'kyc' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tab Navigation -->
            <div class="flex gap-1 bg-slate-100 p-1 rounded-2xl w-fit mb-6 shadow-inner">
                <button @click="activeTab = 'kyc'" 
                        :class="activeTab === 'kyc' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500 hover:text-slate-700'"
                        class="px-6 py-2 rounded-xl text-sm font-black transition-all">
                    KYC Details
                </button>
                <button @click="activeTab = 'rpm'" 
                        :class="activeTab === 'rpm' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500 hover:text-slate-700'"
                        class="px-6 py-2 rounded-xl text-sm font-black transition-all">
                    RPM Checklist
                </button>
                <button @click="activeTab = 'docs'" 
                        :class="activeTab === 'docs' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500 hover:text-slate-700'"
                        class="px-6 py-2 rounded-xl text-sm font-black transition-all">
                    Documents
                </button>
            </div>

            <!-- KYC Tab -->
            <div x-show="activeTab === 'kyc'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Standard Info -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100" x-data="{ editing: false }">
                        <div class="flex justify-between items-center mb-6 border-b pb-2">
                            <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Identification Details</h3>
                            <button @click="editing = !editing" class="text-xs font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">
                                <span x-show="!editing">Edit Details</span>
                                <span x-show="editing">Cancel</span>
                            </button>
                        </div>

                        <div x-show="!editing" class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500">PAN Number</label>
                                <p class="text-lg font-black text-slate-900 font-mono tracking-tighter">{{ $lead->masked_pan }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500">Aadhaar Number</label>
                                <p class="text-lg font-black text-slate-900 font-mono tracking-tighter">{{ $lead->masked_aadhaar }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500">Demat ID</label>
                                <p class="text-lg font-black text-slate-900 font-mono tracking-tighter">{{ $lead->demat_id ?? 'Not Provided' }}</p>
                            </div>
                        </div>

                        <form x-show="editing" action="{{ route('kyc-rpm.update-data', $lead) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block uppercase">Update PAN</label>
                                <input type="text" name="pan_number" value="{{ $lead->pan_number }}" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold p-3 focus:ring-indigo-500 uppercase" placeholder="ABCDE1234F">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block uppercase">Update Aadhaar</label>
                                <input type="text" name="aadhaar_number" value="{{ $lead->aadhaar_number }}" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold p-3 focus:ring-indigo-500" placeholder="1234 5678 9012">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block uppercase">Update Demat ID</label>
                                <input type="text" name="demat_id" value="{{ $lead->demat_id }}" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm font-bold p-3 focus:ring-indigo-500" placeholder="16-digit Demat ID">
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white font-black py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition uppercase tracking-widest text-xs">
                                Save Compliance Data
                            </button>
                        </form>
                    </div>

                    <!-- Status Panel -->
                    <div class="bg-indigo-900 p-8 rounded-3xl shadow-xl text-white">
                        <h3 class="text-sm font-black text-indigo-300 uppercase tracking-widest mb-6 border-b border-indigo-800 pb-2">Verification Status</h3>
                        @php
                            $kycMandatory = \App\Models\SystemSetting::get('kyc_mandatory', '0') === '1';
                        @endphp
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center {{ $lead->is_kyc_completed ? 'bg-green-500' : 'bg-amber-500' }}">
                                @if($lead->is_kyc_completed)
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-2xl font-black uppercase">{{ $lead->is_kyc_completed ? 'Verified' : 'Pending' }}</p>
                                <p class="text-indigo-300 font-medium">Standard KYC Compliance</p>
                                @if(!$kycMandatory)
                                    <span class="text-[10px] bg-indigo-800 px-2 py-0.5 rounded-full font-black tracking-widest text-indigo-300 uppercase mt-1 inline-block">Optional</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-sm text-indigo-400 leading-relaxed italic border-t border-indigo-800 pt-4">
                            {{ $kycMandatory ? 'KYC is mandatory for this account. Leads cannot be marked as Paid Client without full verification.' : 'KYC is currently optional. Agents can onboard clients without full verification, but data collection is still recommended for audit trails.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- RPM Tab -->
            <div x-show="activeTab === 'rpm'" class="space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6 border-b pb-2">Risk Profiling Dashboard</h3>
                    
                    @if($lead->complianceSteps->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-slate-400 italic">No RPM steps generated yet for this lead.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($lead->complianceSteps as $step)
                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $step->completed_at ? 'bg-green-100 text-green-600' : 'bg-slate-200 text-slate-400' }}">
                                            @if($step->completed_at)
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-900">{{ $step->step_name }}</p>
                                            @if($step->completed_at)
                                                <p class="text-[10px] text-slate-400 font-bold uppercase">Done by {{ $step->user->name }} on {{ $step->completed_at->format('d M, Y') }}</p>
                                            @else
                                                <p class="text-[10px] text-amber-500 font-black uppercase tracking-wider">Required Step</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(!$step->completed_at)
                                        @if(Auth::user()->can('rbac.compliance.complete_step') || $lead->assigned_to === Auth::id())
                                            <form action="{{ route('leads.compliance.step', $lead) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="step_key" value="{{ $step->step_key }}">
                                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-black shadow-md hover:bg-indigo-700 transition">
                                                    Mark Done
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Documents Tab -->
            <div x-show="activeTab === 'docs'" class="space-y-6">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest border-b pb-2">Compliance Vault</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @forelse($lead->documents as $doc)
                            <div class="p-4 border border-slate-100 rounded-2xl flex items-center gap-3 bg-slate-50">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-indigo-600">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-black text-slate-900 truncate">{{ $doc->name }}</p>
                                    <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">{{ $doc->created_at->format('d M, Y') }}</p>
                                </div>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="p-2 text-slate-400 hover:text-indigo-600 transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center">
                                <p class="text-slate-400 italic">No documents uploaded yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
