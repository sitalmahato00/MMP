<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden']) }} style="border: 1px solid #DCE3EB; border-radius: 4px; box-shadow: 0 1px 2px rgba(11,46,107,0.05);">
    @if(isset($header))
        <div class="flex items-center justify-between px-5 py-3" style="border-bottom: 1px solid #DCE3EB; background-color: #F8FAFC;">
            {{ $header }}
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-5 py-3" style="border-top: 1px solid #F0F3F7; background-color: #F8FAFC;">
            {{ $footer }}
        </div>
    @endif
</div>
