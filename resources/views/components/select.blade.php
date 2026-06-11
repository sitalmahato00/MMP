{{--
    x-select
    Consistent styled select dropdown.
    Props:
      name      - Select name (required)
      required  - Boolean (default: false)
    Slot:
      $slot     - <option> elements
--}}
@props(['name', 'required' => false])

<select
    name="{{ $name }}"
    id="{{ $name }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'w-full border border-slate-300 bg-white rounded-[8px] px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0e4fee]/15 focus:border-[#0e4fee] transition duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}>
    {{ $slot }}
</select>
