@extends('layouts.app')

@section('title', 'Notices')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-violet-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Notices
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">View department and general notices</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card 
            title="Total Notices" 
            value="{{ $totalNotices }}" 
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            tone="blue"
        />
        <x-stat-card 
            title="Published" 
            value="{{ $publishedNotices }}" 
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            tone="emerald"
        />
        <x-stat-card 
            title="Draft" 
            value="{{ $draftNotices }}" 
            icon="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
            tone="amber"
        />
    </div>

    {{-- Filters --}}
    <x-filter-bar action="{{ route('teacher.notices.index') }}">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Notice title..." 
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
            <select name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
                <option value="administrative" {{ request('type') == 'administrative' ? 'selected' : '' }}>Administrative</option>
            </select>
        </div>
    </x-filter-bar>

    {{-- Notices List --}}
    <div class="space-y-3">
        @forelse($notices as $notice)
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg font-semibold text-slate-900">{{ $notice->title }}</h3>
                            @if($notice->status === 'draft')
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    Draft
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    Published
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 line-clamp-2">{{ $notice->content }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span>{{ bsDate($notice->created_at, 'M d, Y') }}</span>
                            <span>•</span>
                            <span>By {{ $notice->author->name ?? 'System' }}</span>
                            @if($notice->type)
                                <span>•</span>
                                <span class="capitalize">{{ $notice->type }}</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('teacher.notices.show', $notice) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-100 whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-500">No notices available</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notices->hasPages())
        <div class="flex justify-center">
            {{ $notices->links() }}
        </div>
    @endif
</div>
@endsection
