{{--
    x-search-filter
    Reusable search + filter bar for index pages.
    Props:
      action  - Form action URL (defaults to current URL)
      method  - Form method (default: GET)
    Slot:
      $slot   - Filter inputs (x-input, x-select, etc.)
      $extra  - Optional right-side content (e.g. export button)
--}}
@props(['action' => null, 'method' => 'GET'])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 px-4 py-3">
    <form method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
          action="{{ $action ?? request()->url() }}"
          class="flex flex-wrap items-center gap-3">
        @if(strtoupper($method) !== 'GET') @csrf @endif

        <div class="flex flex-wrap items-center gap-3 flex-1">
            {{ $slot }}
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            <x-btn type="submit" variant="secondary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </x-btn>
            @if(request()->anyFilled(array_keys(request()->except(['_token','page']))))
                <x-btn href="{{ request()->url() }}" variant="ghost" size="sm">Clear</x-btn>
            @endif
            @if(isset($extra))
                {{ $extra }}
            @endif
        </div>
    </form>
</div>
