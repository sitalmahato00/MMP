@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Notices & Announcements</h1>
    <p class="text-sm text-slate-500 mt-1">Stay updated with the latest notices from the college.</p>
</div>

<div class="space-y-3">
    @forelse($notices as $notice)
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="mt-1 h-2.5 w-2.5 rounded-full flex-shrink-0
                {{ $notice->priority === 'high' ? 'bg-red-500' : ($notice->priority === 'medium' ? 'bg-amber-500' : 'bg-blue-500') }}">
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-slate-900">{{ $notice->title }}</h3>
                @if($notice->content)
                <p class="mt-1 text-sm text-slate-600 line-clamp-2">{!! strip_tags($notice->content) !!}</p>
                @endif
                <p class="mt-2 text-xs text-slate-400">{{ bsDate($notice->created_at, 'Y, F d') }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <p class="text-sm text-slate-500">No notices published yet.</p>
    </div>
    @endforelse
</div>

@if($notices->hasPages())
<div class="mt-6">
    {{ $notices->links() }}
</div>
@endif
@endsection
