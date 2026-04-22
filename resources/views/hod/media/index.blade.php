@extends('layouts.app')
@section('title', 'Gallery Library')

@section('content')
<div x-data="{ view: localStorage.getItem('hod_media_view') ?? 'cards', setView(v) { this.view = v; localStorage.setItem('hod_media_view', v); } }" class="space-y-6">
    <x-page-header title="Gallery Library" subtitle="Manage department gallery files and images."/>

    {{-- Stats Cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Files</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalFiles }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Size</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ number_format($totalSize / 1048576, 2) }} MB</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Images</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $imageFiles }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Documents</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $documentFiles }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Filters & Actions --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="min-w-[18rem] flex-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search files or titles..." 
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <div class="min-w-[12rem]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Type</label>
            <select name="type" onchange="this.form.submit()"
                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                <option value="">All Types</option>
                <option value="image" @selected(request('type') === 'image')>Images</option>
                <option value="document" @selected(request('type') === 'document')>Documents</option>
                <option value="video" @selected(request('type') === 'video')>Videos</option>
            </select>
        </div>

        <div class="min-w-[12rem]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">From Date</label>
            <x-bs-date-picker name="date_from" :value="request('date_from')" placeholder="From BS date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
        </div>

        <div class="min-w-[12rem]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">To Date</label>
            <x-bs-date-picker name="date_to" :value="request('date_to')" placeholder="To BS date" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="rounded-2xl bg-[#8B0000] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6d0000]">Filter</button>
            <a href="{{ route('hod.media.index') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('hod.media.gallery') }}" 
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Gallery View
            </a>
            <button type="button" onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-2xl bg-[#8B0000] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6d0000]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload Files
            </button>
        </div>
    </form>
</div>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-center gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-900">Showing {{ number_format($mediaFiles->total()) }} file{{ $mediaFiles->total() === 1 ? '' : 's' }}</p>
            <p class="text-xs text-slate-500">Toggle between gallery cards and table list views.</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-[#8B0000] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Cards
            </button>
            <button type="button" @click="setView('table')"
                    :class="view === 'table' ? 'bg-[#8B0000] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Table
            </button>
        </div>
    </div>
</div>

<div x-show="view === 'cards'" x-cloak>
    {{-- Media Grid --}}
@if($mediaFiles->count() > 0)
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @foreach($mediaFiles as $media)
            <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg"
                 x-data="{ showActions: false }">
                {{-- Preview --}}
                <div class="aspect-square bg-slate-100">
                    @if(str_starts_with($media->mime_type, 'image/'))
                        <img src="{{ asset('storage/' . $media->file_path) }}" 
                             alt="{{ $media->title }}" 
                             class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center">
                            <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-3">
                    <p class="truncate text-sm font-semibold text-slate-800" title="{{ $media->title }}">
                        {{ $media->title }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ number_format($media->size / 1024, 1) }} KB
                    </p>
                </div>

                {{-- Actions Overlay --}}
                <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition group-hover:opacity-100">
                    <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank"
                       class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-slate-700 transition hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('hod.media.destroy', $media) }}" 
                          onsubmit="return confirm('Delete this file?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 text-white transition hover:bg-red-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $mediaFiles->links() }}
    </div>
@else
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-800">No media files</h3>
        <p class="mt-1 text-sm text-slate-500">Upload your first file to get started.</p>
    </div>
@endif
</div>

<div x-show="view === 'table'" x-cloak>
    @if($mediaFiles->count() > 0)
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-[0.15em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Preview</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Size</th>
                        <th class="px-4 py-3">Uploaded</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($mediaFiles as $media)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 align-top">
                                @if(str_starts_with($media->mime_type, 'image/'))
                                    <img src="{{ asset('storage/' . $media->file_path) }}" alt="{{ $media->title }}" class="h-16 w-24 rounded-xl object-cover border border-slate-200" />
                                @else
                                    <div class="flex h-16 w-24 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 align-top">
                                <p class="font-semibold text-slate-900">{{ $media->title }}</p>
                                <p class="text-xs text-slate-500">{{ $media->file_name }}</p>
                            </td>
                            <td class="px-4 py-4 align-top text-slate-600">{{ ucfirst($media->file_type) }}</td>
                            <td class="px-4 py-4 align-top text-slate-600">{{ number_format($media->size / 1024, 1) }} KB</td>
                            <td class="px-4 py-4 align-top text-slate-600">{{ bsDate($media->created_at, 'Y, F d') }}</td>
                            <td class="px-4 py-4 align-top text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('hod.media.destroy', $media) }}" onsubmit="return confirm('Delete this file?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white hover:bg-red-700 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $mediaFiles->links() }}
        </div>
    @else
        <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-slate-800">No media files</h3>
            <p class="mt-1 text-sm text-slate-500">Upload your first file to get started.</p>
        </div>
    @endif
</div>

{{-- Upload Modal --}}
<div id="uploadModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     onclick="if(event.target === this) this.classList.add('hidden')">
    <div class="w-full max-w-lg rounded-2xl bg-white p-6" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800">Upload Files</h3>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('hod.media.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Files (max 10)</label>
                    <input type="file" name="files[]" multiple required accept="image/*,application/pdf,.doc,.docx"
                           class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-slate-500">Max 10MB per file</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Title (optional)</label>
                    <input type="text" name="title" 
                           class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Leave empty to use filename">
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6d0000]">
                        Upload
                    </button>
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
