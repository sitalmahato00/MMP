@extends('layouts.app')
@section('title', $content->title)

@section('content')
<x-page-header title="Facility/Resource Page" subtitle="View page information and content."
               back="{{ route('hod.facilities.index') }}"/>

<div class="max-w-4xl space-y-6">
    {{-- Page Header --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $content->title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    @if($content->is_published)
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                            Published
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                            Draft
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('hod.facilities.edit', $content) }}" 
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        {{-- Page Meta --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-sm">
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Created {{ $content->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Updated {{ $content->updated_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span class="font-mono text-xs">{{ $content->slug }}</span>
            </div>
        </div>
    </div>

    {{-- Featured Image --}}
    @if($content->featured_image)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Featured Image</h2>
            <img src="{{ asset('storage/' . $content->featured_image) }}" 
                 alt="{{ $content->title }}" 
                 class="w-full max-w-2xl rounded-xl border border-slate-200">
        </div>
    @endif

    {{-- Page Content --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Content</h2>
        <div class="prose prose-slate max-w-none">
            {!! nl2br(e($content->content)) !!}
        </div>
    </div>

    {{-- SEO Information --}}
    @if($content->meta_title || $content->meta_description)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">SEO Information</h2>
            <dl class="space-y-3">
                @if($content->meta_title)
                    <div>
                        <dt class="text-sm font-semibold text-slate-700">Meta Title</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $content->meta_title }}</dd>
                    </div>
                @endif
                @if($content->meta_description)
                    <div>
                        <dt class="text-sm font-semibold text-slate-700">Meta Description</dt>
                        <dd class="mt-1 text-sm text-slate-600">{{ $content->meta_description }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif
</div>
@endsection