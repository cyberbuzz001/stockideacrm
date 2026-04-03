@props(['href', 'active', 'icon', 'label', 'badge' => null, 'badgeId' => null, 'pulseAlert' => false, 'pulseId' => null])

@php
    $icons = [
        'layout'         => '<path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="M3 9V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4"/><path d="M9 22V9"/><path d="M15 22V9"/>',
        'users'          => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user-check'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
        'graduation-cap' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v5"/>',
        'credit-card'    => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
        'trending-up'    => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        'shield-check'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/>',
        'file-text'      => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="9" y2="9"/>',
        'mail'           => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'users-round'    => '<path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/>',
        'book-open'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'refresh-ccw'    => '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>',
        'bar-chart-3'    => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
        'brain'          => '<path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 4.44-2.54Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-4.44-2.54Z"/>',
        'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'target'         => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'calendar-clock' => '<path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5"/><path d="M17.5 17.5 16 16.5V14"/><circle cx="16" cy="16" r="6"/>',
        'fingerprint'    => '<path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.02-.3 3"/><path d="M7 22c-.62 0-1.12-.1-1.5-.2a3 3 0 0 1-1.5-5.5 15.6 15.6 0 0 1 .53-2.1c.32-1 .73-1.93 1.22-2.82A9 9 0 0 1 12 7c5 0 9 4 9 9"/><path d="M14 22c.7 0 1.3-.1 1.8-.3a6.8 6.8 0 0 0 2.2-6.5C18 10.6 15.3 8 12 8s-6 2.6-6 7.2c0 .4 0 .9.1 1.4"/><path d="M1.3 12.8A7.8 7.8 0 0 1 12 6c4.3 0 7.8 3.5 7.8 8s-3.5 8-7.8 8c-.6 0-1.1-.1-1.6-.2"/><path d="M9 10a.5.5 0 0 0 1 0"/><path d="M15 10a.5.5 0 0 0-1 0"/>',
        'history'        => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'alert-circle'   => '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
        'settings-2'     => '<path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>',
        'calendar-days'  => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
    ];
@endphp

<a href="{{ $href }}" 
   class="nav-item flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 {{ $active ? 'nav-item-active' : 'text-[#6B7280] hover:bg-[#F3F4F6] hover:text-[#111827]' }}">
    <svg class="nav-icon w-4 h-4 flex-shrink-0 {{ $active ? 'text-[#4F46E5]' : 'text-[#9CA3AF]' }}" 
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        {!! $icons[$icon] ?? '' !!}
    </svg>
    <span class="font-medium truncate">{{ $label }}</span>
    
    <span @if($badgeId) id="{{ $badgeId }}" @endif class="ml-auto bg-[#EF4444] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-2 ring-white {{ ($badge === null || $badge <= 0) ? 'hidden' : '' }}">
        {{ $badge ?? '' }}
    </span>
    
    <span @if($pulseId) id="{{ $pulseId }}" @endif class="ml-auto flex h-2 w-2 {{ !$pulseAlert ? 'hidden' : '' }}">
        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-[#EF4444] opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#EF4444]"></span>
    </span>
</a>
