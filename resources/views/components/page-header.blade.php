{{--
    x-page-header
    Props:
      title     - Page title (required)
      subtitle  - Small description under title (optional)
      back      - URL for back arrow (optional)
    Slots:
      $actions  - Buttons/links on the right side (optional)
--}}
@props(['title', 'subtitle' => null, 'back' => null])

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
        @if($back)
            <a href="{{ $back }}"
               class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif
        <div>
            <h1 class="text-2xl font-black text-gray-900 leading-tight">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 flex-shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
