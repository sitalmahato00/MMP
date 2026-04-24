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

    <div class="space-y-4">
        @forelse($items as $notice)
            @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
            <a href="{{ route('public.news-events.show', $notice->slug) }}" class="block rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                <div class="flex gap-4">
                    <div class="flex h-16 w-16 flex-shrink-0 flex-col items-center justify-center rounded bg-blue-600 text-white">
                        <span class="text-xs font-semibold">{{ bsDate($noticeDate, 'F') }}</span>
                        <span class="text-2xl font-bold">{{ bsDate($noticeDate, 'd') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $notice->title }}</h3>
                        @if($notice->content)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ Str::limit(strip_tags($notice->content), 180) }}
                            </p>
                        @endif
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="px-2 py-1 rounded {{ $notice->type === 'event' ? 'bg-teal-100 text-teal-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $notice->type === 'event' ? 'Event' : 'News' }}
                            </span>
                            <span>{{ bsDate($noticeDate, 'Y, F d') }}</span>
                            @if($notice->attachment)
                                <span class="flex items-center gap-1 text-blue-600">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    Attachment
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="py-16 text-center">
                <p class="text-4xl mb-4">📰</p>
                <p class="font-semibold text-gray-500">No news or events published yet.</p>
                <p class="mt-2 text-sm text-gray-400">Check back soon for updates.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
</div>
@endsection
