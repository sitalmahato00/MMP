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
    {{ $attributes->merge(['class' => 'w-full border border-slate-300 bg-white rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0e4fee]/15 focus:border-[#0e4fee] transition duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}
>
