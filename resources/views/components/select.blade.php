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
    {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000] transition-all duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}>
    {{ $slot }}
</select>
