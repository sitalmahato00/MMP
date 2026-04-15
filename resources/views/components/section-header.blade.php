{{--
    x-section-header
    Used to divide content sections within a page (not the page title).
    Props:
      title   - Section label (required)
      subtitle - Optional description
    Slot:
      $actions - Optional right-side actions
--}}
@props(['title', 'subtitle' => null])

<div class="flex items-end justify-between mb-4 pb-3 border-b border-gray-100">
    <div>
        <h2 class="text-base font-bold text-gray-800">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
    <div class="flex items-center gap-2">
        {{ $actions }}
    </div>
    @endif
</div>
