{{--
    x-form-field
    Props:
      label     - Field label (required)
      name      - Input name for error lookup (required)
      required  - Show red asterisk (default: false)
      span      - 'full' for md:col-span-2 (optional)
    Slot:
      $slot     - The actual input/select/textarea element
--}}
@props(['label', 'name', 'required' => false, 'span' => null])

<div class="{{ $span === 'full' ? 'md:col-span-2' : '' }}">
    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-[0.22em] mb-2">
        {{ $label }}
        @if($required) <span class="text-red-600 ml-0.5">*</span> @endif
    </label>
    {{ $slot }}
    @error($name)
        <p class="text-sm text-red-600 mt-2 flex items-start gap-2">
            <svg class="mt-0.5 w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
