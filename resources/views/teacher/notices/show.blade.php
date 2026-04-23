@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        :title="$notice->title" 
        subtitle="Notice Details"
        back="{{ route('teacher.notices.index') }}" />

    {{-- Notice Info Cards --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Type</p>
                <x-badge color="blue">{{ ucfirst($notice->type ?? 'General') }}</x-badge>
            </div>
        </x-card>

        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Published Date</p>
                <p class="text-sm font-semibold text-slate-900">{{ bsDate($notice->created_at, 'M d, Y') }}</p>
            </div>
        </x-card>

        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Author</p>
                <p class="text-sm font-semibold text-slate-900">{{ $notice->author->name ?? 'System' }}</p>
            </div>
        </x-card>
    </section>

    {{-- Content --}}
    <x-card>
        <div class="prose prose-sm max-w-none text-slate-600">
            {!! nl2br(e($notice->content)) !!}
        </div>
    </x-card>

    {{-- Attachments --}}
    @if($notice->attachments->isNotEmpty())
        <x-card>
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Attachments
                </h2>
                <p class="text-xs text-slate-500">{{ $notice->attachments->count() }} file{{ $notice->attachments->count() > 1 ? 's' : '' }} attached</p>
            </div>
            
            <div class="space-y-2">
                @foreach($notice->attachments as $attachment)
                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" 
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50 hover:border-slate-300">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $attachment->file_name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ number_format($attachment->file_size / 1024, 2) }} KB
                                @if($attachment->file_type)
                                    • {{ strtoupper($attachment->file_type) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
@endsection
