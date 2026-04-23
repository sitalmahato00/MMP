@extends('layouts.guest')
@section('title', 'News & Events')
@section('meta_description', 'Latest news, events and happenings at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #003D82;">
                <span>News & Events</span>
                <span class="text-xs text-blue-200">{{ $items->total() }} articles</span>
            </div>

            <div class="rounded-b-lg border border-t-0 border-gray-200 bg-white shadow-md">
                @forelse($items as $notice)
                    @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                    <a href="{{ route('public.news-events.show', $notice->slug) }}" class="group block border-b border-gray-100 transition-colors hover:bg-blue-50/50 last:border-0">
                        <div class="flex gap-5 p-5">
                            <div class="flex h-16 w-16 flex-shrink-0 flex-col items-center justify-center rounded text-white shadow-sm" style="background-color: #003D82;">
                                <span class="text-[9px] font-bold">{{ bsDate($noticeDate, 'Y') }}</span>
                                <span class="text-xl font-black leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                                <span class="text-[8px] font-bold uppercase">{{ bsDate($noticeDate, 'F') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="mb-2 text-[15px] font-bold leading-snug text-gray-900 transition-colors group-hover:text-[#003D82]">
                                    {{ $notice->title }}
                                </h3>
                                @if($notice->content)
                                    <p class="mb-3 line-clamp-2 text-sm leading-relaxed text-gray-600">
                                        {{ Str::limit(strip_tags($notice->content), 180) }}
                                    </p>
                                @endif
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $notice->type === 'event' ? 'border-teal-100 bg-teal-50 text-teal-700' : 'border-purple-100 bg-purple-50 text-purple-700' }}">
                                        {{ $notice->type === 'event' ? 'Event' : 'News' }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                                    @if($notice->attachment)
                                        <span class="flex items-center gap-1 text-xs font-semibold text-blue-700">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Attachment
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="py-16 text-center text-gray-400">
                        <p class="mb-4 text-4xl font-black tracking-[0.2em] text-gray-300">NEWS</p>
                        <p class="font-semibold text-gray-500">No news or events published yet.</p>
                        <p class="mt-2 text-sm text-gray-400">Check back soon for the latest updates from MMP.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        </div>

        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #003D82;">Quick Links</div>
                <div class="rounded-b-lg border border-t-0 border-gray-200 bg-white shadow-md">
                    @foreach([
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Exam Schedules & Results', 'href' => route('public.notices', ['type' => 'exam'])],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Our Programs', 'href' => route('public.departments')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 text-sm text-gray-700 transition-colors hover:bg-blue-50 hover:text-blue-800 last:border-0">
                            <span class="text-blue-600">&rsaquo;</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="section-header" style="background-color: #003D82;">Notice Board</div>
                <div class="space-y-3 border border-t-0 border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Visit the official notice board for all institutional announcements.</p>
                    <a href="{{ route('public.notices') }}" class="inline-block text-sm font-bold text-[#003D82] hover:underline">View All Notices &raquo;</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
