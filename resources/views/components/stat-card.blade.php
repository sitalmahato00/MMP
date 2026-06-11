@props(['title', 'value', 'icon', 'tone' => 'blue', 'trend' => null])

@php
    $toneMap = [
        'blue'    => ['accent' => '#1D4ED8', 'iconBg' => '#EFF6FF', 'iconColor' => '#1D4ED8', 'bar' => '#1D4ED8'],
        'emerald' => ['accent' => '#059669', 'iconBg' => '#ECFDF5', 'iconColor' => '#059669', 'bar' => '#059669'],
        'violet'  => ['accent' => '#7C3AED', 'iconBg' => '#F5F3FF', 'iconColor' => '#7C3AED', 'bar' => '#7C3AED'],
        'amber'   => ['accent' => '#D97706', 'iconBg' => '#FFFBEB', 'iconColor' => '#D97706', 'bar' => '#D97706'],
        'rose'    => ['accent' => '#E11D48', 'iconBg' => '#FFF1F2', 'iconColor' => '#E11D48', 'bar' => '#E11D48'],
        'cyan'    => ['accent' => '#0891B2', 'iconBg' => '#ECFEFF', 'iconColor' => '#0891B2', 'bar' => '#0891B2'],
    ];
    $t = $toneMap[$tone] ?? $toneMap['blue'];
@endphp

<div class="relative overflow-hidden bg-white" style="border: 1px solid #DCE3EB; border-radius: 4px; padding: 1rem;">
    {{-- Top accent bar --}}
    <div class="absolute top-0 left-0 right-0 h-0.5" style="background-color: {{ $t['bar'] }};"></div>

    <div class="flex items-start justify-between">
        <div class="flex h-9 w-9 items-center justify-center rounded" style="background-color: {{ $t['iconBg'] }};">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $t['iconColor'] }};">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
        @if($trend)
            <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs font-semibold"
                  style="{{ $trend['positive'] ? 'background-color: #DCFCE7; color: #15803D;' : 'background-color: #FEE2E2; color: #B91C1C;' }}">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M{{ $trend['positive'] ? '12 7a1 1 0 110 2H9.414l5.293 5.293a1 1 0 01-1.414 1.414L8 10.414v2.586a1 1 0 11-2 0V9a1 1 0 011-1h5z' : '8 13a1 1 0 110-2h2.586L5.293 5.707a1 1 0 011.414-1.414L10 9.586V7a1 1 0 112 0v5a1 1 0 01-1 1H8z' }}" clip-rule="evenodd" />
                </svg>
                {{ $trend['value'] }}
            </span>
        @endif
    </div>

    <div class="mt-3">
        <div class="text-2xl font-bold tracking-tight" style="color: #111827;">{{ $value }}</div>
        <p class="mt-0.5 text-xs font-semibold uppercase tracking-wider" style="color: #6B7280;">{{ $title }}</p>
    </div>
</div>
