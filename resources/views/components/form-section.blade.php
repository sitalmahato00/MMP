{{--
    x-form-section
    Props:
      title     - Section heading (required)
      subtitle  - Small helper text (optional)
    Slot:
      $slot     - Form fields content
--}}
@props(['title', 'subtitle' => null])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
        <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-xs text-gray-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
