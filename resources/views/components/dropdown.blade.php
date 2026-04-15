{{--
    x-dropdown
    Alpine.js powered dropdown menu.
    Props:
      align - 'left' | 'right' (default: right)
    Slots:
      $trigger - The button/link that opens the dropdown
      $slot    - The dropdown menu items
--}}
@props(['align' => 'right'])

@php
    $alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">

    {{-- Trigger --}}
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    {{-- Menu --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute z-50 mt-2 w-48 {{ $alignClass }} bg-white rounded-xl border border-gray-100 shadow-xl overflow-hidden"
         x-cloak>
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
