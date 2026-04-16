{{--
    x-textarea
    Consistent styled textarea.
    Props:
      name      - Textarea name (required)
      rows      - Number of rows (default: 4)
      placeholder - Placeholder text (optional)
--}}
@props(['name', 'rows' => 4, 'placeholder' => '', 'value' => null])

@php
    // Decode once — Blade's {{ }} encodes the slot content, so we decode it back
    // to get the raw value before {{ old() }} re-encodes it for HTML.
    $slotValue = html_entity_decode((string) $slot, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $resolvedValue = trim($slotValue) !== '' ? $slotValue : $value;
@endphp

<textarea
    name="{{ $name }}"
    id="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000] transition-all duration-150 resize-none' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}>{{ old($name, $resolvedValue) }}</textarea>
