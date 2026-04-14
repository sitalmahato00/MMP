<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            {{ $header }}
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $footer }}
        </div>
    @endif
</div>
