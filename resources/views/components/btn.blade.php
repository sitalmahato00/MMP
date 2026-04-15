{{--
    x-btn
    Consistent button / link button.
    Props:
      href      - If set, renders as <a> tag, else as <button>
      variant   - 'primary' | 'secondary' | 'danger' | 'ghost'  (default: primary)
      type      - Button type (default: button)
      size      - 'sm' | 'md' | 'lg' (default: md)
    Slot:
      $slot     - Button label / content
--}}
@props([
    'href'    => null,
    'variant' => 'primary',
    'type'    => 'button',
    'size'    => 'md',
])

@php
    $variants = [
        'primary'   => 'bg-[#8B0000] text-white hover:bg-[#6a0000] shadow-sm',
        'secondary' => 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 shadow-sm',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
        'ghost'     => 'text-gray-500 hover:text-gray-700 hover:bg-gray-100',
    ];
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-5 py-2.5',
        'lg' => 'text-base px-6 py-3',
    ];
    $base    = 'inline-flex items-center gap-2 font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#8B0000]/40';
    $classes = "$base {$variants[$variant]} {$sizes[$size]}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
