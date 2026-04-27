{{-- Sidebar Section Label Partial (accordion, scroll-persistent) --}}
{{-- Variables: $label, $sectionId (optional, auto-derived from label) --}}
@php $sectionId = 'nav-sec-' . \Illuminate\Support\Str::slug($label); @endphp

<div class="pt-4 pb-1 px-1"
     x-data="{ open: localStorage.getItem('{{ $sectionId }}') !== 'false' }"
     x-init="$watch('open', value => localStorage.setItem('{{ $sectionId }}', value))"
     @sidebar-section-open.window="if ($event.detail !== '{{ $sectionId }}') open = false">
    <button type="button"
        x-show="!sidebarCollapsed"
        @click="open = !open; if (open) $dispatch('sidebar-section-open', '{{ $sectionId }}')"
        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left transition-colors duration-200 hover:bg-white/5">
        <p class="text-sm font-normal text-black dark:text-white transition-colors">{{ $label }}</p>
        <svg class="h-3.5 w-3.5 flex-shrink-0 text-black dark:text-white transition-transform duration-200" :class="open ? 'rotate-0' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open || sidebarCollapsed" x-cloak class="space-y-1">
