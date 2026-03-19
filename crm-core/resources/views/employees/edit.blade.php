<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Employee: <span class="text-indigo-600">{{ $employee->name }}</span>
            </h2>
            <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-gray-800">
                ← Back to Team
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul class="list-disc ml-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('employees.update', $employee->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                            <select name="role" id="role"
                                class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                required>
                                <option value="BA" {{ old('role', $employee->role) === 'BA' ? 'selected' : '' }}>Business Advisor (BA)</option>
                                <option value="SBA" {{ old('role', $employee->role) === 'SBA' ? 'selected' : '' }}>Senior Business Advisor (SBA)</option>
                                <option value="Manager" {{ old('role', $employee->role) === 'Manager' ? 'selected' : '' }}>Manager</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $employee->name) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required>
                        </div>

                        <!-- Change Password (Optional) -->
                        <div class="mb-4 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">🔒 Change Password <span class="font-normal text-gray-400">(leave blank to keep current)</span></p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                                    <input type="password" name="password"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                                    <input type="password" name="password_confirmation"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </div>
                            </div>
                        </div>

                        <!-- Reports To -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Reports To (Optional)</label>
                            <select name="parent_id"
                                class="shadow border rounded w-full py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">None (Direct Report)</option>
                                @foreach($supervisors as $sup)
                                    <option value="{{ $sup->id }}" {{ old('parent_id', $employee->parent_id) == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }} ({{ $sup->role }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Assign a Manager or SBA as the supervisor.</p>
                        </div>

                        <!-- Active Status Toggle -->
                        <div class="mb-6 flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                    {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Account Active</p>
                                <p class="text-xs text-gray-400">If disabled, the user cannot log in to the CRM.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-4">
                            <a href="{{ route('employees.index') }}"
                                class="text-sm text-gray-600 underline">Cancel</a>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg focus:outline-none shadow-sm transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>