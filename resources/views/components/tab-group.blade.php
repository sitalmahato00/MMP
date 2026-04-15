{{--
    x-tab-group
    Alpine.js tab group wrapper.
    Props:
      :tabs   - Array of tab labels passed as PHP (e.g. ['Overview', 'Settings'])
      default - Active tab on load (default: 0)
--}}
@props(['tabs' => [], 'default' => 0])

<div x-data="{ activeTab: {{ $default }} }">
    {{-- Tab Headers --}}
    <div class="flex gap-1 border-b border-gray-100 mb-5 overflow-x-auto">
        @foreach($tabs as $i => $tab)
        <button
            @click="activeTab = {{ $i }}"
            :class="activeTab === {{ $i }} ? 'border-b-2 border-[#8B0000] text-[#8B0000] font-semibold' : 'text-gray-400 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-all duration-150 -mb-px">
            {{ $tab }}
        </button>
        @endforeach
    </div>

    {{-- Tab Panels --}}
    {{ $slot }}
</div>
