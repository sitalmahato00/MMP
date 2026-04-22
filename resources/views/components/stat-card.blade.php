@props(['title', 'value', 'icon', 'tone' => 'blue', 'trend' => null])

@php
    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100', 'bar' => 'bg-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100', 'bar' => 'bg-emerald-500'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100', 'bar' => 'bg-violet-500'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100', 'bar' => 'bg-amber-500'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-100', 'bar' => 'bg-rose-500'],
        'cyan'    => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'ring' => 'ring-cyan-100', 'bar' => 'bg-cyan-500'],
    ];
    $t = $toneMap[$tone] ?? $toneMap['blue'];
@endphp

<div class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
    <div class="flex items-start justify-between">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
            <svg class="h-4 w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
        @if($trend)
            <span class="inline-flex items-center gap-1 rounded-full {{ $trend['positive'] ? 'bg-emerald-50' : 'bg-rose-50' }} px-2 py-1">
                <svg class="h-3 w-3 {{ $trend['positive'] ? 'text-emerald-600' : 'text-rose-600' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M{{ $trend['positive'] ? '12 7a1 1 0 110 2H9.414l5.293 5.293a1 1 0 01-1.414 1.414L8 10.414v2.586a1 1 0 11-2 0V9a1 1 0 011-1h5z' : '8 13a1 1 0 110-2h2.586L5.293 5.707a1 1 0 011.414-1.414L10 9.586V7a1 1 0 112 0v5a1 1 0 01-1 1H8z' }}" clip-rule="evenodd" />
                </svg>
                <span class="text-xs font-semibold {{ $trend['positive'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $trend['value'] }}</span>
            </span>
        @endif
    </div>
    <div class="mt-3">
        <div class="flex items-baseline gap-1">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</span>
        </div>
        <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $title }}</p>
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
</div>
