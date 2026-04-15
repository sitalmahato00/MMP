{{--
    x-empty-state
    Consistent empty content placeholder.
    Props:
      title   - Heading text (required)
      message - Sub-description (optional)
      action  - Button href (optional)
      actionLabel - Button text (default: "Add New")
    Slot:
      $icon   - Optional SVG icon override
--}}
@props(['title', 'message' => null, 'action' => null, 'actionLabel' => 'Add New'])

<div class="flex flex-col items-center justify-center py-16 text-center">
    @if(isset($icon))
        <div class="mb-4 text-gray-200">
            {{ $icon }}
        </div>
    @else
        <svg class="mx-auto w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
    @endif
    <h3 class="text-sm font-bold text-gray-500">{{ $title }}</h3>
    @if($message)
        <p class="text-xs text-gray-400 mt-1 max-w-xs">{{ $message }}</p>
    @endif
    @if($action)
        <div class="mt-4">
            <x-btn href="{{ $action }}" size="sm">{{ $actionLabel }}</x-btn>
        </div>
    @endif
</div>
