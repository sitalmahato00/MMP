@extends('layouts.guest')
@section('title', 'Notices & Announcements')
@section('breadcrumb', true)

@section('content')
@php
    $activeType = $activeType ?? 'general';
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #8B0000;">
                <span>📋 All Notices & Announcements</span>
                <span class="text-red-200 text-xs">{{ $notices->total() }} notices</span>
            </div>
            <div class="bg-white border border-gray-200 border-t-0">
                <div class="flex border-b border-gray-200">
                    <a href="{{ route('public.notices', ['type' => 'general']) }}"
                       class="flex-1 text-center py-3 text-sm font-bold transition-colors {{ $activeType === 'general' ? 'bg-[#8B0000] text-white' : 'bg-white text-gray-700 hover:bg-red-50' }}">
                        Notice Board
                    </a>
                    <a href="{{ route('public.notices', ['type' => 'exam']) }}"
                       class="flex-1 text-center py-3 text-sm font-bold transition-colors {{ $activeType === 'exam' ? 'bg-[#8B0000] text-white' : 'bg-white text-gray-700 hover:bg-red-50' }}">
                        Exam Schedules & Results
                    </a>
                </div>
                <div class="mb-6">
                @forelse($notices as $notice)
                    @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                    <div class="group flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-red-50 transition-colors">
                        <div class="flex-shrink-0 w-12 h-12 text-white flex flex-col items-center justify-center rounded" style="background-color: #8B0000;">
                            <span class="text-[9px] font-bold uppercase">{{ optional($noticeDate)->format('M') }}</span>
                            <span class="text-xl font-black leading-tight">{{ optional($noticeDate)->format('d') }}</span>
                            <span class="text-[9px]">{{ optional($noticeDate)->format('Y') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="#" class="font-semibold text-gray-900 hover:text-red-800 text-sm leading-snug block group-hover:text-red-800 transition-colors">
                                {{ $notice->title }}
                            </a>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-xs font-bold text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100 uppercase">{{ $notice->type }}</span>
                                <span class="text-xs text-gray-400">{{ optional($noticeDate)->format('F d, Y') }}</span>
                                @if($notice->attachment)
                                    <a href="{{ asset('storage/'.$notice->attachment) }}" class="text-xs text-red-700 hover:underline flex items-center gap-1 font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Download
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">📋</p>
                        <p class="font-semibold text-gray-500">{{ $activeType === 'exam' ? 'No exam schedule or result notices published yet.' : 'No notices published yet.' }}</p>
                    </div>
                @endforelse
            </div>
            {{ $notices->withQueryString()->links() }}
        </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    @foreach([
                        ['label' => 'Notice Board', 'href' => route('public.notices', ['type' => 'general'])],
                        ['label' => 'Exam Schedules & Results', 'href' => route('public.notices', ['type' => 'exam'])],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                            <span class="text-red-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
