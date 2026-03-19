<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Client: {{ $client->name }}
            </h2>
            <a href="{{ route('clients.show', $client) }}"
                class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-all">
                ← Back to Profile
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl p-8 border border-slate-100 mt-6">
                <form method="POST" action="{{ route('clients.update', $client) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Full
                                Name</label>
                            <input type="text" name="name" value="{{ old('name', $client->name) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Email
                                Address</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Location</label>
                            <input type="text" name="location" value="{{ old('location', $client->location) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Investment
                                Capacity</label>
                            <input type="text" name="investment_cap"
                                value="{{ old('investment_cap', $client->investment_cap) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Demat
                                ID</label>
                            <input type="text" name="demat_id" value="{{ old('demat_id', $client->demat_id) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label
                                class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">PAN
                                Number</label>
                            <input type="text" name="pan_number" value="{{ old('pan_number', $client->pan_number) }}"
                                class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label
                            class="block text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Complete
                            Address</label>
                        <textarea name="address" rows="3"
                            class="w-full rounded-2xl border-slate-100 bg-slate-50 font-bold text-slate-900 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $client->address) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 border-t border-slate-50 pt-8">
                        <a href="{{ route('clients.show', $client) }}"
                            class="px-6 py-3 bg-white text-slate-600 rounded-2xl font-bold text-sm border border-slate-200 hover:bg-slate-50 transition-all">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-8 py-3 bg-indigo-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>