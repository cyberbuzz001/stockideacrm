<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Compose Internal Mail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <form action="{{ route('internal-mails.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Recipients -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Recipients
                        </label>
                        <select name="recipients" id="recipients" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                            <option value="all">📢 All Employees</option>
                            <optgroup label="Individual Employees">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->role }})
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('recipients')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Category
                        </label>
                        <select name="category" required
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Subject
                        </label>
                        <input type="text" name="subject" required placeholder="e.g., KYC Compliance Update Required"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium">
                        @error('subject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Body -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Message Body
                        </label>
                        <textarea name="body" rows="10" required placeholder="Write your message here..."
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-medium"></textarea>
                        @error('body')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Attachment -->
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">
                            Attachment (Optional)
                        </label>
                        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx"
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-slate-500">Allowed: PDF, DOC, DOCX, XLS, XLSX (Max 10MB)</p>
                        @error('attachment')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('internal-mails.index') }}"
                            class="px-6 py-3 bg-slate-100 text-slate-700 rounded-xl font-bold hover:bg-slate-200 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-colors">
                            📤 Send Mail
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>