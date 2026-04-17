@extends('layouts.app')
@section('title', 'Media Gallery')

@section('content')
<x-page-header title="Media Gallery" subtitle="Upload and manage institutional photos and documents.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.media.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Upload Media
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search by filename…" class="flex-1 min-w-[180px]"/>
    <x-select name="type">
        <option value="">All Types</option>
        <option value="gallery" {{ request('type') === 'gallery' ? 'selected' : '' }}>Gallery</option>
        <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Document</option>
    </x-select>
</x-search-filter>

{{-- Image Grid --}}
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    @forelse($media as $item)
    <div class="group relative bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-200">
        @if(Str::startsWith($item->mime_type ?? '', 'image'))
            <img src="{{ $item->url }}"
                 alt="{{ $item->file_name }}"
                 class="w-full h-28 object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-28 bg-gray-50 flex items-center justify-center">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        @endif
        <div class="p-2">
            <p class="text-[10px] text-gray-500 truncate" title="{{ $item->file_name ?? $item->title }}">{{ $item->file_name ?? $item->title }}</p>
            <div class="flex items-center justify-between mt-1.5">
                <x-badge :color="$item->file_type === 'gallery' ? 'blue' : 'gray'" class="text-[9px]">{{ $item->file_type }}</x-badge>
                <form method="POST" action="{{ route('admin.media.destroy', $item) }}">
                    @csrf @method('DELETE')
                    <button type="submit" title="Delete"
                            onclick="return confirm('Delete this file?')"
                            class="text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full">
        <x-empty-state title="No media files yet"
                       message="Upload images or documents to the gallery."
                       action="{{ route('admin.media.create') }}"
                       actionLabel="Upload Media"/>
    </div>
    @endforelse
</div>

@if($media->hasPages())
<div class="mt-4">{{ $media->withQueryString()->links() }}</div>
@endif
@endsection
