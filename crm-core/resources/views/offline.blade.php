<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline | {{ $company_name }} CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background: #0f172a; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-slate-900 text-white selection:bg-indigo-500 selection:text-white">

    <div class="max-w-md w-full bg-slate-800 rounded-3xl p-8 shadow-2xl border border-slate-700 text-center relative overflow-hidden">
        <!-- Glow effect -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-1/2 bg-rose-500/20 blur-3xl rounded-full pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <div class="w-20 h-20 bg-slate-700/50 rounded-2xl flex items-center justify-center mx-auto shadow-inner border border-slate-600">
                <svg class="w-10 h-10 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238L5.3 11V5l4 4-4 414m-8.121 2.121a9 9 0 0112.728 0"/>
                </svg>
            </div>
            
            <div>
                <h1 class="text-2xl font-black tracking-tight text-white">You're Offline</h1>
                <p class="text-slate-400 mt-2 text-sm leading-relaxed">It seems your internet connection dropped. The CRM is running in cached fallback mode.</p>
            </div>

            <div class="bg-indigo-900/40 border border-indigo-500/30 rounded-xl p-4 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-indigo-300">Cached Views Available:</p>
                        <a href="/leads?tab=followup" class="text-xs text-indigo-400 font-medium hover:text-indigo-300 underline underline-offset-2 mt-1 block">Go to Follow-ups</a>
                    </div>
                </div>
            </div>

            <button onclick="window.location.reload()" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-3.5 px-4 rounded-xl transition-colors shadow-lg shadow-slate-900/20">
                Try Reloading
            </button>
        </div>
    </div>

</body>
</html>
