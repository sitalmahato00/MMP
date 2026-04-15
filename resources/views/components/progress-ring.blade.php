{{--
    x-progress-ring
    SVG circular progress indicator.
    Props:
      value   - Percentage value 0-100 (required)
      size    - Circle diameter in px (default: 120)
      color   - Stroke color (default: #8B0000)
      label   - Center label below percentage (optional)
--}}
@props(['value' => 0, 'size' => 120, 'color' => '#8B0000', 'label' => null])

@php
    $strokeWidth = 4;
    $radius = ($size / 2) - $strokeWidth;
    $circumference = round(2 * M_PI * $radius, 2);
    $offset = round($circumference - ($value / 100) * $circumference, 2);
@endphp

<div class="flex flex-col items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px; position: relative;">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="transform -rotate-90">
        <circle
            cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
            fill="none" stroke="#e5e7eb" stroke-width="{{ $strokeWidth }}"/>
        <circle
            cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
            fill="none"
            stroke="{{ $color }}"
            stroke-width="{{ $strokeWidth }}"
            stroke-linecap="round"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $offset }}"
            style="transition: stroke-dashoffset 0.6s ease;"/>
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-2xl font-black text-gray-900">{{ $value }}%</span>
        @if($label)
            <span class="text-[9px] uppercase font-bold text-gray-400 tracking-wider mt-0.5">{{ $label }}</span>
        @endif
    </div>
</div>
