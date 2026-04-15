{{--
    x-tab-panel
    A single panel inside x-tab-group.
    Props:
      :index  - The 0-based index of this panel (required)
--}}
@props(['index'])

<div x-show="activeTab === {{ $index }}"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-cloak>
    {{ $slot }}
</div>
