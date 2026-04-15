{{--
    x-loader
    Full-page or inline loading spinner.
    Props:
      size    - 'sm' | 'md' | 'lg' (default: md)
      text    - Optional loading text below spinner
      overlay - If true, shows as full-page overlay (default: false)
--}}
@props(['size' => 'md', 'text' => null, 'overlay' => false])

@php
    $sizes = ['sm' => 'w-5 h-5', 'md' => 'w-8 h-8', 'lg' => 'w-12 h-12'];
    $spinClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($overlay)
<div class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm">
    <svg class="{{ $spinClass }} animate-spin text-[#8B0000]" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
    </svg>
    @if($text)
        <p class="mt-3 text-sm font-medium text-gray-500 animate-pulse">{{ $text }}</p>
    @endif
</div>
@else
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-2']) }}>
    <svg class="{{ $spinClass }} animate-spin text-[#8B0000]" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
    </svg>
    @if($text)
        <p class="text-xs font-medium text-gray-400 animate-pulse">{{ $text }}</p>
    @endif
</div>
@endif
