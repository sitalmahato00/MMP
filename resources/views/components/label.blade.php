{{--
    x-label
    Form label component with optional required indicator.
    Props:
      for      - Input ID this label is for (required)
      required - Show red asterisk (default: false)
--}}
@props(['for', 'required' => false])

<label for="{{ $for }}" class="block text-sm font-medium text-gray-700 mb-1">
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-0.5">*</span>
    @endif
</label>
