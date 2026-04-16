{{--
    x-file-preview
    Inline preview for attachments (images, PDFs, videos).
    Props:
      url      - Full URL of the file
      filename - Display name
      type     - File extension (pdf, jpg, png, etc.)
--}}
@props(['url', 'filename' => '', 'type' => ''])

@php
    $ext = strtolower($type ?: pathinfo($filename, PATHINFO_EXTENSION));
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    $isPdf = $ext === 'pdf';
    $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
@endphp

<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true"
            class="inline-flex items-center gap-1.5 text-sm text-[#8B0000] hover:text-[#a00000] hover:underline transition-colors">
        @if($isImage)
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        @elseif($isPdf)
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        @elseif($isVideo)
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        @else
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        @endif
        <span class="truncate max-w-[120px]">{{ $filename ?: 'Preview' }}</span>
    </button>

    {{-- Preview Modal --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             @click.self="open = false" @keydown.escape.window="open = false">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden" @click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-sm font-semibold text-gray-800 truncate">{{ $filename }}</span>
                        <span class="text-xs text-gray-400 uppercase flex-shrink-0">{{ $ext }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ $url }}" download class="text-xs text-gray-400 hover:text-[#8B0000] transition-colors px-2 py-1 rounded hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                        <button @click="open = false" class="text-gray-300 hover:text-gray-600 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Content --}}
                <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-gray-50">
                    @if($isImage)
                        <img src="{{ $url }}" alt="{{ $filename }}" class="max-w-full max-h-[70vh] object-contain rounded shadow-sm">
                    @elseif($isPdf)
                        <iframe src="{{ $url }}" class="w-full h-[75vh] rounded border border-gray-200"></iframe>
                    @elseif($isVideo)
                        <video controls class="max-w-full max-h-[70vh] rounded shadow-sm">
                            <source src="{{ $url }}">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-sm text-gray-400 mb-3">Preview not available for this file type</p>
                            <a href="{{ $url }}" download class="inline-flex items-center gap-2 px-4 py-2 bg-[#8B0000] text-white text-sm rounded-lg hover:bg-[#a00000] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download File
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>
