<x-app-layout>
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Navigation -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('agent.learning.index') }}" class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-slate-900 line-clamp-1">{{ $module->title }}</h1>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-500">{{ $module->schedule_type }} Module</span>
                        <span class="text-[10px] text-slate-400">&bull; {{ strtoupper($module->file_type) }}</span>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('agent.learning.complete', $module) }}" method="POST" id="completion-form">
                @csrf
                <input type="hidden" name="time_spent" id="time-spent" value="0">
                <button type="button" id="mark-complete-btn" class="bg-slate-200 text-slate-400 flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all cursor-not-allowed" disabled>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span id="btn-text">Reading... (60s)</span>
                </button>
            </form>
        </div>

        <!-- Viewer Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden relative group">
            
            <!-- Anti-Download Overlay (transparent, catches right clicks) -->
            <div class="absolute inset-0 z-10 hidden" oncontextmenu="return false;"></div>

            <!-- Content Viewer -->
            <div class="w-full aspect-video bg-slate-900 flex items-center justify-center" style="min-height: 70vh;">
                @if($module->file_type === 'pdf')
                    <!-- PDF Viewer -->
                    <iframe src="{{ asset('storage/' . $module->file_path) }}#toolbar=0" class="w-full h-full border-0" oncontextmenu="return false;"></iframe>
                @elseif($module->file_type === 'image')
                    <!-- Image Viewer -->
                    <img src="{{ asset('storage/' . $module->file_path) }}" alt="{{ $module->title }}" class="max-w-full max-h-[80vh] object-contain pointer-events-none" oncontextmenu="return false;">
                @elseif($module->file_type === 'video')
                    <!-- Video Player with disabled download menu -->
                    <video controls controlsList="nodownload" oncontextmenu="return false;" class="w-full h-full outline-none">
                        <source src="{{ asset('storage/' . $module->file_path) }}" type="video/{{ pathinfo($module->file_path, PATHINFO_EXTENSION) }}">
                        Your browser does not support the video tag.
                    </video>
                @endif
            </div>
            
            <div class="bg-slate-50 border-t border-slate-100 p-3 text-center">
                <p class="text-[10px] text-slate-400 font-mono tracking-wider">DO NOT DOWNLOAD OR DISTRIBUTE. PROPERTY OF STOCKIDEA.</p>
            </div>
        </div>
    </div>

    <!-- Timer Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mark-complete-btn');
            const btnText = document.getElementById('btn-text');
            const timeSpentInput = document.getElementById('time-spent');
            const form = document.getElementById('completion-form');
            
            // Configuration
            const MIN_SECONDS = 60; // Require 60 seconds reading time
            
            // Check if already completed (by checking if the button text should say something else, 
            // but the controller handles if the user already completed it. For safety, let's just run the timer)
            
            let timeRemaining = MIN_SECONDS;
            let timeSpent = 0;
            
            // Track total time spent
            let trackInterval = setInterval(() => {
                timeSpent++;
                timeSpentInput.value = timeSpent;
            }, 1000);
            
            // Countdown for button
            let countdownInterval = setInterval(() => {
                timeRemaining--;
                
                if (timeRemaining > 0) {
                    btnText.textContent = `Reading... (${timeRemaining}s)`;
                } else {
                    clearInterval(countdownInterval);
                    
                    // Enable button
                    btn.disabled = false;
                    btn.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
                    btn.classList.add('bg-emerald-600', 'text-white', 'hover:bg-emerald-700', 'hover:shadow-md', 'cursor-pointer');
                    btnText.textContent = "Mark as Complete";
                    
                    // Change type to submit
                    btn.type = 'submit';
                }
            }, 1000);

            function stopTimers() {
                if (trackInterval) { clearInterval(trackInterval); trackInterval = null; }
                if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
            }

            // Avoid leaking timers when navigating away or tab is hidden
            window.addEventListener('pagehide', stopTimers);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) stopTimers();
            });
            
            // Prevent Ctrl+P / Cmd+P
            document.addEventListener("keydown", function(e) {
                if((e.ctrlKey || e.metaKey) && (e.key === "p" || e.charCode === 16 || e.charCode === 112 || e.keyCode === 80) ){
                    e.cancelBubble = true;
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    alert("Printing is disabled to protect company intellectual property.");
                }
            });
        });
    </script>
</x-app-layout>
