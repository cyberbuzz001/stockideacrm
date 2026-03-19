<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AI Call Analysis') }} <span
                class="bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full ml-2">BETA</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Upload Section -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100">
                        <div class="p-6">
                            <h3 class="font-bold text-lg text-slate-900 mb-4">Analyze Call Recording</h3>
                            <p class="text-sm text-slate-500 mb-6">Upload an audio conversation to get instant insights
                                on sentiment, keywords, and a summary.</p>

                            <form action="{{ route('ai.analyze') }}" method="POST" enctype="multipart/form-data"
                                class="space-y-6">
                                @csrf

                                <div
                                    class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center hover:bg-slate-50 transition-colors cursor-pointer relative">
                                    <input type="file" name="audio_file"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        onchange="document.getElementById('fileName').innerText = this.files[0].name"
                                        required>
                                    <div class="space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-slate-300"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                        </svg>
                                        <p class="font-bold text-indigo-600">Click to Upload Audio</p>
                                        <p class="text-xs text-slate-400">MP3, WAV, M4A (Max 10MB)</p>
                                        <p id="fileName" class="text-sm font-medium text-slate-700 mt-2"></p>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition-colors flex justify-center items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Generate AI Insights
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Coming Soon -->
                    <div
                        class="mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-3xl p-6 text-white overflow-hidden relative">
                        <div class="relative z-10">
                            <h3 class="font-bold text-lg mb-2">Real-time Coaching</h3>
                            <p class="text-indigo-100 text-sm mb-4">Coming soon: Live guidance for agents during calls
                                based on real-time transcription.</p>
                            <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm">Phase
                                2</span>
                        </div>
                        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    </div>
                </div>

                <!-- Results Section -->
                <div>
                    @if(session('analysis_result'))
                        @php $result = session('analysis_result'); @endphp
                        <div
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-100 h-full animate-fade-in-up">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="font-bold text-lg text-slate-900">Analysis Results</h3>
                                    <span
                                        class="bg-green-100 text-green-700 px-3 py-1 rounded-lg text-xs font-bold">Completed</span>
                                </div>

                                <!-- Score -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-slate-50 p-4 rounded-xl text-center">
                                        <p class="text-xs text-slate-500 uppercase font-black tracking-widest mb-1">
                                            Sentiment</p>
                                        <p class="text-xl font-bold text-green-600">{{ $result['sentiment'] }}</p>
                                    </div>
                                    <div class="bg-slate-50 p-4 rounded-xl text-center">
                                        <p class="text-xs text-slate-500 uppercase font-black tracking-widest mb-1">Score
                                        </p>
                                        <p class="text-xl font-bold text-indigo-600">{{ $result['sentiment_score'] }}/100
                                        </p>
                                    </div>
                                </div>

                                <!-- Keywords -->
                                <div class="mb-6">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Detected
                                        Keywords</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($result['keywords'] as $keyword)
                                            <span
                                                class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold">#{{ $keyword }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="mb-6">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">AI Summary
                                    </p>
                                    <p class="text-slate-600 text-sm leading-relaxed bg-slate-50 p-4 rounded-xl">
                                        {{ $result['summary'] }}
                                    </p>
                                </div>

                                <!-- Transcript Snippet -->
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Transcript
                                        Snippet</p>
                                    <div
                                        class="bg-slate-900 text-slate-300 p-4 rounded-xl text-xs font-mono whitespace-pre-line">
                                        {{ $result['transcript'] }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div
                            class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-3xl h-full flex flex-col items-center justify-center p-10 text-center text-slate-400">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="font-medium">No analysis results yet</p>
                            <p class="text-sm mt-1">Upload a recording to see AI insights here.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>