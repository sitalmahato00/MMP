{{--
    x-textarea
    Consistent styled textarea.
    Props:
      name      - Textarea name (required)
      rows      - Number of rows (default: 4)
      placeholder - Placeholder text (optional)
--}}
@props(['name', 'rows' => 4, 'placeholder' => ''])

<textarea
    name="{{ $name }}"
    id="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000] transition-all duration-150 resize-none' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}>{{ old($name) }}</textarea>
