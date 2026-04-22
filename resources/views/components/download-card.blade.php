{{--
    x-download-card
    Card display for a single download
    Props:
      download - Download model instance
--}}
@props(['download'])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden group">
    {{-- Image Preview --}}
    <div class="relative h-40 bg-gray-100 overflow-hidden">
        @if(in_array($download->file_type, ['jpg', 'jpeg', 'png']))
            <img 
                src="{{ $download->file_url }}" 
                alt="{{ $download->title }}" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-semibold text-gray-900 truncate flex-1">{{ $download->title }}</h3>
            @if($download->uploaded_by !== auth()->id())
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-semibold flex-shrink-0" title="Uploaded by another user">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Other
                </span>
            @endif
        </div>
        
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded">{{ $download->category ?? 'General' }}</span>
                <span>{{ number_format($download->file_size/1024, 1) }} KB</span>
            </div>
            <div class="text-xs text-gray-400">
                {{ bsDate($download->created_at, 'Y, F d') }}
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2">
            @php $canEdit = $download->uploaded_by === auth()->id(); @endphp
            
            {{-- View Button (for all) --}}
            <a href="{{ $download->file_url }}" target="_blank" class="flex-1 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors text-center border border-gray-200">
                <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
            
            @if($canEdit)
                {{-- Edit Button (own only) --}}
                <a href="{{ route('hod.downloads.edit', $download) }}" class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors text-center border border-blue-200">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                
                {{-- Delete Button (own only) --}}
                <form action="{{ route('hod.downloads.destroy', $download) }}" method="POST" class="flex-1" onsubmit="return confirm('Delete this file?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-red-200">
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
