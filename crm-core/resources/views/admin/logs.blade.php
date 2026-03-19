<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Error Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Recent Log Entries (Last 100)</h3>
                        <form action="{{ route('admin.logs.clear') }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to clear the logs?');">
                            @csrf
                            <button type="submit"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Clear Logs
                            </button>
                        </form>
                    </div>

                    <div
                        class="bg-slate-900 text-slate-200 p-4 rounded-lg font-mono text-sm overflow-x-auto h-96 overflow-y-scroll">
                        @forelse($logs as $log)
                            <div
                                class="mb-2 pb-2 border-b border-slate-700 whitespace-pre-wrap hover:bg-slate-800 p-1 transition-colors">
                                @php
                                    // Highlight Error levels
                                    $class = 'text-slate-300';
                                    if (str_contains($log, '.ERROR'))
                                        $class = 'text-red-400 font-bold';
                                    if (str_contains($log, '.WARNING'))
                                        $class = 'text-yellow-400';
                                    if (str_contains($log, '.INFO'))
                                        $class = 'text-blue-400';
                                @endphp
                                <span class="{{ $class }}">{{ $log }}</span>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 italic py-10">No logs found or file is empty.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>