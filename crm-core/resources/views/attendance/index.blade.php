<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Smart Attendance Monitor') }}
        </h2>
    </x-slot>

    <div x-data="{ openAdminModal: false }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Summary Stats (Today) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-indigo-500">
                    <div class="text-xs font-bold text-gray-400 uppercase">Present Today</div>
                    <div class="text-2xl font-black text-gray-800">
                        {{ $stats['present'] }} / {{ $employees->count() }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <div class="text-xs font-bold text-gray-400 uppercase">Active Now</div>
                    <div class="text-2xl font-black text-gray-800">
                        {{ $stats['active'] }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-yellow-500">
                    <div class="text-xs font-bold text-gray-400 uppercase">Avg Productivity</div>
                    <div class="text-2xl font-black text-gray-800">
                        {{ number_format($stats['avg_productivity'], 0) }}%
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                    <div class="text-xs font-bold text-gray-400 uppercase">Absentees / Leave</div>
                    <div class="text-2xl font-black text-gray-800">
                         {{ $stats['absent'] }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Filters -->
                    <div
                        class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100 flex flex-wrap gap-4 items-end justify-between">
                        <form method="GET" action="{{ route('attendance.index') }}"
                            class="flex flex-wrap gap-4 w-full md:w-auto items-end">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Date</label>
                                <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}"
                                    class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Employee</label>
                                <select name="user_id"
                                    class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 w-40">
                                    <option value="">All Staff</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-bold hover:bg-black transition">
                                View Report
                            </button>
                            <a href="{{ route('attendance.index') }}"
                                class="text-xs text-gray-500 hover:text-gray-800 underline my-auto">Reset</a>
                        </form>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('attendance.report.monthly') }}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                Monthly Report
                            </a>
                            @if(auth()->user()->role === 'Admin')
                                <button @click="openAdminModal = true"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    Manage Attendance
                                </button>
                            @endif
                            <div class="text-right">
                                <div class="text-xs text-gray-400 font-bold uppercase">Office Hours</div>
                                <div class="text-sm font-bold text-gray-700">09:00 AM - 05:00 PM</div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 table-auto">
                            <thead>
                                <tr class="bg-gray-50 text-left">
                                    <th
                                        class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider rounded-l-lg">
                                        Employee</th>
                                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider">
                                        Type</th>
                                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider">
                                        Times</th>
                                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider">Work
                                        vs Idle</th>
                                    <th class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider">
                                        Productivity</th>
                                    <th
                                        class="px-4 py-3 text-xs font-black text-gray-400 uppercase tracking-wider rounded-r-lg">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($attendances as $att)
                                                            @php
                                                                // Logic for Duration Capping
                                                                $login = \Carbon\Carbon::parse($att->login_at);
                                                                $now = now();
                                                                $isToday = $att->date->isToday();

                                                                $logoutTime = $att->logout_at ? \Carbon\Carbon::parse($att->logout_at) : null;
                                                                $endTime = $logoutTime;

                                                                // If no logout yet
                                                                if (!$logoutTime) {
                                                                    if ($isToday) {
                                                                        $endTime = $now; // Still counting
                                                                    } else {
                                                                        // Forgot logout: Cap at last activity OR 5 PM
                                                                        $lastActivity = $att->last_activity_at ? \Carbon\Carbon::parse($att->last_activity_at) : null;
                                                                        $officeEnd = $att->date->copy()->setTime(17, 30, 0); // Cap at 5:30 PM per new rule

                                                                        // Use the latest reasonable time, capped at office end if no activity later
                                                                        $endTime = $lastActivity ?? $officeEnd;
                                                                        if ($endTime->gt($officeEnd)) {
                                                                            $endTime = $officeEnd;
                                                                        }
                                                                    }
                                                                }

                                                                $totalDurationMinutes = $endTime->diffInMinutes($login);
                                                                if ($totalDurationMinutes < 0)
                                                                    $totalDurationMinutes = 0;

                                                                $hours = floor($totalDurationMinutes / 60);
                                                                $minutes = $totalDurationMinutes % 60;

                                                                $effectiveHours = (float) $att->effective_hours;
                                                                $effectiveMinutes = $effectiveHours * 60;

                                                                $score = $att->productivity_score > 0 ? $att->productivity_score :
                                                                    ($totalDurationMinutes > 0 ? ($effectiveMinutes / $totalDurationMinutes * 100) : 0);

                                                                if ($score > 100)
                                                                    $score = 100;
                                                            @endphp
                                                            <tr class="hover:bg-gray-50 transition">
                                                                <!-- Employee Info -->
                                                                <td class="px-4 py-4 whitespace-nowrap">
                                                                    <div class="flex items-center">
                                                                        <div
                                                                            class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs mr-3">
                                                                            {{ substr($att->user->name, 0, 2) }}
                                                                        </div>
                                                                        <div>
                                                                            <div class="font-bold text-sm text-gray-900">{{ $att->user->name }}
                                                                            </div>
                                                                            <div class="text-[10px] text-gray-500">{{ $att->user->role }}</div>
                                                                            @if(!$isToday)
                                                                                <div class="text-[10px] text-gray-400 mt-1">
                                                                                    {{ $att->date->format('M d') }}</div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Attendance Type & Remarks -->
                                                                <td class="px-4 py-4 whitespace-nowrap">
                                                                    <span
                                                                        class="px-2 py-1 rounded text-[10px] font-bold uppercase
                                                                            {{ $att->attendance_type === 'Present' ? 'bg-green-100 text-green-700' :
                                    ($att->attendance_type === 'Absent' ? 'bg-red-100 text-red-700' :
                                        ($att->attendance_type === 'Leave' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700')) }}">
                                                                        {{ $att->attendance_type }}
                                                                    </span>
                                                                    @if($att->admin_remarks)
                                                                        <div class="text-[10px] text-gray-500 mt-1 italic max-w-[150px] truncate"
                                                                            title="{{ $att->admin_remarks }}">
                                                                            {{ $att->admin_remarks }}
                                                                        </div>
                                                                    @endif
                                                                </td>

                                                                <!-- Timings -->
                                                                <td class="px-4 py-4 whitespace-nowrap">
                                                                    @if(in_array($att->attendance_type, ['Absent', 'Leave']))
                                                                        <span class="text-xs text-gray-400 font-mono">--:--</span>
                                                                    @else
                                                                        <div class="text-xs">
                                                                            <div class="flex items-center gap-2 mb-1">
                                                                                <span class="w-12 text-gray-400 font-bold">IN:</span>
                                                                                <span
                                                                                    class="font-mono font-bold text-green-600">{{ $login->format('h:i A') }}</span>
                                                                            </div>
                                                                            <div class="flex items-center gap-2">
                                                                                <span class="w-12 text-gray-400 font-bold">OUT:</span>
                                                                                @if($logoutTime)
                                                                                    <span
                                                                                        class="font-mono font-bold text-red-600">{{ $logoutTime->format('h:i A') }}</span>
                                                                                @elseif($isToday && now()->format('H:i') < '17:30')
                                                                                    <span
                                                                                        class="text-[10px] bg-green-100 text-green-700 px-1 rounded animate-pulse">Running</span>
                                                                                @else
                                                                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1 rounded"
                                                                                        title="Shift Ended">17:30 PM</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="mt-1 text-[10px] text-gray-400 font-bold">
                                                                                Total: {{ $hours }}h {{ $minutes }}m
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </td>

                                                                <!-- Progress Bar -->
                                                                <td class="px-4 py-4 whitespace-nowrap w-48">
                                                                    @if(!in_array($att->attendance_type, ['Absent', 'Leave']))
                                                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1 overflow-hidden">
                                                                            <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $score }}%">
                                                                            </div>
                                                                        </div>
                                                                        <div class="flex justify-between text-[10px] font-bold">
                                                                            <span class="text-emerald-600">{{ number_format($effectiveHours, 1) }}h
                                                                                Work</span>
                                                                            <span
                                                                                class="text-gray-400">{{ number_format(($totalDurationMinutes - $effectiveMinutes) / 60, 1) }}h
                                                                                Idle</span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-xs text-gray-400">-</span>
                                                                    @endif
                                                                </td>

                                                                <!-- Productivity Score -->
                                                                <td class="px-4 py-4 whitespace-nowrap">
                                                                    @if(!in_array($att->attendance_type, ['Absent', 'Leave']))
                                                                        <div class="flex items-center">
                                                                            <span
                                                                                class="text-lg font-black {{ $score >= 80 ? 'text-emerald-500' : ($score >= 50 ? 'text-yellow-500' : 'text-red-500') }}">
                                                                                {{ number_format($score, 0) }}%
                                                                            </span>
                                                                        </div>
                                                                    @else
                                                                        <span class="text-lg font-black text-gray-300">0%</span>
                                                                    @endif
                                                                </td>

                                                                <!-- Status Badge -->
                                                                <td class="px-4 py-4 whitespace-nowrap">
                                                                    @if(in_array($att->attendance_type, ['Absent', 'Leave']))
                                                                        <span class="text-xs font-bold text-gray-400">{{ $att->attendance_type }}</span>
                                                                    @elseif($isToday && !$logoutTime && now()->format('H:i') < '17:30')
                                                                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                                                                                {{ $att->status === 'Active' ? 'bg-green-100 text-green-800' :
                                                                        ($att->status === 'Idle' ? 'bg-yellow-100 text-yellow-800' :
                                                                            'bg-red-100 text-red-800') }}">
                                                                                                        {{ $att->status ?? 'Online' }}
                                                                                                    </span>
                                                                                                    <div class="text-[10px] text-gray-400 mt-1 text-center">
                                                                                                        {{ $att->last_activity_at ? \Carbon\Carbon::parse($att->last_activity_at)->diffForHumans() : 'Just now' }}
                                                                                                    </div>
                                                                    @else
                                                                        <span
                                                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                                            Shift Ended
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">
                                            No attendance records found for {{ request('date', 'today') }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Management Modal -->
        <div x-show="openAdminModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="openAdminModal = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('attendance.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Update Attendance Record
                            </h3>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Employee</label>
                                <select name="user_id" required class="w-full rounded border-gray-300">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                                <input type="date" name="date" required value="{{ now()->toDateString() }}"
                                    class="w-full rounded border-gray-300">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Attendance Type</label>
                                <select name="attendance_type" required class="w-full rounded border-gray-300">
                                    <option value="Present">Present</option>
                                    <option value="Half Day">Half Day</option>
                                    <option value="Absent">Absent</option>
                                    <option value="Leave">Leave</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Remarks / Notes</label>
                                <textarea name="admin_remarks" rows="3" class="w-full rounded border-gray-300"
                                    placeholder="Reason for leave, half-day timings, etc..."></textarea>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Save Details
                            </button>
                            <button type="button" @click="openAdminModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>