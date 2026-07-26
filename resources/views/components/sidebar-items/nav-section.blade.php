{{-- Sidebar Section Label — Nepal Government Portal Style --}}
{{-- Variables: $label --}}

<div class="pt-2 pb-0.5 px-1"
     x-show="navSearch === '' || Array.from($el.querySelectorAll('[data-nav-label]')).some(el => el.dataset.navLabel.includes(navSearch.toLowerCase()))">
    {{-- Section label: hidden when sidebar is collapsed --}}
    <div class="px-3 py-1" x-show="!sidebarCollapsed && navSearch === ''" x-cloak>
        <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: rgba(255,255,255,0.40);">{{ $label }}</p>
    </div>
    {{-- Divider shown instead of label when collapsed --}}
    <div x-show="sidebarCollapsed && navSearch === ''" x-cloak class="my-1 mx-2 border-t" style="border-color: rgba(255,255,255,0.10);"></div>
    <div class="mt-0.5 space-y-0.5">
