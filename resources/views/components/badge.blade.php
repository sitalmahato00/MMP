{{--
    x-badge
    Consistent status/label pill badge.
    Props:
      color   - 'green' | 'red' | 'blue' | 'yellow' | 'purple' | 'gray' (default: gray)
      dot     - Show leading dot indicator (default: false)
--}}
@props(['color' => 'gray', 'dot' => false])

@php
    $colors = [
        'green'  => 'bg-green-50 text-green-700',
        'red'    => 'bg-red-50 text-red-700',
        'blue'   => 'bg-blue-50 text-blue-700',
        'yellow' => 'bg-yellow-50 text-yellow-700',
        'purple' => 'bg-purple-50 text-purple-700',
        'orange' => 'bg-orange-50 text-orange-700',
        'gray'   => 'bg-gray-100 text-gray-600',
    ];
    $dots = [
        'green'  => 'bg-green-500',
        'red'    => 'bg-red-500',
        'blue'   => 'bg-blue-500',
        'yellow' => 'bg-yellow-500',
        'purple' => 'bg-purple-500',
        'orange' => 'bg-orange-500',
        'gray'   => 'bg-gray-400',
    ];
    $cls = $colors[$color] ?? $colors['gray'];
    $dotCls = $dots[$color] ?? $dots['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide whitespace-nowrap $cls"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotCls }} flex-shrink-0"></span>
    @endif
    {{ $slot }}
</span>
