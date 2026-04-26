{{--
    x-error
    Display validation error message for a specific field.
    Props:
      field - The field name to check for errors (required)
--}}
@props(['field'])

@error($field)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
