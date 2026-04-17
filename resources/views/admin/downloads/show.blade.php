@extends('layouts.app')
@section('title', $download->title)

@section('content')
<x-page-header :title="$download->title" subtitle="{{ $download->is_public ? 'Public' : 'Private' }} download resource">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.downloads.index') }}" variant="secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Downloads
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">File Preview</h2>
            </div>
            <div class="p-6">
                @if($download->file_path)
                    <x-file-preview
                        :url="route('admin.downloads.file', $download)"
                        :filename="$download->file_name ?? basename($download->file_path)"
                        :type="$download->file_type ?? pathinfo($download->file_path, PATHINFO_EXTENSION)"
                    />
                @else
                    <p class="text-sm text-gray-400">No file attached.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Details</h2>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase">Category</div>
                    <div class="mt-1 text-gray-900">{{ $download->category ?? 'general' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase">Visibility</div>
                    <div class="mt-1">
                        <x-badge :color="$download->is_public ? 'green' : 'amber'">{{ $download->is_public ? 'Public' : 'Private' }}</x-badge>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase">Uploaded</div>
                    <div class="mt-1 text-gray-900">{{ bsDate($download->created_at, 'd F Y') }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase">Filename</div>
                    <div class="mt-1 text-gray-900 break-all">{{ $download->file_name ?? basename($download->file_path) }}</div>
                </div>
                @if($download->description)
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase">Description</div>
                        <div class="mt-1 text-gray-700 leading-relaxed">{{ $download->description }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <a href="{{ route('admin.downloads.file', $download) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#8B0000] hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download File
            </a>
        </div>
    </div>
</div>
@endsection