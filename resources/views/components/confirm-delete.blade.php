{{--
    x-confirm-delete
    Inline confirm-delete form using Alpine.js with animation.
    Renders a small "Delete" button that expands into a confirmation bar.
    Props:
      action  - Form DELETE route URL (required)
      name    - Resource name for confirmation text (required)
--}}
@props(['action', 'name'])

<div x-data="{ confirm: false }" class="flex items-center gap-2">
    {{-- Initial delete button --}}
    <button x-show="!confirm" @click="confirm = true" type="button"
            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-150"
            title="Delete {{ $name }}" x-cloak>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>

    {{-- Confirmation bar --}}
    <div x-show="confirm"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="flex items-center gap-1.5 bg-red-50 border border-red-100 rounded-lg px-2 py-1"
         x-cloak>
        <span class="text-xs font-semibold text-red-700 whitespace-nowrap">Delete?</span>
        <form method="POST" action="{{ $action }}">
            @csrf @method('DELETE')
            <button type="submit"
                    class="text-xs font-bold text-white bg-red-600 hover:bg-red-700 px-2 py-0.5 rounded transition-colors">
                Yes
            </button>
        </form>
        <button @click="confirm = false" type="button"
                class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-1 py-0.5 rounded transition-colors">
            No
        </button>
    </div>
</div>
