@extends('layouts.app')
@section('title', 'Gallery')

@section('content')
<x-page-header title="Gallery" subtitle="Browse department images in gallery view."
               back="{{ route('hod.media.index') }}"/>

{{-- Filters --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="min-w-[18rem] flex-1">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search gallery images..." 
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
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
            <a href="{{ route('hod.media.gallery') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
        </div>
    </form>
</div>

{{-- Gallery Grid --}}
@if($images->count() > 0)
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach($images as $image)
            <a href="{{ asset('storage/' . $image->file_path) }}" target="_blank"
               class="group relative aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 transition hover:shadow-xl">
                <img src="{{ asset('storage/' . $image->file_path) }}" 
                     alt="{{ $image->title }}" 
                     class="h-full w-full object-cover transition group-hover:scale-105">
                
                {{-- Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 transition group-hover:opacity-100">
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <p class="truncate text-sm font-semibold text-white">{{ $image->title }}</p>
                        <p class="mt-0.5 text-xs text-white/80">
                            {{ number_format($image->size / 1024, 1) }} KB • {{ $image->uploader?->name }}
                        </p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $images->links() }}
    </div>
@else
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-800">No images found</h3>
        <p class="mt-1 text-sm text-slate-500">Upload some images to see them here.</p>
    </div>
@endif
@endsection
