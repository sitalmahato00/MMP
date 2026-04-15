{{--
    x-dropdown-item
    A single item inside an x-dropdown menu.
    Props:
      href      - URL for link item (optional)
      type      - 'link' | 'button' | 'divider' (default: link)
      danger    - Makes the text red (default: false)
--}}
@props(['href' => null, 'type' => 'link', 'danger' => false])

@if($type === 'divider')
    <div class="my-1 border-t border-gray-100"></div>
@elseif($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => 'flex items-center gap-2.5 px-4 py-2 text-sm font-medium transition-colors duration-150 ' . ($danger ? 'text-red-600 hover:bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900')]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => 'w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-left transition-colors duration-150 ' . ($danger ? 'text-red-600 hover:bg-red-50' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900')]) }}>
        {{ $slot }}
    </button>
@endif
