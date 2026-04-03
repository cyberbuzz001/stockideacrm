@props(['title', 'value', 'icon' => 'chart-bar', 'color' => 'indigo', 'link' => null, 'trend' => null, 'trendUp' => true])

@php
    $gradients = [
        'indigo' => 'from-[#6366F1] to-[#4F46E5]',
        'emerald' => 'from-[#10B981] to-[#059669]',
        'rose' => 'from-[#F43F5E] to-[#E11D48]',
        'amber' => 'from-[#F59E0B] to-[#D97706]',
        'blue' => 'from-[#3B82F6] to-[#2563EB]',
    ];
    $gradient = $gradients[$color] ?? $gradients['indigo'];
@endphp

<div class="glass-card p-6 rounded-3xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl group relative overflow-hidden animate-fade-in-up border-none">
    @if($link)
        <a href="{{ $link }}" class="absolute inset-0 z-10"></a>
    @endif

    <div class="flex items-start justify-between relative z-20">
        <div class="space-y-1">
            <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">{{ $title }}</p>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">{{ $value }}</h3>
            
            @if($trend !== null)
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full {{ $trendUp ? 'bg-emerald-100/50 text-emerald-600' : 'bg-rose-100/50 text-rose-600' }}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $trendUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                        </svg>
                    </span>
                    <span class="text-[11px] font-bold {{ $trendUp ? 'text-emerald-500' : 'text-rose-500' }}">{{ $trend }}%</span>
                    <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">vs yesterday</span>
                </div>
            @endif
        </div>

        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 transform group-hover:rotate-12 transition-transform duration-500">
            @if($icon === 'users')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            @elseif($icon === 'phone')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            @elseif($icon === 'currency-rupee')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif($icon === 'check-circle')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif($icon === 'target')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            @elseif($icon === 'clock')
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            @endif
        </div>
    </div>

    <!-- Decorative background element -->
    <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-gradient-to-br {{ $gradient }} opacity-[0.03] rounded-full blur-2xl"></div>
</div>