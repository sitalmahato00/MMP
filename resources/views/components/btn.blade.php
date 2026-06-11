{{--
    x-btn
    Consistent button / link button.
    Props:
      href      - If set, renders as <a> tag, else as <button>
      variant   - 'primary' | 'secondary' | 'danger' | 'ghost'  (default: primary)
      type      - Button type (default: button)
      size      - 'sm' | 'md' | 'lg' (default: md)
      icon      - Icon name (optional)
    Slot:
      $slot     - Button label / content
--}}
@props([
    'href'    => null,
    'variant' => 'primary',
    'type'    => 'button',
    'size'    => 'md',
    'icon'    => null,
])

@php
    $variants = [
        'primary'   => 'bg-[#00d14d] text-white hover:bg-[#00b53c] shadow-sm',
        'success'   => 'bg-[#00d14d] text-white hover:bg-[#00b53c] shadow-sm',
        'view'      => 'bg-[#0347f0] text-white hover:bg-[#0a39cc] shadow-sm',
        'edit'      => 'bg-[#F59E0B] text-white hover:bg-[#D97706] shadow-sm',
        'secondary' => 'bg-white text-[#334155] border border-slate-300 hover:bg-slate-100 shadow-sm',
        'danger'    => 'bg-[#DC2626] text-white hover:bg-[#B91C1C] shadow-sm',
        'ghost'     => 'text-[#0B2E6B] hover:text-[#0A295F] hover:bg-slate-100',
    ];
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-5 py-2.5',
        'lg' => 'text-base px-6 py-3',
    ];
    $base    = 'inline-flex items-center gap-2 font-semibold rounded-[8px] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#0B2E6B]/20';
    $classes = "$base {$variants[$variant]} {$sizes[$size]}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            @if($icon === 'arrow-left')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            @elseif($icon === 'eye')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            @endif
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            @if($icon === 'arrow-left')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            @elseif($icon === 'eye')
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            @endif
        @endif
        {{ $slot }}
    </button>
@endif
