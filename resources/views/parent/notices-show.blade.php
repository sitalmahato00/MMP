@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('parent.notices.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Notices
        </a>
    </div>

    <!-- Main Content Card -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="border-b border-slate-200 bg-gradient-to-r from-blue-50 to-slate-50 px-8 py-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold 
                            {{ $notice->type === 'exam' ? 'bg-amber-100 text-amber-700' : 
                               ($notice->type === 'academic' ? 'bg-purple-100 text-purple-700' : 
                               ($notice->type === 'department' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700')) }}">
                            <i class="fas {{ $notice->type === 'exam' ? 'fa-file-alt' : 
                                           ($notice->type === 'academic' ? 'fa-graduation-cap' : 
                                           ($notice->type === 'department' ? 'fa-building' : 'fa-bullhorn')) }} mr-1.5"></i>
                            {{ ucfirst($notice->type) }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $notice->title }}</h1>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                        @if($notice->department || $notice->program)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-building text-slate-400"></i>
                                <span>{{ $notice->department?->name ?? $notice->program?->name }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar text-slate-400"></i>
                            <span>{{ bsDate($notice->published_at ?? $notice->created_at) }}</span>
                        </div>
                        @if($notice->author)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-slate-400"></i>
                                <span>{{ $notice->author->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-8 py-6">
            <div class="prose prose-slate max-w-none">
                {!! $notice->content !!}
            </div>
        </div>

        <!-- Attachments Section -->
        @if($notice->attachment || $notice->attachments->count() > 0)
            <div class="border-t border-slate-200 px-8 py-6 bg-slate-50">
                <h3 class="text-sm font-semibold text-slate-900 mb-4 flex items-center">
                    <i class="fas fa-paperclip mr-2 text-slate-400"></i>
                    Attachments
                </h3>
                <div class="space-y-3">
                    @if($notice->attachment)
                        @php
                            $fileName = basename($notice->attachment);
                            $fileSize = \Illuminate\Support\Facades\Storage::exists('public/' . $notice->attachment) 
                                ? \Illuminate\Support\Facades\Storage::size('public/' . $notice->attachment) 
                                : 0;
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-200 hover:border-blue-300 transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-{{ in_array($extension, ['pdf']) ? 'pdf' : (in_array($extension, ['doc', 'docx']) ? 'word' : (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'alt')) }} text-blue-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $fileName }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format($fileSize / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($notice->attachment) }}" target="_blank" download 
                               class="flex-shrink-0 ml-4 inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-download mr-1.5"></i>
                                Download
                            </a>
                        </div>
                    @endif

                    @foreach($notice->attachments as $attachment)
                        @php
                            $fileName = basename($attachment->file_path);
                            $fileSize = \Illuminate\Support\Facades\Storage::exists('public/' . $attachment->file_path) 
                                ? \Illuminate\Support\Facades\Storage::size('public/' . $attachment->file_path) 
                                : 0;
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-200 hover:border-blue-300 transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-{{ in_array($extension, ['pdf']) ? 'pdf' : (in_array($extension, ['doc', 'docx']) ? 'word' : (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'alt')) }} text-blue-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $attachment->file_name ?? $fileName }}</p>
                                    <p class="text-xs text-slate-500">{{ $attachment->file_type ?? strtoupper($extension) }} • {{ number_format($fileSize / 1024, 2) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" download 
                               class="flex-shrink-0 ml-4 inline-flex items-center px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-download mr-1.5"></i>
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
