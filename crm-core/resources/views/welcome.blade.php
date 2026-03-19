<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StockIdea CRM | AI-Powered Sales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,900&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="antialiased bg-slate-50 text-slate-900">
    <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">
        <!-- Background Decorative Blobs -->
        <div
            class="absolute top-0 -left-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob">
        </div>
        <div
            class="absolute top-0 -right-4 w-72 h-72 bg-indigo-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000">
        </div>

        <div class="z-10 text-center px-4">
            <div class="mb-8 inline-block">
                <div class="text-5xl md:text-7xl font-black text-indigo-600 tracking-tighter drop-shadow-sm">
                    STOCK<span class="text-slate-900">IDEA</span>
                </div>
            </div>

            <h1 class="text-2xl md:text-4xl font-bold tracking-tight text-slate-800 mb-4">
                The Modern Standard for <span
                    class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">AI-Powered
                    CRM</span>
            </h1>

            <p class="text-slate-500 max-w-lg mx-auto mb-10 text-lg">
                A high-performance workspace for sales teams. Real-time lead tracking, automated distribution, and
                performance analytics.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition transform hover:-translate-y-1">
                        Go to Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition transform hover:-translate-y-1">
                        Sign In to Portal
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="w-full sm:w-auto px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-2xl font-bold hover:bg-slate-50 transition transform hover:-translate-y-1">
                            Create Account
                        </a>
                    @endif
                @endauth
            </div>

            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto opacity-50">
                <div class="text-center">
                    <p class="text-2xl font-black text-indigo-900">99%</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Response Rate</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black text-indigo-900">2.4x</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Yield Increase</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black text-indigo-900">Real-time</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Lead Scoring</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-black text-indigo-900">Instant</p>
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Escalations</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-auto py-8 text-slate-400 text-xs font-medium">
            &copy; {{ date('Y') }} StockIdea Financial Services. All rights reserved.
        </div>
    </div>

    <style>
        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
</body>

</html>