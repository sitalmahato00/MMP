{{-- Sidebar Nav Link Partial --}}
{{-- Variables: $href, $iconName, $label, $isActive, $accent, $badge (optional), $disabled (optional) --}}

@php
    $icons = [
        'home'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'calendar'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'building'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'academic-cap'    => '<path d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>',
        'users'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'user-group'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'briefcase'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'heart'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        'graduation-cap'  => '<path d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>',
        'clipboard-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'doc-text'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'chart-bar'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'bell'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'photo'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'download'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
        'shield'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'cog'             => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'doc-report'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'collection'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
        'external'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>',
        'funnel'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18l-7 8v5l-4 2v-7L3 5z"/>',
        'messages'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5M21 12c0 4.418-4.03 8-9 8a9.76 9.76 0 01-4-.82L3 20l1.09-3.27A7.52 7.52 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        'identification'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306-2.566 2-4 2"/>',
    ];

    $svgPath = $icons[$iconName] ?? $icons['doc-text'];
    $badgeValue = isset($badge) && $badge !== null && (int) $badge > 0 ? (int) $badge : null;
    $displayBadge = $badgeValue !== null ? ($badgeValue > 99 ? '99+' : (string) $badgeValue) : null;
    $isDisabled = isset($disabled) && $disabled;
    
    $baseClass = 'group relative flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-sm font-normal transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-white/10';
    if ($isDisabled) {
        $baseClass .= ' cursor-not-allowed opacity-50 text-slate-500';
    } else if ($isActive) {
        $baseClass .= ' text-white';
    } else {
        $baseClass .= ' text-slate-400 hover:text-white hover:bg-white/8';
    }
@endphp

@if($isDisabled)
    <div title="{{ $label }} (Coming Soon)"
         class="{{ $baseClass }} text-slate-400"
         :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : 'lg:justify-start'">
        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-slate-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {!! $svgPath !!}
            </svg>
        </span>

        <span x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-1 truncate transition-opacity duration-200">{{ $label }}</span>
        
        <span x-show="!sidebarCollapsed" x-cloak class="ml-auto inline-flex items-center rounded-full bg-slate-600/20 px-2 py-0.5 text-[10px] font-medium text-slate-400">
            Coming Soon
        </span>

        <span x-show="sidebarCollapsed" x-cloak class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-950 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl ring-1 ring-white/10 transition-opacity duration-200 group-hover:opacity-100 lg:block">
            {{ $label }} (Coming Soon)
        </span>
    </div>
@else
    <a href="{{ $href }}"
       title="{{ $label }}"
       class="{{ $baseClass }}"
       style="{{ $isActive ? 'background: ' . $accent . '1a;' : '' }}"
       :class="sidebarCollapsed ? 'lg:justify-center lg:gap-0' : 'lg:justify-start'">
        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-colors duration-200
            @if($isActive) text-white @else text-slate-400 group-hover:text-white @endif"
            style="{{ $isActive ? 'background: ' . $accent . '33;' : 'background: rgba(255,255,255,0.06);' }}">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {!! $svgPath !!}
            </svg>
            @if($badgeValue !== null)
                <span x-show="sidebarCollapsed" x-cloak class="absolute -right-0.5 -top-0.5 inline-flex h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-slate-950"></span>
            @endif
        </span>
        <span x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-1 truncate transition-opacity duration-200">{{ $label }}</span>
        @if($badgeValue !== null)
            <span x-show="!sidebarCollapsed" x-cloak class="ml-auto inline-flex min-w-6 items-center justify-center rounded-full bg-rose-500/15 px-2 py-0.5 text-[10px] font-semibold text-rose-100 ring-1 ring-rose-400/20">
                {{ $displayBadge }}
            </span>
        @endif
        @if($isActive)
            <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full" style="background: {{ $accent }};"></span>
        @endif
        <span x-show="sidebarCollapsed" x-cloak class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-950 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl ring-1 ring-white/10 transition-opacity duration-200 group-hover:opacity-100 lg:block">
            {{ $label }}
            @if($displayBadge)
                <span class="ml-2 inline-flex rounded-full bg-white/10 px-1.5 py-0.5 text-[10px] font-bold">{{ $displayBadge }}</span>
            @endif
        </span>
    </a>
@endif