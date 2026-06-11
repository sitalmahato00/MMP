@props(['title', 'value', 'icon' => null, 'color' => 'blue', 'tone' => null, 'trend' => null])

@php
    // Support both 'color' (old) and 'tone' (old) prop names
    $c = $color ?? $tone ?? 'blue';
    $gradients = [
        'blue'    => ['from' => '#2563EB', 'to' => '#3B82F6'],
        'green'   => ['from' => '#10B981', 'to' => '#22C55E'],
        'emerald' => ['from' => '#10B981', 'to' => '#22C55E'],
        'purple'  => ['from' => '#7C3AED', 'to' => '#A855F7'],
        'violet'  => ['from' => '#7C3AED', 'to' => '#A855F7'],
        'orange'  => ['from' => '#F97316', 'to' => '#FB923C'],
        'amber'   => ['from' => '#F59E0B', 'to' => '#FBBF24'],
        'cyan'    => ['from' => '#06B6D4', 'to' => '#22D3EE'],
        'indigo'  => ['from' => '#4F46E5', 'to' => '#6366F1'],
        'teal'    => ['from' => '#0F766E', 'to' => '#14B8A6'],
        'red'     => ['from' => '#DC2626', 'to' => '#EF4444'],
        'rose'    => ['from' => '#DC2626', 'to' => '#EF4444'],
        'slate'   => ['from' => '#475569', 'to' => '#64748B'],
        'gray'    => ['from' => '#475569', 'to' => '#64748B'],
        'sky'     => ['from' => '#0284C7', 'to' => '#38BDF8'],
        'zinc'    => ['from' => '#52525B', 'to' => '#71717A'],
    ];
    $g = $gradients[$c] ?? $gradients['blue'];

    // Extract icon path from HTML string if passed as full SVG
    $iconPath = '';
    if ($icon) {
        preg_match_all('/d=["\']([^"\']+)["\']/', $icon, $m);
        $iconPath = implode(' ', $m[1] ?? []);
    }
@endphp

<div class="relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
     style="background: linear-gradient(135deg, {{ $g['from'] }}, {{ $g['to'] }});">
    {{-- Glassmorphism background circle --}}
    <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
    <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>

    <div class="relative flex items-center justify-between gap-3">
        {{-- Icon --}}
        @if($icon)
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
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
            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80 truncate">{{ $title }}</p>
        </div>
    </div>

    @if($trend)
    <div class="relative mt-2.5 flex items-center gap-1">
        <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-[11px] font-bold text-white">
            {{ $trend['value'] }}
        </span>
    </div>
    @endif
</div>
