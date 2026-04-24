@extends('layouts.app')

@section('title', 'News & Events')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_parent_news_events_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_parent_news_events_view', v); }
}" class="space-y-6">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">News & Events</h1>
        <p class="mt-0.5 text-sm text-slate-500">Latest college news and upcoming events</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Total</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                    <i class="fas fa-newspaper text-xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">News</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['news'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                    <i class="fas fa-rss text-xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">Events</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['events'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50">
                    <i class="fas fa-calendar-alt text-xl text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('parent.news-events.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            <div class="relative lg:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search news or events..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
            </div>
            <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All</option>
                <option value="news" @selected(request('type') === 'news')>News</option>
                <option value="event" @selected(request('type') === 'event')>Events</option>
            </select>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition">Apply Filters</button>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('parent.news-events.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition">Clear</a>
            @endif
        </div>
    </form>

    <!-- News & Events List -->
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <p class="text-sm text-slate-500">
                @if($items->total() > 0)
                    Showing <span class="font-semibold text-slate-700">{{ $items->firstItem() }}-{{ $items->lastItem() }}</span>
                    of <span class="font-semibold text-slate-700">{{ number_format($items->total()) }}</span> items
                @else
                    No news or events match your filters
                @endif
            </p>

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
                    <h3 class="text-base font-bold text-slate-800">No news or events available</h3>
                    <p class="mt-1 text-sm text-slate-500">There are no published updates right now.</p>
                </div>
            @else
                <div class="mmp-table-wrap">
                    <table class="mmp-table w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Source</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Date</th>
                                <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $item)
                                <tr class="group hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-slate-900 truncate text-sm">{{ $item->title }}</p>
                                                @if($item->attachment || $item->attachments->count() > 0)
                                                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has attachments">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 truncate">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 70) }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $item->type === 'event' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                                        {{ $item->program?->name ?? $item->department?->name ?? ($item->author?->name ?? 'College-wide') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('parent.news-events.show', $item) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
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
                    <h3 class="text-base font-bold text-slate-800">No news or events available</h3>
                    <p class="mt-1 text-sm text-slate-500">There are no published updates right now.</p>
                </div>
            @else
                <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($items as $item)
                        <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                            <div class="flex flex-col items-center text-center">
                                <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $item->type === 'event' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">{{ ucfirst($item->type) }}</span>
                                <div class="flex items-center justify-center gap-2 mt-3">
                                    <h3 class="text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $item->title }}</h3>
                                    @if($item->attachment || $item->attachments->count() > 0)
                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has attachments">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    @endif
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->content), 90) }}</p>
                            </div>
                            <div class="mt-3 text-center">
                                <p class="text-xs text-slate-600 font-medium truncate">{{ $item->program?->name ?? $item->department?->name ?? ($item->author?->name ?? 'College-wide') }}</p>
                                <p class="text-[11px] text-slate-400">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</p>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-2">
                                <a href="{{ route('parent.news-events.show', $item) }}" class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">View</a>
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
