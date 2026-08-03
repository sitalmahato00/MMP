@extends('layouts.guest')
@section('title', 'Notices — ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Page Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-black text-gray-900 font-serif">{{ $department->name }} Notices</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Official notices and announcements</p>
                    </div>
                    <a href="{{ route('public.department.show', $department->slug) }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-[#003D82] hover:underline">
                        ← Back to Department
                    </a>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('public.department.notices', $department->slug) }}"
                  class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Search notices..."
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                    </div>
                    <select name="category"
                            class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none bg-white min-w-36">
                        <option value="all" {{ ($category ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                        <option value="general"    {{ ($category ?? '') === 'general'    ? 'selected' : '' }}>General</option>
                        <option value="exam"       {{ ($category ?? '') === 'exam'       ? 'selected' : '' }}>Examination</option>
                        <option value="academic"   {{ ($category ?? '') === 'academic'   ? 'selected' : '' }}>Academic</option>
                        <option value="department" {{ ($category ?? '') === 'department' ? 'selected' : '' }}>Department</option>
                    </select>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-bold text-white rounded-lg transition-colors"
                            style="background-color: #003D82;">
                        Search
                    </button>
                    @if(($search ?? '') || (($category ?? 'all') !== 'all'))
                    <a href="{{ route('public.department.notices', $department->slug) }}"
                       class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Clear
                    </a>
                    @endif
                </div>
            </form>

            {{-- Notice List --}}
            @php
                $typeColors = [
                    'exam'       => 'bg-red-50 text-red-700 border-red-200',
                    'academic'   => 'bg-blue-50 text-blue-700 border-blue-200',
                    'department' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'general'    => 'bg-gray-100 text-gray-700 border-gray-200',
                ];
                $typeLabels = [
                    'exam'       => 'Examination',
                    'academic'   => 'Academic',
                    'department' => 'Department',
                    'general'    => 'General',
                ];
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @if($notices->count())
                    <div class="divide-y divide-gray-100">
                        @foreach($notices as $notice)
                        @php
                            $tc = $typeColors[$notice->type] ?? $typeColors['general'];
                            $tl = $typeLabels[$notice->type] ?? ucfirst($notice->type);
                            $hasAtt = $notice->attachment || $notice->attachments?->count();
                        @endphp
                        <div class="flex items-start gap-4 px-5 py-4 hover:bg-blue-50/30 transition-colors group">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $tc }}">
                                        {{ $tl }}
                                    </span>
                                    <span class="text-[11px] text-gray-400">
                                        {{ \Carbon\Carbon::parse($notice->published_at ?? $notice->created_at)->format('d M Y') }}
                                    </span>
                                </div>
                                <h3 class="font-semibold text-gray-900 text-sm group-hover:text-[#003D82] transition-colors leading-snug">
                                    {{ $notice->title }}
                                </h3>
                                @if($notice->content)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($notice->content), 150) }}
                                </p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 pt-1">
                                @if($hasAtt)
                                <a href="{{ $notice->attachment ? asset('storage/' . $notice->attachment) : '#' }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#003D82] border-2 border-[#003D82] rounded-lg hover:bg-[#003D82] hover:text-white transition-all duration-200"
                                   target="_blank" rel="noopener">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                                @endif
                                <a href="{{ route('public.notice.show', $notice->slug) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded-lg transition-colors"
                                   style="background-color: #003D82;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    {{-- Pagination --}}
                    @if($notices->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                        {{ $notices->links() }}
                    </div>
                    @endif
                @else
                <div class="p-12 text-center">
                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="font-semibold text-gray-700 text-sm">No notices found.</p>
                    @if($search || ($category && $category !== 'all'))
                    <a href="{{ route('public.department.notices', $department->slug) }}" class="text-sm text-[#003D82] hover:underline mt-1 inline-block">Clear filters</a>
                    @endif
                </div>
                @endif
            </div>

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
