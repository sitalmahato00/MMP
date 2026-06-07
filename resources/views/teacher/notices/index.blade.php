@extends('layouts.app')

@section('title', 'Notices')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_teacher_notices_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_teacher_notices_view', v); }
}" class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Notices" 
        subtitle="View department and general notices" />

    {{-- Filters --}}
    <x-search-filter action="{{ route('teacher.notices.index') }}">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search</label>
            <x-input type="text" name="search" value="{{ request('search') }}" placeholder="Notice title..." />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
            <x-select name="type">
                <option value="">All Types</option>
                <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                <option value="exam" {{ request('type') == 'exam' ? 'selected' : '' }}>Exam</option>
                <option value="department" {{ request('type') == 'department' ? 'selected' : '' }}>Department</option>
                <option value="academic" {{ request('type') == 'academic' ? 'selected' : '' }}>Academic</option>
            </x-select>
        </div>
    </x-search-filter>

    {{-- Notices Table/Cards --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        {{-- Panel header: result count + view toggle --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Department Notices</h2>
                <p class="text-xs text-slate-500">Latest notices and announcements</p>
            </div>

            {{-- View toggle --}}
            <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
                <button type="button" @click="setView('table')"
                        :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                    Table
                </button>
                <button type="button" @click="setView('cards')"
                        :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Cards
                </button>
            </div>
        </div>

        {{-- ── TABLE VIEW ─────────────────────────────────────── --}}
        <div x-show="view === 'table'" x-cloak>
            @if($notices->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <x-empty-state 
                        title="No notices available"
                        message="There are no notices to display at the moment.">
                        <x-slot name="icon">
                            <svg class="mx-auto w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </x-slot>
                    </x-empty-state>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Author</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Date</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden sm:table-cell">Attachments</th>
                                <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($notices as $notice)
                                <tr class="group hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate text-sm">{{ $notice->title }}</p>
                                                <p class="text-[11px] text-slate-400 truncate">{{ Str::limit(strip_tags($notice->content), 60) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <x-badge color="blue">{{ ucfirst($notice->type ?? 'General') }}</x-badge>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                                        {{ $notice->author->name ?? 'System' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                                        {{ bsDate($notice->created_at, 'M d, Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden sm:table-cell">
                                        @if($notice->attachment || $notice->attachments->isNotEmpty())
                                            <div class="flex items-center gap-1">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                {{ ($notice->attachment ? 1 : 0) + $notice->attachments->count() }}
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-btn href="{{ route('teacher.notices.show', $notice) }}" variant="secondary" size="sm">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </x-btn>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($notices->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $notices->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- ── CARD VIEW ──────────────────────────────────────── --}}
        <div x-show="view === 'cards'" x-cloak>
            @if($notices->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <x-empty-state 
                        title="No notices available"
                        message="There are no notices to display at the moment.">
                        <x-slot name="icon">
                            <svg class="mx-auto w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </x-slot>
                    </x-empty-state>
                </div>
            @else
                <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($notices as $notice)
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                        {{-- Icon --}}
                        <div class="flex flex-col items-center text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $notice->title }}</h3>
                            <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ Str::limit(strip_tags($notice->content), 80) }}</p>
                        </div>
                        {{-- Badges --}}
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                            <x-badge color="blue">{{ ucfirst($notice->type ?? 'General') }}</x-badge>
                            @if($notice->attachment || $notice->attachments->isNotEmpty())
                                <span class="rounded-lg bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">{{ ($notice->attachment ? 1 : 0) + $notice->attachments->count() }} file{{ ((($notice->attachment ? 1 : 0) + $notice->attachments->count()) > 1) ? 's' : '' }}</span>
                            @endif
                        </div>
                        {{-- Meta info --}}
                        <div class="mt-3 space-y-0.5 text-center">
                            <p class="text-xs text-slate-600 font-medium truncate">{{ $notice->author->name ?? 'System' }}</p>
                            <p class="text-[11px] text-slate-400">{{ bsDate($notice->created_at, 'M d, Y') }}</p>
                        </div>
                        {{-- Actions --}}
                        <div class="mt-4 grid grid-cols-1 gap-2">
                            <x-btn href="{{ route('teacher.notices.show', $notice) }}" variant="secondary" size="sm" class="w-full justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View Notice
                            </x-btn>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($notices->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $notices->links() }}</div>
                @endif
            @endif
        </div>

    </section>
</div>
@endsection
