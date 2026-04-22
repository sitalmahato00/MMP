{{--
    x-download-table-row
    Single row for download table
    Props:
      download - Download model instance
--}}
@props(['download'])

<tr class="hover:bg-gray-50/70 transition-colors border-b border-gray-100">
    <td class="px-5 py-3.5">
        @if(in_array($download->file_type, ['jpg', 'jpeg', 'png']))
            <img 
                src="{{ $download->file_url }}" 
                alt="preview" 
                class="w-16 h-12 object-cover rounded-lg border border-gray-200"
            />
        @else
            <div class="w-16 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg border border-gray-200 flex items-center justify-center">
                @php
                    $fileType = strtolower($download->file_type);
                    $iconColor = match($fileType) {
                        'pdf' => 'text-red-600',
                        'doc', 'docx' => 'text-blue-600',
                        'xls', 'xlsx' => 'text-green-600',
                        'zip' => 'text-yellow-600',
                        default => 'text-gray-600',
                    };
                @endphp
                @if($fileType === 'pdf')
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <text x="8" y="16" font-size="8" font-weight="bold" fill="currentColor">PDF</text>
                    </svg>
                @elseif(in_array($fileType, ['doc', 'docx']))
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <text x="7" y="16" font-size="7" font-weight="bold" fill="currentColor">DOC</text>
                    </svg>
                @elseif(in_array($fileType, ['xls', 'xlsx']))
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <text x="7" y="16" font-size="7" font-weight="bold" fill="currentColor">XLS</text>
                    </svg>
                @elseif($fileType === 'zip')
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <path d="M9 9h6v2H9V9zm0 4h6v2H9v-2z"/>
                    </svg>
                @else
                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                @endif
            </div>
        @endif
    </td>
    <td class="px-5 py-3.5">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-900">{{ $download->title }}</span>
            @if($download->uploaded_by !== auth()->id())
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-semibold" title="Uploaded by another user">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Other
                </span>
            @endif
        </div>
    </td>
    <td class="px-5 py-3.5 text-gray-600">{{ $download->category ?? 'General' }}</td>
    <td class="px-5 py-3.5 text-gray-600">{{ number_format($download->file_size/1024, 1) }} KB</td>
    <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ bsDate($download->created_at, 'Y, F d') }}</td>
    <td class="px-5 py-3.5 text-right">
        @php $canEdit = $download->uploaded_by === auth()->id(); @endphp
        <div class="flex items-center justify-end gap-2">
            {{-- View Button (for all) --}}
            <a href="{{ $download->file_url }}" target="_blank" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Download">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            
            @if($canEdit)
                {{-- Edit Button (own only) --}}
                <a href="{{ route('hod.downloads.edit', $download) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                
                {{-- Delete Button (own only) --}}
                <form action="{{ route('hod.downloads.destroy', $download) }}" method="POST" class="inline" onsubmit="return confirm('Delete this file?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
