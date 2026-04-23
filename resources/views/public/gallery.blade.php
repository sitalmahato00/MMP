@extends('layouts.guest')
@section('title', 'Photo Gallery')
@section('meta_description', 'Browse photos from Manmohan Memorial Polytechnic campus life, events and activities.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #003D82;">
                <span>📷 Photo Gallery</span>
                <span class="text-blue-200 text-xs">{{ $media->count() }} photos</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0 p-5">
                @if($media->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3" x-data="{ lightbox: false, currentImage: '', currentTitle: '' }">
                        @foreach($media as $item)
                            <div class="group relative aspect-square overflow-hidden rounded bg-gray-100 cursor-pointer border border-gray-200 hover:border-[#003D82] transition-colors"
                                 @click="lightbox = true; currentImage = '{{ $item->url }}'; currentTitle = '{{ e($item->title) }}'">
                                <img src="{{ $item->url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-end">
                                    <div class="w-full p-2 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                        <p class="text-white text-xs font-medium truncate">{{ $item->title }}</p>
                                        @if($item->department)
                                            <p class="text-white/70 text-[10px]">{{ $item->department->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Lightbox Modal --}}
                        <div x-show="lightbox" x-cloak @click.self="lightbox = false" @keydown.escape.window="lightbox = false"
                             class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4" x-transition>
                            <button @click="lightbox = false" class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 z-50">&times;</button>
                            <div class="max-w-4xl max-h-[85vh] w-full">
                                <img :src="currentImage" :alt="currentTitle" class="max-w-full max-h-[80vh] mx-auto rounded shadow-2xl object-contain">
                                <p class="text-white text-center mt-3 text-sm font-medium" x-text="currentTitle"></p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">📷</p>
                        <p class="font-semibold text-gray-500">No photos available yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Gallery photos will be uploaded soon. Check back later!</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #003D82;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                    @foreach([
                        ['label' => 'News & Events', 'href' => route('public.news-events')],
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Our Programs', 'href' => route('public.departments')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                            <span class="text-blue-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

