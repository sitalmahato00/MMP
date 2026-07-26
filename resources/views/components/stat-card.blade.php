@props(['title', 'value', 'icon' => null, 'color' => 'blue', 'tone' => null, 'trend' => null])

@php
    // Support both 'color' (old) and 'tone' (old) prop names
    $c = $color ?? $tone ?? 'blue';
    $solidColors = [
        'blue'    => '#2563EB',
        'green'   => '#10B981',
        'emerald' => '#10B981',
        'purple'  => '#7C3AED',
        'violet'  => '#7C3AED',
        'orange'  => '#F97316',
        'amber'   => '#F59E0B',
        'cyan'    => '#06B6D4',
        'indigo'  => '#4F46E5',
        'teal'    => '#0F766E',
        'red'     => '#DC2626',
        'rose'    => '#DC2626',
        'slate'   => '#475569',
        'gray'    => '#475569',
        'sky'     => '#0284C7',
        'zinc'    => '#52525B',
    ];
    $bgColor = $solidColors[$c] ?? $solidColors['blue'];

    // Extract icon path from HTML string if passed as full SVG
    $iconPath = '';
    if ($icon) {
        preg_match_all('/d=["\']([^"\']+)["\']/', $icon, $m);
        $iconPath = implode(' ', $m[1] ?? []);
    }
@endphp

<div class="kpi-card relative overflow-hidden rounded-lg p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
     style="background-color: {{ $bgColor }};">
    <div class="relative flex items-center justify-between gap-3">
        {{-- Icon --}}
        @if($icon)
        <div class="kpi-icon flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-white/20">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @foreach($m[1] ?? [] as $d)
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d }}"/>
                @endforeach
            </svg>
        </div>
        @endif

        {{-- Value + label --}}
        <div class="{{ $icon ? 'text-right' : '' }} flex-1 min-w-0">
            <p class="text-xl font-black text-white leading-tight tracking-tight">{{ $value }}</p>
            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/90 truncate">{{ $title }}</p>
        </div>
    </div>

    @if($trend)
    <div class="relative mt-2.5 flex items-center gap-1">
        <span class="inline-flex items-center gap-1 rounded bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white">
            {{ $trend['value'] }}
        </span>
    </div>
    @endif
</div>
