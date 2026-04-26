@extends('layouts.guest')
@section('title', 'Notices & Announcements')
@section('breadcrumb', true)

@section('content')
@php
    $activeType = $activeType ?? 'all';
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notices & Announcements</h1>
        <p class="text-sm text-gray-500">{{ $notices->total() }} notices</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="divide-y">
            @forelse($notices as $notice)
                @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                <a href="{{ route('public.notice.show', $notice->slug) }}" class="block p-4 hover:bg-gray-50">
                    <div class="font-semibold text-gray-900 mb-2">{{ $notice->title }}</div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 flex-wrap">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $notice->type }}</span>
                        @if($notice->department)
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded">{{ $notice->department->name }}</span>
                        @endif
                        @if($notice->program)
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">{{ $notice->program->name }}</span>
                        @endif
                        <span>{{ bsDate($noticeDate, 'Y, F d') }}</span>
                        @if($notice->attachment)
                            <span class="flex items-center gap-1 text-blue-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Attachment
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="py-16 text-center">
                    <p class="text-4xl mb-4">📋</p>
                    <p class="font-semibold text-gray-500">No notices published yet.</p>
                </div>
            @endforelse
        </div>
        <div class="p-4 border-t">
            {{ $notices->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
