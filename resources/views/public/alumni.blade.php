@extends('layouts.guest')
@section('title', 'Alumni Directory')

@section('content')
<section class="py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900 lg:text-4xl">Alumni Directory</h1>
            <p class="mx-auto mt-3 max-w-2xl text-base text-slate-600">Explore our growing network of graduates making an impact across Nepal and beyond.</p>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mb-8 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-md hover:shadow-xl transition-shadow duration-300">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Search</label>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Search alumni by name�"
                           class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20"/>
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Department</label>
                    <select name="department" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Batch</label>
                    <select name="year" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#003D82] focus:ring-[#003D82]/20">
                        <option value="">All Years</option>
                        @foreach($graduationYears as $y)
                            <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-[#003D82] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">Search</button>
                @if(request()->hasAny(['search','department','year']))
                    <a href="{{ route('public.alumni') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</a>
                @endif
            </div>
        </form>

        {{-- Results --}}
        @if($alumni->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($alumni as $a)
            @php
                $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
                $grad = $gradients[$a->id % 6];
                $profileCompletion = max(0, min(100, (int) ($a->profile_completion ?? 0)));
            @endphp
            <a href="{{ route('public.alumni.profile', $a->id) }}"
               class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-md overflow-hidden hover:shadow-2xl hover:border-slate-300 dark:hover:border-slate-600 hover:scale-[1.02] hover:-translate-y-1 transition-all duration-300">
                <div class="px-4 pt-6 pb-4 relative text-center">
                    @if($a->is_featured)
                        <span class="absolute top-2 right-2 rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400">⭐ Featured</span>
                    @endif
                    
                    {{-- Centered Profile Picture --}}
                    <div class="flex justify-center mb-3">
                        @if($a->user?->avatar)
                            <img src="{{ asset('storage/'.$a->user->avatar) }}" alt="" class="h-20 w-20 rounded-full object-cover ring-4 ring-slate-100 shadow-md"/>
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br {{ $grad }} text-2xl font-bold text-white ring-4 ring-slate-100 shadow-md">
                                {{ strtoupper(substr($a->user?->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 group-hover:text-[#003D82] dark:group-hover:text-blue-400 transition truncate">{{ $a->user?->name }}</h3>
                    @if($a->current_job)
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $a->current_job }}@if($a->company_name) · {{ $a->company_name }}@endif</p>
                    @endif
                    @if($a->work_location)
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">{{ $a->work_location }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-1 justify-center">
                        <span class="rounded bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">{{ $a->department?->code }}</span>
                        <span class="rounded bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-300">Batch {{ $a->graduation_year }}</span>
                        @if($a->is_verified)
                            <span class="rounded bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Verified</span>
                        @endif
                        @if(($a->visible_projects_count ?? 0) > 0)
                            <span class="rounded bg-violet-50 dark:bg-violet-900/30 px-2 py-0.5 text-[10px] font-bold text-violet-600 dark:text-violet-400">{{ $a->visible_projects_count }} {{ Str::plural('project', $a->visible_projects_count) }}</span>
                        @endif
                        @if(($a->achievement_records_count ?? 0) > 0)
                            <span class="rounded bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400">{{ $a->achievement_records_count }} {{ Str::plural('achievement', $a->achievement_records_count) }}</span>
                        @endif
                    </div>
                    @if($profileCompletion > 0)
                        <div class="mt-3">
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#003D82] to-amber-500" style="width: {{ $profileCompletion }}%"></div>
                            </div>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Profile {{ $profileCompletion }}%</p>
                        </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $alumni->withQueryString()->links() }}</div>
        @else
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-12 text-center shadow-md hover:shadow-xl transition-shadow duration-300">
            <svg class="mx-auto w-12 h-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">No alumni found</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Try adjusting your search filters.</p>
        </div>
        @endif

    </div>
</section>
@endsection

