@extends('layouts.app')

@section('title', 'News & Events')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_teacher_news_events_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_teacher_news_events_view', v); }
}" class="space-y-6">
    <x-page-header title="News & Events" subtitle="View published news and event updates relevant to your department." />

    <x-search-filter action="{{ route('teacher.news-events.index') }}">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search</label>
            <x-input type="text" name="search" value="{{ request('search') }}" placeholder="Title or content..." />
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type</label>
            <x-select name="type">
                <option value="">All</option>
                <option value="news" @selected(request('type') === 'news')>News</option>
                <option value="event" @selected(request('type') === 'event')>Events</option>
            </x-select>
        </div>
    </x-search-filter>

    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Department Feed</h2>
                <p class="text-xs text-slate-500">
                    @if($items->total() > 0)
                        Showing {{ $items->firstItem() }}-{{ $items->lastItem() }} of {{ number_format($items->total()) }} items
                    @else
                        No items match your filters
                    @endif
                </p>
            </div>

            <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
                <button type="button" @click="setView('table')"
                        :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">Table</button>
                <button type="button" @click="setView('cards')"
                        :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">Cards</button>
            </div>
        </div>

        <div x-show="view === 'table'" x-cloak>
            @if($items->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <x-empty-state title="No news or events available" message="There are no published news or event posts to show right now." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Audience</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Date</th>
                                <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $item)
                                <tr class="group hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate text-sm">{{ $item->title }}</p>
                                            <p class="text-[11px] text-slate-400 truncate">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 70) }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $item->type === 'event' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">{{ ucfirst($item->type) }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">{{ $item->program?->name ?? $item->department?->name ?? ($item->author?->name ?? 'College-wide') }}</td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('teacher.news-events.show', $item) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($items->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
                @endif
            @endif
        </div>

        <div x-show="view === 'cards'" x-cloak>
            @if($items->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <x-empty-state title="No news or events available" message="There are no published news or event posts to show right now." />
                </div>
            @else
                <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($items as $item)
                        <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                            <div class="flex flex-col items-center text-center">
                                <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $item->type === 'event' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">{{ ucfirst($item->type) }}</span>
                                <h3 class="mt-3 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $item->title }}</h3>
                                <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 90) }}</p>
                            </div>
                            <div class="mt-3 text-center">
                                <p class="text-xs text-slate-600 font-medium truncate">{{ $item->program?->name ?? $item->department?->name ?? ($item->author?->name ?? 'College-wide') }}</p>
                                <p class="text-[11px] text-slate-400">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</p>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-2">
                                <a href="{{ route('teacher.news-events.show', $item) }}" class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($items->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
                @endif
            @endif
        </div>
    </section>
</div>
@endsection
