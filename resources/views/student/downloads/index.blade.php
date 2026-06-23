@extends('layouts.app')

@section('title', 'Study Materials')

@section('content')
<div class="space-y-6">
    {{-- KPI Cards --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2">
        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalDownloads) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Resources</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($subjectCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Subjects</p>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search materials..."
                       class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                <select name="subject_id" class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('student.downloads.index') }}" class="rounded-lg border border-slate-300 dark:border-[#2d4a70] px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    Clear
                </a>
            </div>
        </form>
    </section>

    {{-- Downloads List --}}
    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm">
        <div class="border-b border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Available Resources</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Study materials uploaded by your teachers</p>
        </div>
        
        <div class="divide-y divide-slate-100 dark:divide-[#1e3a5f]">
            @forelse($downloads as $download)
                <div class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $download->title }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                @if($download->subject)
                                    <span>{{ $download->subject->name }}</span>
                                    <span>•</span>
                                @endif
                                <span>Uploaded {{ bsDate($download->created_at, 'F d, Y') }}</span>
                                @if($download->uploadedBy)
                                    <span>•</span>
                                    <span>By {{ $download->uploadedBy->name }}</span>
                                @endif
                            </div>
                            @if($download->description)
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $download->description }}</p>
                            @endif
                        </div>
                        
                        <a href="{{ route('student.downloads.file', $download->id) }}" 
                           class="flex-shrink-0 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No study materials available yet</p>
                </div>
            @endforelse
        </div>

        @if($downloads->hasPages())
            <div class="border-t border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
                {{ $downloads->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
