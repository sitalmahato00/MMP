@extends('layouts.guest')
@section('title', 'Notices — ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
@php
    $typeBadgeColors = [
        'general'    => 'bg-blue-600 text-white',
        'academic'   => 'bg-indigo-600 text-white',
        'exam'       => 'bg-amber-500 text-white',
        'department' => 'bg-emerald-600 text-white',
        'program'    => 'bg-green-600 text-white',
    ];
    $typeLabels = [
        'general'    => 'General',
        'academic'   => 'Academic',
        'exam'       => 'Examination',
        'department' => 'Department',
        'program'    => 'Program',
    ];
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Page Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $department->name }} Notices</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Official notices and announcements
                        @if($notices->total())
                            &mdash; {{ $notices->total() }} {{ $notices->total() === 1 ? 'notice' : 'notices' }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('public.department.show', $department->slug) }}"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#003D82] hover:underline flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Department
                </a>
            </div>

            {{-- Search + Category Filter --}}
            <form method="GET" action="{{ route('public.department.notices', $department->slug) }}"
                  class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Search notices..."
                           class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white">
                </div>
                <select name="category"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white min-w-40">
                    <option value="all" {{ ($category ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                    <option value="general"    {{ ($category ?? '') === 'general'    ? 'selected' : '' }}>General</option>
                    <option value="exam"       {{ ($category ?? '') === 'exam'       ? 'selected' : '' }}>Examination</option>
                    <option value="academic"   {{ ($category ?? '') === 'academic'   ? 'selected' : '' }}>Academic</option>
                    <option value="department" {{ ($category ?? '') === 'department' ? 'selected' : '' }}>Department</option>
                </select>
                <button type="submit"
                        class="px-5 py-2 text-sm font-bold text-white bg-[#003D82] hover:bg-blue-900 rounded-lg transition-colors">
                    Search
                </button>
                @if(($search ?? '') || (($category ?? 'all') !== 'all'))
                    <a href="{{ route('public.department.notices', $department->slug) }}"
                       class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
                        Clear
                    </a>
                @endif
            </form>

            {{-- Card Grid --}}
            @if($notices->count())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($notices as $notice)
                        @php
                            $noticeDate  = $notice->published_at ?? $notice->created_at;
                            $firstImage  = $notice->attachments->firstWhere('is_image', true);
                            $hasAttachment = $notice->attachments->count() > 0 || $notice->attachment;
                            $badgeColor  = $typeBadgeColors[$notice->type] ?? 'bg-slate-600 text-white';
                            $typeLabel   = $typeLabels[$notice->type] ?? ucfirst($notice->type);
                        @endphp
                        <a href="{{ route('public.notice.show', $notice->slug) }}"
                           class="group block rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">

                            {{-- Image / Placeholder --}}
                            <div class="relative h-44 bg-gradient-to-br from-slate-50 to-blue-100 overflow-hidden">
                                @if($firstImage)
                                    <img src="{{ $firstImage->url }}"
                                         alt="{{ $notice->title }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-16 h-16 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                @endif

                                {{-- Date badge --}}
                                <div class="absolute top-3 left-3 bg-[#003D82] text-white px-2.5 py-1.5 rounded-lg shadow-lg">
                                    <div class="text-[10px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</div>
                                    <div class="text-xl font-bold leading-tight">{{ bsDate($noticeDate, 'd') }}</div>
                                </div>

                                {{-- Type badge --}}
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold shadow-lg {{ $badgeColor }}">
                                        {{ $typeLabel }}
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
                                <h3 class="text-sm font-bold text-gray-900 mb-1.5 line-clamp-2 group-hover:text-[#003D82] transition-colors leading-snug">
                                    {{ $notice->title }}
                                </h3>

                                @if($notice->content)
                                    <p class="text-xs text-gray-500 mb-3 line-clamp-2 leading-relaxed">
                                        {{ Str::limit(strip_tags($notice->content), 100) }}
                                    </p>
                                @endif

                                <div class="flex items-center gap-1 text-xs text-gray-400 mt-auto">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ bsDate($noticeDate, 'Y, F d') }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($notices->hasPages())
                    <div class="mt-4">
                        {{ $notices->withQueryString()->links() }}
                    </div>
                @endif

            @else
                <div class="py-16 text-center bg-white rounded-lg border border-gray-200 shadow-sm">
                    <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="font-semibold text-gray-700">No notices found.</p>
                    @if($search || ($category && $category !== 'all'))
                        <a href="{{ route('public.department.notices', $department->slug) }}"
                           class="text-sm text-[#003D82] hover:underline mt-2 inline-block">
                            Clear filters
                        </a>
                    @endif
                </div>
            @endif

        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'notices',
                'downloads'  => collect([]),
                'events'     => collect([]),
            ])
        </div>

    </div>
</div>
@endsection
