@extends('layouts.guest')
@section('title', 'Notices & Announcements')
@section('meta_description', 'Official notices and announcements from Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $activeType = $activeType ?? 'all';
    $typeLabels = [
        'all'      => 'All',
        'general'  => 'General',
        'academic' => 'Academic',
        'exam'     => 'Exam',
        'department' => 'Department',
        'program'  => 'Program',
    ];
    $typeColors = [
        'general'    => 'bg-blue-100 text-blue-700',
        'academic'   => 'bg-indigo-100 text-indigo-700',
        'exam'       => 'bg-amber-100 text-amber-700',
        'department' => 'bg-emerald-100 text-emerald-700',
        'program'    => 'bg-green-100 text-green-700',
    ];
    $typeBadgeColors = [
        'general'    => 'bg-blue-600 text-white',
        'academic'   => 'bg-indigo-600 text-white',
        'exam'       => 'bg-amber-500 text-white',
        'department' => 'bg-emerald-600 text-white',
        'program'    => 'bg-green-600 text-white',
    ];
@endphp

<div class="mx-auto w-full px-4 py-8 md:px-8 xl:px-16 2xl:px-24">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notices & Announcements</h1>
        <p class="text-sm text-gray-500">{{ $notices->total() }} {{ $notices->total() === 1 ? 'notice' : 'notices' }}</p>
    </div>

    {{-- Type Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($typeLabels as $typeKey => $typeLabel)
            <a href="{{ route('public.notices', array_merge(request()->query(), ['type' => $typeKey])) }}"
               class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-colors
                      {{ $activeType === $typeKey
                         ? 'bg-[#003D82] text-white border-[#003D82]'
                         : 'bg-white text-gray-600 border-gray-300 hover:border-[#003D82] hover:text-[#003D82]' }}">
                {{ $typeLabel }}
            </a>
        @endforeach
    </div>

    {{-- Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($notices as $notice)
            @php
                $noticeDate  = $notice->published_at ?? $notice->created_at;
                $firstImage  = $notice->attachments->firstWhere('is_image', true);
                $hasAttachment = $notice->attachments->count() > 0 || $notice->attachment;
                $badgeColor  = $typeBadgeColors[$notice->type] ?? 'bg-slate-600 text-white';
                $tagColor    = $typeColors[$notice->type] ?? 'bg-slate-100 text-slate-700';
            @endphp
            <a href="{{ route('public.notice.show', $notice->slug) }}"
               class="group block rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">

                {{-- Image / Placeholder --}}
                <div class="relative h-48 bg-gradient-to-br from-slate-50 to-blue-100 overflow-hidden">
                    @if($firstImage)
                        <img src="{{ $firstImage->url }}"
                             alt="{{ $notice->title }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-20 h-20 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Date badge --}}
                    <div class="absolute top-3 left-3 bg-[#003D82] text-white px-3 py-1.5 rounded-lg shadow-lg">
                        <div class="text-[10px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</div>
                        <div class="text-2xl font-bold leading-tight">{{ bsDate($noticeDate, 'd') }}</div>
                    </div>

                    {{-- Type badge --}}
                    <div class="absolute top-3 right-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold shadow-lg {{ $badgeColor }}">
                            {{ ucfirst($notice->type) }}
                        </span>
                    </div>

                    {{-- Attachment indicator --}}
                    @if($hasAttachment)
                        <div class="absolute bottom-3 right-3 bg-black/50 text-white px-2 py-1 rounded text-[10px] font-semibold flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Attachment
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="p-4">
                    <h3 class="text-base font-bold text-gray-900 mb-1.5 line-clamp-2 group-hover:text-[#003D82] transition-colors leading-snug">
                        {{ $notice->title }}
                    </h3>

                    @if($notice->content)
                        <p class="text-xs text-gray-500 mb-3 line-clamp-2 leading-relaxed">
                            {{ Str::limit(strip_tags($notice->content), 110) }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between flex-wrap gap-1.5 text-xs text-gray-500 mt-auto">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ bsDate($noticeDate, 'Y, F d') }}
                        </span>

                        <div class="flex flex-wrap gap-1">
                            @if($notice->department)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">
                                    {{ $notice->department->name }}
                                </span>
                            @endif
                            @if($notice->program)
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-700">
                                    {{ $notice->program->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 text-center">
                <p class="text-4xl mb-4">📋</p>
                <p class="font-semibold text-gray-500">No notices published yet.</p>
                <p class="mt-2 text-sm text-gray-400">Check back soon for updates.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $notices->withQueryString()->links() }}
    </div>

</div>
@endsection
