<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Lead: {{ $lead->name }}
            </h2>
            <a href="{{ route('leads.show', $lead) }}" class="text-blue-500 hover:underline">← Cancel</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('leads.update', $lead) }}">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $lead->name) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Mobile (Read Only)</label>
                            <input type="text" value="{{ $lead->mobile }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-400 leading-tight bg-gray-50"
                                readonly>
                            <p class="text-xs text-gray-500 mt-1">Mobile number cannot be changed once created.</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Location</label>
                            <input type="text" name="location" value="{{ old('location', $lead->location) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Demat Status</label>
                            <select name="demat_status" class="shadow border rounded w-full py-2 px-3">
                                <option value="Not Checked" {{ old('demat_status', $lead->demat_status) == 'Not Checked' ? 'selected' : '' }}>Not Checked</option>
                                <option value="Active" {{ old('demat_status', $lead->demat_status) == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('demat_status', $lead->demat_status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="No Demat" {{ old('demat_status', $lead->demat_status) == 'No Demat' ? 'selected' : '' }}>No Demat</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Lead Source</label>
                            <select name="source" class="shadow border rounded w-full py-2 px-3">
                                @foreach(['Direct', 'Social Media', 'Referral', 'Website', 'CSV Upload'] as $src)
                                    <option value="{{ $src }}" {{ old('source', $lead->source) == $src ? 'selected' : '' }}>
                                        {{ $src }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Segment / Remarks</label>
                        <textarea name="remarks"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight h-24">{{ old('remarks', $lead->remarks) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>