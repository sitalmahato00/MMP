@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
])

@php
    $colors = [
        'blue' => 'text-blue-600 bg-blue-50',
        'red' => 'text-[#8B0000] bg-red-50',
        'green' => 'text-green-600 bg-green-50',
        'purple' => 'text-purple-600 bg-purple-50',
        'yellow' => 'text-yellow-600 bg-yellow-50',
    ];
    $colorClass = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
    <div>
        <p class="text-sm font-medium text-gray-500 mb-1">{{ $title }}</p>
        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
    </div>
    @if($icon)
    <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $colorClass }}">
        {!! $icon !!}
    </div>
    @endif
</div>
