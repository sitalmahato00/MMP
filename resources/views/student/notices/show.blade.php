@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="{{ route('student.notices.index') }}" 
                   class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Notices
                </a>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Notice</p>
                <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                    {{ $notice->title }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                    <span>Published: {{ bsDate($notice->published_at, 'F d, Y') }}</span>
                    @if($notice->author)
                        <span>•</span>
                        <span>By: {{ $notice->author->name }}</span>
                    @endif
                    @if($notice->attachment || $notice->attachments->count() > 0)
                        <span>•</span>
                        <span>{{ ($notice->attachment ? 1 : 0) + $notice->attachments->count() }} attachment(s)</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-4">
        {{-- Notice Content --}}
        <div class="lg:col-span-3 space-y-6">
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! nl2br(e($notice->content)) !!}
                </div>
            </section>

            @if($notice->attachment || $notice->attachments->count() > 0)
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Attachments</h2>
                        <p class="text-xs text-slate-500">Preview or download attached files</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ ($notice->attachment ? 1 : 0) + $notice->attachments->count() }} file{{ (($notice->attachment ? 1 : 0) + $notice->attachments->count()) > 1 ? 's' : '' }}</span>
                </div>

                <div class="grid gap-4">
                    @php
                        $allAttachments = collect();
                        if ($notice->attachment) {
                            $allAttachments->push((object)[
                                'file_path' => $notice->attachment,
                                'file_name' => basename($notice->attachment),
                                'file_size' => Storage::disk('public')->exists($notice->attachment) ? Storage::disk('public')->size($notice->attachment) : null,
                            ]);
                        }
                        foreach ($notice->attachments as $attachment) {
                            $allAttachments->push($attachment);
                        }
                    @endphp

                    @foreach($allAttachments as $attachment)
                        @php
                            $path = ltrim($attachment->file_path, '/');
                            $url = asset('storage/' . $path);
                            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg','jpeg','png','gif','webp','svg']);
                            $fileName = $attachment->file_name ?? basename($path);
                            $fileSize = $attachment->file_size ?? null;
                        @endphp

                        <article class="rounded-3xl border border-slate-200 bg-slate-50 overflow-hidden shadow-sm">
                            <div class="grid gap-4 lg:grid-cols-[120px_auto] p-4">
                                <div class="overflow-hidden rounded-3xl bg-white">
                                    @if($isImage)
                                        <img src="{{ $url }}" alt="{{ $fileName }}" class="h-28 w-full object-cover" />
                                    @else
                                        <div class="flex h-28 items-center justify-center bg-slate-100 text-slate-500">
                                            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <p class="font-semibold text-slate-900 truncate">{{ $fileName }}</p>
                                        @if($fileSize)
                                            <p class="text-xs text-slate-500">{{ number_format($fileSize / 1024, 1) }} KB</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ $url }}" target="_blank"
                                           class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l-3-3m0 0l-3 3m3-3v12"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6"/></svg>
                                            View
                                        </a>
                                        <a href="{{ $url }}" download
                                           class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5M12 15V3"/></svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Notice Info --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Notice Information</h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-600">Published Date</span>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ bsDate($notice->published_at, 'F d, Y') }}</p>
                    </div>
                    
                    @if($notice->author)
                        <div>
                            <span class="text-xs text-slate-600">Published By</span>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ $notice->author->name }}</p>
                        </div>
                    @endif
                    
                    <div>
                        <span class="text-xs text-slate-600">Department</span>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $notice->department->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection