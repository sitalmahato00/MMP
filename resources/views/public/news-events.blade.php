@extends('layouts.guest')
@section('title', 'News & Events')
@section('meta_description', 'Latest news, events and happenings at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">News & Events</h1>
        <p class="text-sm text-gray-500">{{ $items->total() }} articles</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $notice)
            @php 
                $noticeDate = $notice->published_at ?? $notice->created_at;
                $firstImage = $notice->attachments->where('is_image', true)->first();
                $hasVideo = $notice->attachments->where('file_type', 'mp4')->count() > 0 || 
                           $notice->attachments->where('file_type', 'webm')->count() > 0;
            @endphp
            <a href="{{ route('public.news-events.show', $notice->slug) }}" class="group block rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                {{-- Image/Video Thumbnail --}}
                <div class="relative h-48 bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden">
                    @if($firstImage)
                        <img src="{{ $firstImage->url }}" alt="{{ $notice->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    
                    {{-- Video indicator --}}
                    @if($hasVideo)
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Date badge --}}
                    <div class="absolute top-3 left-3 bg-blue-600 text-white px-3 py-1.5 rounded-lg shadow-lg">
                        <div class="text-xs font-bold">{{ bsDate($noticeDate, 'F') }}</div>
                        <div class="text-2xl font-black leading-none">{{ bsDate($noticeDate, 'd') }}</div>
                    </div>
                    
                    {{-- Type badge --}}
                    <div class="absolute top-3 right-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $notice->type === 'event' ? 'bg-teal-500 text-white' : 'bg-purple-500 text-white' }} shadow-lg">
                            {{ $notice->type === 'event' ? 'Event' : 'News' }}
                        </span>
                    </div>
                </div>
                
                {{-- Content --}}
                <div class="p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        {{ $notice->title }}
                    </h3>
                    
                    @if($notice->content)
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            {{ Str::limit(strip_tags($notice->content), 120) }}
                        </p>
                    @endif
                    
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ bsDate($noticeDate, 'Y, F d') }}
                        </span>
                        
                        @if($notice->attachments->count() > 0)
                            <span class="flex items-center gap-1 text-blue-600 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $notice->attachments->count() }} {{ $notice->attachments->count() === 1 ? 'file' : 'files' }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 text-center">
                <p class="text-4xl mb-4">📰</p>
                <p class="font-semibold text-gray-500">No news or events published yet.</p>
                <p class="mt-2 text-sm text-gray-400">Check back soon for updates.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $items->links() }}
    </div>
</div>
@endsection
