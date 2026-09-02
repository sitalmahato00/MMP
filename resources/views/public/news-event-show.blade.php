@extends('layouts.guest')
@section('title', $notice->title)
@section('breadcrumb', true)

@section('content')
<div class="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg bg-white shadow-md">
                {{-- Featured Cover Image at Top --}}
                @php
                    $firstImg = $notice->attachments->where('is_image', true)->first();
                    $primaryCoverImg = $firstImg?->url ?? ($notice->attachment && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $notice->attachment) ? asset('storage/' . $notice->attachment) : null);
                @endphp
                @if($primaryCoverImg)
                    <div class="w-full bg-slate-900 border-b border-gray-200 overflow-hidden flex items-center justify-center max-h-[440px]">
                        <img src="{{ $primaryCoverImg }}" alt="{{ $notice->title }}" class="w-full h-auto max-h-[440px] object-contain">
                    </div>
                @endif

                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded border border-blue-100 bg-blue-50 px-2 py-1 text-xs font-bold uppercase text-blue-700">{{ $notice->type }}</span>
                        @if($notice->department)
                            <span class="rounded border border-blue-100 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">{{ $notice->department->name }}</span>
                        @endif
                    </div>

                    <h1 class="mb-4 text-2xl font-bold text-gray-900 md:text-3xl">{{ $notice->title }}</h1>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                        @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ bsDate($noticeDate, 'Y, F d') }}</span>
                        </div>

                        @if($notice->author)
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>{{ $notice->author->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Media Gallery (Images & Videos) --}}
                @php
                    $images = $notice->attachments->where('is_image', true);
                    $videos = $notice->attachments->whereIn('file_type', ['mp4', 'webm', 'mov', 'avi']);
                @endphp
                
                @if($images->count() > 0 || $videos->count() > 0)
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Media Gallery
                        </h3>
                        
                        {{-- Images Grid --}}
                        @if($images->count() > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                                @foreach($images as $image)
                                    <a href="{{ $image->url }}" target="_blank" class="group relative aspect-video rounded-lg overflow-hidden bg-gray-200 shadow-md hover:shadow-xl transition-all duration-300">
                                        <img src="{{ $image->url }}" alt="{{ $image->file_name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                            <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                            </svg>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        
                        {{-- Videos --}}
                        @if($videos->count() > 0)
                            <div class="space-y-4">
                                @foreach($videos as $video)
                                    <div class="rounded-lg overflow-hidden bg-black shadow-lg">
                                        <video controls class="w-full" preload="metadata">
                                            <source src="{{ $video->url }}" type="video/{{ $video->file_type }}">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="bg-gray-800 px-4 py-2 text-sm text-gray-300">
                                            {{ $video->file_name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="px-6 py-6">
                    <div class="prose prose-lg max-w-none">
                        {!! nl2br(e($notice->content)) !!}
                    </div>
                </div>

                @php
                    $otherAttachments = $notice->attachments->reject(function($attachment) {
                        return $attachment->is_image || in_array($attachment->file_type, ['mp4', 'webm', 'mov', 'avi']);
                    });
                @endphp
                
                @if($notice->attachment || $otherAttachments->count() > 0)
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Attachments
                        </h3>
                        <div class="space-y-3">
                            @if($notice->attachment)
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-blue-300 transition-colors">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900">Main Attachment</p>
                                        <p class="text-xs text-gray-500">Click to download</p>
                                    </div>
                                    <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank" class="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                                        Download
                                    </a>
                                </div>
                            @endif

                            @foreach($otherAttachments as $attachment)
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3 hover:border-blue-300 transition-colors">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                                        @if($attachment->is_pdf)
                                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->file_name }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($attachment->file_size)
                                                {{ number_format($attachment->file_size / 1024, 1) }} KB
                                            @endif
                                            @if($attachment->file_type)
                                                | {{ strtoupper($attachment->file_type) }}
                                            @endif
                                        </p>
                                    </div>
                                    <a href="{{ $attachment->url }}" target="_blank" class="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                                        Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            @if($relatedNotices->count() > 0)
                <div>
                    <div class="section-header" style="background-color: #003D82;">Related News & Events</div>
                    <div class="rounded-b-lg border border-t-0 border-gray-200 bg-white shadow-md">
                        @foreach($relatedNotices->take(5) as $relatedNotice)
                            <a href="{{ route('public.news-events.show', $relatedNotice->slug) }}" class="flex items-start gap-3 border-b border-gray-100 px-4 py-3 text-sm text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-800 last:border-0">
                                @php $relatedDate = $relatedNotice->published_at ?? $relatedNotice->created_at; @endphp
                                <div class="flex h-10 w-8 flex-shrink-0 flex-col items-center justify-center rounded text-center text-xs text-white" style="background-color: #003D82;">
                                    <span class="font-bold leading-none">{{ bsDate($relatedDate, 'd') }}</span>
                                    <span class="text-[8px] uppercase leading-none">{{ bsDate($relatedDate, 'M') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium leading-snug">{{ $relatedNotice->title }}</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="rounded bg-blue-50 px-1.5 py-0.5 text-xs font-bold uppercase text-blue-700">{{ $relatedNotice->type }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <div class="section-header" style="background-color: #003D82;">Quick Links</div>
                <div class="rounded-b-lg border border-t-0 border-gray-200 bg-white shadow-md">
                    @foreach([
                        ['label' => 'All News & Events', 'href' => route('public.news-events')],
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Departments', 'href' => route('public.departments')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 text-sm text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-800 last:border-0">
                            <span class="text-blue-600">&rsaquo;</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
