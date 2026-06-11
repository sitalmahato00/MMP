{{--
    x-info-card
    Props:
      title - Card title (optional)
      icon  - Optional icon HTML or slot
--}}
@props(['title' => null])

<div class="rounded-[8px] border border-slate-200 bg-white p-4 shadow-sm">
    @if($title)
        <div class="mb-3 flex items-center justify-between gap-2 text-sm font-semibold text-slate-900">
            <span>{{ $title }}</span>
            {{ $icon ?? '' }}
        </div>
    @endif
    <div class="text-sm leading-6 text-slate-600">
        {{ $slot }}
    </div>
</div>
