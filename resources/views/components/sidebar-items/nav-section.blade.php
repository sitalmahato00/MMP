{{-- Sidebar Section Label — MMP Portal Style --}}
{{-- Variables: $label --}}

<div class="pt-3 pb-0.5 px-1"
     x-show="navSearch === '' || Array.from($el.querySelectorAll('[data-nav-label]')).some(el => el.dataset.navLabel.includes(navSearch.toLowerCase()))">
    {{-- Section label: hidden when sidebar is collapsed --}}
    <div class="px-3 py-1" x-show="!sidebarCollapsed && navSearch === ''" x-cloak>
        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-300/60">{{ $label }}</p>
    </div>
    {{-- Divider shown instead of label when collapsed --}}
    <div x-show="sidebarCollapsed && navSearch === ''" x-cloak class="my-1.5 mx-2 border-t border-white/10"></div>
    <div class="mt-1 space-y-1">

