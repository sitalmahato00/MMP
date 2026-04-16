@extends('layouts.guest')
@section('title', 'News & Events')
@section('meta_description', 'Latest news, events and happenings at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #8B0000;">
                <span>📰 News & Events</span>
                <span class="text-red-200 text-xs">{{ $notices->total() }} articles</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0">
                @forelse($notices as $notice)
                    @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                    <div class="group border-b border-gray-100 last:border-0 hover:bg-red-50/50 transition-colors">
                        <div class="flex gap-5 p-5">
                            {{-- Date Box --}}
                            <div class="flex-shrink-0 w-16 h-16 text-white flex flex-col items-center justify-center rounded shadow-sm" style="background-color: #8B0000;">
                                <span class="text-[10px] font-bold uppercase">{{ optional($noticeDate)->format('M') }}</span>
                                <span class="text-xl font-black leading-tight">{{ optional($noticeDate)->format('d') }}</span>
                                <span class="text-[9px]">{{ optional($noticeDate)->format('Y') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 group-hover:text-[#8B0000] transition-colors text-[15px] leading-snug mb-2">
                                    {{ $notice->title }}
                                </h3>
                                @if($notice->content)
                                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-2 mb-3">
                                        {{ Str::limit(strip_tags($notice->content), 180) }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="text-xs text-gray-400">{{ optional($noticeDate)->format('F d, Y') }}</span>
                                    @if($notice->attachment)
                                        <a href="{{ asset('storage/'.$notice->attachment) }}" class="text-xs text-red-700 hover:underline flex items-center gap-1 font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Attachment
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">📰</p>
                        <p class="font-semibold text-gray-500">No news or events published yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Check back soon for the latest updates from MMP.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $notices->links() }}
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    @foreach([
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Exam Schedules & Results', 'href' => route('public.notices', ['type' => 'exam'])],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Our Programs', 'href' => route('public.departments')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                            <span class="text-red-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="section-header" style="background-color: #8B0000;">📋 Notice Board</div>
                <div class="bg-white border border-gray-200 border-t-0 p-4 space-y-3">
                    <p class="text-sm text-gray-500">Visit the official notice board for all institutional announcements.</p>
                    <a href="{{ route('public.notices') }}" class="inline-block text-sm font-bold text-[#8B0000] hover:underline">View All Notices »</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
