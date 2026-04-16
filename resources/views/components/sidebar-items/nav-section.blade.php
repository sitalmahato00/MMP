{{-- Sidebar Section Label Partial (collapsible, scroll-persistent, accordion) --}}
{{-- Variables: $label, $sectionId (optional, auto-derived from label) --}}
@php $sectionId = 'nav-sec-' . \Illuminate\Support\Str::slug($label); @endphp
<div class="pt-2 pb-0 px-1"
     x-data="{ open: localStorage.getItem('{{ $sectionId }}') !== 'false' }"
     x-init="$watch('open', v => localStorage.setItem('{{ $sectionId }}', v))"
     @sidebar-section-open.window="if ($event.detail !== '{{ $sectionId }}') open = false">
    <button @click="open = !open; if (!open) {} else $dispatch('sidebar-section-open', '{{ $sectionId }}')"
        class="w-full flex items-center justify-between px-3 py-2.5 rounded group cursor-pointer hover:bg-white/5 transition-colors">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-gray-300 transition-colors">{{ $label }}</p>
        <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200 flex-shrink-0" :class="open ? '' : '-rotate-90'"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-cloak>
