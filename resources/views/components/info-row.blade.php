{{--
    x-info-row
    A consistent label-value display row for detail/show pages.
    Props:
      label   - Left-side label (required)
      full    - Spans full width (no two-column split) (default: false)
--}}
@props(['label', 'full' => false])

<div class="{{ $full ? '' : 'sm:grid sm:grid-cols-3 sm:gap-4' }} py-3 border-b border-gray-50 last:border-0">
    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center">{{ $label }}</dt>
    <dd class="{{ $full ? 'mt-1' : 'mt-1 sm:mt-0 sm:col-span-2' }} text-sm text-gray-800 font-medium">
        {{ $slot }}
    </dd>
</div>
