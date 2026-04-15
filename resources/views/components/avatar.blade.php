{{--
    x-avatar
    User avatar with image or initials fallback.
    Props:
      src     - Image URL (optional)
      name    - User name for initials fallback (required)
      size    - 'xs' | 'sm' | 'md' | 'lg' | 'xl' (default: md)
      color   - Fallback background color (default: uses accent based on name hash)
--}}
@props(['src' => null, 'name', 'size' => 'md'])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
    ];
    $cls = $sizes[$size] ?? $sizes['md'];

    // Deterministic color from name
    $colors = ['bg-[#8B0000]','bg-blue-600','bg-emerald-600','bg-purple-600','bg-amber-600','bg-pink-600','bg-indigo-600','bg-teal-600'];
    $colorIndex = abs(crc32($name)) % count($colors);
    $bgColor = $colors[$colorIndex];
    $initials = collect(explode(' ', $name))->take(2)->map(fn($p) => strtoupper($p[0] ?? ''))->implode('');
@endphp

@if($src)
    <img src="{{ $src }}"
         alt="{{ $name }}"
         {{ $attributes->merge(['class' => "$cls rounded-full object-cover ring-2 ring-white flex-shrink-0"]) }}>
@else
    <div {{ $attributes->merge(['class' => "$cls $bgColor rounded-full flex items-center justify-center font-bold text-white flex-shrink-0 ring-2 ring-white"]) }}>
        {{ $initials }}
    </div>
@endif
