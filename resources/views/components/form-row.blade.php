{{--
    x-form-row
    A consistent 1 or 2 column grid row inside a form section.
    Props:
      cols  - 1 or 2 (default: 2)
    Slot:
      $slot - The form fields
--}}
@props(['cols' => 2])

<div class="{{ $cols == 2 ? 'grid grid-cols-1 md:grid-cols-2 gap-5' : 'space-y-5' }}">
    {{ $slot }}
</div>
