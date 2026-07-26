@extends('layouts.guest')
@section('title', 'Gallery — ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black text-gray-900 font-serif">{{ $department->name }} — Gallery</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Photos from department activities and events</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">{{ $media->count() }} photos</span>
                        <a href="{{ route('public.department.show', $department->slug) }}"
                           class="text-sm font-semibold text-[#003D82] hover:underline">← Back</a>
                    </div>
                </div>
            </div>

            {{-- Gallery Grid --}}
            @if($media->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
                 x-data="{ lightbox: false, activeImg: '', activeTitle: '' }">

                @foreach($media as $item)
                <div class="group relative aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-pointer shadow-sm hover:shadow-lg transition-all duration-200"
                     @click="lightbox = true; activeImg = '{{ $item->url }}'; activeTitle = '{{ addslashes($item->title ?? '') }}'">
                    <img src="{{ $item->url }}"
                         alt="{{ $item->title ?? 'Gallery photo' }}"
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-end p-3">
                        @if($item->title)
                        <p class="text-white text-xs font-medium line-clamp-2 leading-snug">{{ $item->title }}</p>
                        @endif
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="w-7 h-7 bg-white/90 rounded-lg flex items-center justify-center shadow">
                            <svg class="w-3.5 h-3.5 text-[#003D82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zm-4 0a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Lightbox --}}
                <div x-show="lightbox" x-cloak
                     @keydown.escape.window="lightbox = false"
                     @click.self="lightbox = false"
                     class="fixed inset-0 bg-black/90 z-50 flex items-center justify-center p-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100">
                    <div class="relative max-w-4xl w-full">
                        <button @click="lightbox = false"
                                class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <img :src="activeImg" :alt="activeTitle"
                             class="w-full max-h-[80vh] object-contain rounded-xl shadow-2xl">
                        <p x-show="activeTitle" x-text="activeTitle"
                           class="text-white text-sm text-center mt-3 font-medium"></p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
                <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="font-semibold text-gray-700">No photos available yet.</p>
                <p class="text-sm text-gray-500 mt-1">Gallery photos will appear here once uploaded.</p>
            </div>
            @endif

        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'gallery',
                'downloads'  => collect([]),
                'events'     => collect([]),
            ])
        </div>
    </div>
</div>
@endsection
