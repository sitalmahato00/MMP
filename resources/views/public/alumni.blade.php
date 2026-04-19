@extends('layouts.guest')
@section('title', 'Alumni Directory')

@section('content')
<section class="py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-black text-slate-900 lg:text-4xl">Alumni Directory</h1>
            <p class="mx-auto mt-3 max-w-2xl text-base text-slate-600">Explore our growing network of graduates making an impact across Nepal and beyond.</p>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Search</label>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Search alumni by name…"
                           class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#8B0000] focus:ring-[#8B0000]/20"/>
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Department</label>
                    <select name="department" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#8B0000] focus:ring-[#8B0000]/20">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Batch</label>
                    <select name="year" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-[#8B0000] focus:ring-[#8B0000]/20">
                        <option value="">All Years</option>
                        @foreach($graduationYears as $y)
                            <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">Search</button>
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
               class="group rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden hover:shadow-md hover:border-slate-300 transition">
                <div class="h-20 bg-gradient-to-br {{ $grad }} relative">
                    @if($a->is_featured)
                        <span class="absolute top-2 right-2 rounded-lg bg-white/90 px-2 py-0.5 text-[10px] font-bold text-amber-700">★ Featured</span>
                    @endif
                </div>
                <div class="px-4 pb-4 -mt-8 relative">
                    @if($a->user?->avatar)
                        <img src="{{ asset('storage/'.$a->user->avatar) }}" alt="" class="h-14 w-14 rounded-xl object-cover ring-4 ring-white shadow-sm"/>
                    @else
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-xl font-black text-white ring-4 ring-white shadow-sm">
                            {{ strtoupper(substr($a->user?->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <h3 class="mt-2 text-sm font-bold text-slate-900 group-hover:text-[#8B0000] transition truncate">{{ $a->user?->name }}</h3>
                    @if($a->current_job)
                        <p class="text-xs text-slate-500 truncate">{{ $a->current_job }}@if($a->company_name) · {{ $a->company_name }}@endif</p>
                    @endif
                    @if($a->work_location)
                        <p class="text-[11px] text-slate-400 truncate">{{ $a->work_location }}</p>
                    @endif
                    <div class="mt-2 flex flex-wrap gap-1">
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ $a->department?->code }}</span>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Batch {{ $a->graduation_year }}</span>
                        @if($a->is_verified)
                            <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Verified</span>
                        @endif
                        @if(($a->visible_projects_count ?? 0) > 0)
                            <span class="rounded bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-600">{{ $a->visible_projects_count }} {{ Str::plural('project', $a->visible_projects_count) }}</span>
                        @endif
                        @if(($a->achievement_records_count ?? 0) > 0)
                            <span class="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ $a->achievement_records_count }} {{ Str::plural('achievement', $a->achievement_records_count) }}</span>
                        @endif
                    </div>
                    @if($profileCompletion > 0)
                        <div class="mt-3">
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#8B0000] to-amber-500" style="width: {{ $profileCompletion }}%"></div>
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
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <svg class="mx-auto w-12 h-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <h3 class="text-lg font-bold text-slate-900">No alumni found</h3>
            <p class="text-sm text-slate-500 mt-1">Try adjusting your search filters.</p>
        </div>
        @endif

    </div>
</section>
@endsection
