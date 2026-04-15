{{--
    x-input
    Consistent styled text/email/number/date input.
    Props:
      type      - Input type (default: text)
      name      - Input name (required)
      value     - Pre-filled value (optional)
      placeholder - Placeholder text (optional)
      required  - Boolean (default: false)
--}}
@props(['type' => 'text', 'name', 'value' => null, 'placeholder' => '', 'required' => false])

<input
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $name }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000] transition-all duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}
>
