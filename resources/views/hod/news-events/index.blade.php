@extends('layouts.app')

@section('title', 'News & Events')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_hod_news_events_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_news_events_view', v); }
}" class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">News & Events</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ $department->name }} - manage department news posts and event updates</p>
        </div>
        <a href="{{ route('hod.news-events.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create News/Event
        </a>
    </div>

    <form method="GET" action="{{ route('hod.news-events.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
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
                <a href="{{ route('hod.news-events.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition">Clear</a>
            @endif
        </div>
    </form>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
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
                <button type="button" @click="setView('table')" :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">Table</button>
                <button type="button" @click="setView('cards')" :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">Cards</button>
            </div>
        </div>

        <div x-show="view === 'table'" x-cloak>
            @if($items->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <h3 class="text-base font-bold text-slate-800">No news or events available</h3>
                    <p class="mt-1 text-sm text-slate-500">Create the first post for your department.</p>
                </div>
            @else
                <div class="mmp-table-wrap">
                    <table class="mmp-table w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Program</th>
                                <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Status</th>
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
                                    <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">{{ $item->program?->name ?? 'All Programs' }}</td>
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $item->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $item->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('hod.news-events.show', $item) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            @if($item->created_by === auth()->id())
                                                <a href="{{ route('hod.news-events.edit', $item) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <form method="POST" action="{{ route('hod.news-events.destroy', $item) }}" onsubmit="return confirm('Delete {{ addslashes($item->title) }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
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
                    <p class="mt-1 text-sm text-slate-500">Create the first post for your department.</p>
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
                                <p class="text-xs text-slate-600 font-medium truncate">{{ $item->program?->name ?? 'All Programs' }}</p>
                                <p class="text-[11px] text-slate-400">{{ bsDate($item->published_at ?? $item->created_at, 'Y, F d') }}</p>
                            </div>
                            <div class="mt-4 grid grid-cols-1 gap-2">
                                <a href="{{ route('hod.news-events.show', $item) }}" class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">View</a>
                                @if($item->created_by === auth()->id())
                                    <a href="{{ route('hod.news-events.edit', $item) }}" class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($items->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
