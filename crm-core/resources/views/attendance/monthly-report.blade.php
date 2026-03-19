<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monthly Attendance Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Filters & Legend -->
                    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                        <div class="flex items-center gap-4">
                            <form method="GET" action="{{ route('attendance.report.monthly') }}"
                                class="flex items-center gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Select
                                        Month</label>
                                    <input type="month" name="month" value="{{ $month }}"
                                        class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                                </div>
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition mt-auto">
                                    View Report
                                </button>
                            </form>

                            <a href="{{ route('attendance.report.monthly.export', ['month' => $month]) }}"
                                class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-emerald-700 transition mt-auto flex items-center gap-2 whitespace-nowrap">
                                📥 Export to CSV
                            </a>
                        </div>

                        <div class="flex items-center gap-2 text-xs font-bold">
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded border border-green-200">P =
                                Present</span>
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded border border-red-200">A =
                                Absent</span>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded border border-yellow-200">L =
                                Leave</span>
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded border border-blue-200">HD = Half
                                Day</span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded border border-gray-200">- = No
                                Data</span>
                        </div>
                    </div>

                    <!-- Matrix Table -->
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left font-black text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 w-48 shadow-r">
                                        Employee</th>
                                    <th class="px-2 py-3 text-center font-black text-gray-500 w-16 bg-gray-100">Stats
                                    </th>

                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $date = $startOfMonth->copy()->day($d);
                                            $isWeekend = $date->isWeekend();
                                        @endphp
                                        <th
                                            class="px-1 py-2 text-center font-medium w-12 border-l {{ $isWeekend ? 'bg-gray-100 text-red-400' : 'text-gray-700' }}">
                                            <div>{{ $d }}</div>
                                            <div class="text-[9px] uppercase">{{ $date->format('D') }}</div>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($employees as $emp)
                                    @php
                                        $data = $reportData[$emp->id] ?? [];
                                        $counts = collect($data)->filter()->groupBy('type')->map(fn($g) => count($g));
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <!-- Employee Name (Sticky) -->
                                        <td
                                            class="px-4 py-3 whitespace-nowrap font-bold text-gray-900 sticky left-0 bg-white z-10 shadow-r">
                                            {{ $emp->name }}
                                        </td>

                                        <!-- Stats Summary -->
                                        <td class="px-2 py-3 text-center bg-gray-50 text-[10px] space-y-1">
                                            <div class="text-green-600 font-bold">P: {{ $counts->get('Present', 0) }}</div>
                                            <div class="text-red-500 font-bold">A: {{ $counts->get('Absent', 0) }}</div>
                                            <div class="text-blue-500 font-bold">L: {{ $counts->get('Leave', 0) }}</div>
                                        </td>

                                        <!-- Days Columns -->
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            @php
                                                $dayData = $data[$d] ?? null;
                                                $date = $startOfMonth->copy()->day($d);
                                                $isFuture = $date->isFuture();
                                                $isWeekend = $date->isWeekend();

                                                // Color Logic
                                                $bgColor = 'bg-white';
                                                $textColor = 'text-gray-300';
                                                $label = '-';
                                                $subLabel = '';

                                                if ($dayData) {
                                                    $type = $dayData['type'];
                                                    $hours = number_format($dayData['hours'], 1);

                                                    if ($type === 'Present') {
                                                        $bgColor = 'bg-green-50';
                                                        $textColor = 'text-green-700';
                                                        $label = 'P';
                                                        $subLabel = $hours . 'h';
                                                    } elseif ($type === 'Absent') {
                                                        $bgColor = 'bg-red-50';
                                                        $textColor = 'text-red-700';
                                                        $label = 'A';
                                                    } elseif ($type === 'Leave') {
                                                        $bgColor = 'bg-yellow-50';
                                                        $textColor = 'text-yellow-700';
                                                        $label = 'L';
                                                    } elseif ($type === 'Half Day') {
                                                        $bgColor = 'bg-blue-50';
                                                        $textColor = 'text-blue-700';
                                                        $label = 'HD';
                                                        $subLabel = $hours . 'h';
                                                    }
                                                } elseif ($isWeekend) {
                                                    $bgColor = 'bg-gray-50';
                                                    $textColor = 'text-gray-300';
                                                    $label = 'OFF';
                                                }
                                            @endphp
                                            <td class="px-1 py-2 text-center border-l {{ $bgColor }} hover:brightness-95 transition cursor-default"
                                                title="{{ $date->format('Y-m-d') }}">
                                                @if(!$isFuture)
                                                    <div class="font-black {{ $textColor }}">{{ $label }}</div>
                                                    @if($subLabel)
                                                        <div class="text-[9px] font-mono text-gray-500">{{ $subLabel }}</div>
                                                    @endif
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $daysInMonth + 2 }}"
                                            class="px-6 py-10 text-center text-gray-400 italic">
                                            No employees found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>