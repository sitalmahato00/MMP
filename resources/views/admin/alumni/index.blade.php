@extends('layouts.app')
@section('title', 'Alumni')

@section('content')
@php
    $statusMap = [
        'employed'   => ['label' => 'Employed',   'cls' => 'bg-emerald-50 text-emerald-700'],
        'studying'   => ['label' => 'Studying',   'cls' => 'bg-blue-50 text-blue-700'],
        'freelancing'=> ['label' => 'Freelancing', 'cls' => 'bg-violet-50 text-violet-700'],
        'unemployed' => ['label' => 'Unemployed', 'cls' => 'bg-amber-50 text-amber-700'],
        'unknown'    => ['label' => 'Unknown',    'cls' => 'bg-slate-100 text-slate-600'],
    ];
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_alumni_view') ?? 'table',
    toggleView(v) { this.view = v; localStorage.setItem('mmp_alumni_view', v); }
}">

<x-page-header title="Alumni" subtitle="Manage graduated students, career data, and final-year projects">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.alumni.create') }}">Add Alumni</x-btn>
    </x-slot>
</x-page-header>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $totalAlumni }}</p>
                <p class="text-xs text-slate-500">Total Alumni</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $featuredCount }}</p>
                <p class="text-xs text-slate-500">Featured</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $employmentRate }}%</p>
                <p class="text-xs text-slate-500">Employment Rate</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $thisYearCount }}</p>
                <p class="text-xs text-slate-500">Added This Year</p>
            </div>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" action="{{ route('admin.alumni.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or ID…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"/>
        </div>
        <div class="w-40">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Department</label>
            <select name="department_id" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Program</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Grad Year</label>
            <select name="graduation_year" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All</option>
                @foreach($graduationYears as $y)
                    <option value="{{ $y }}" @selected(request('graduation_year') == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Status</label>
            <select name="employment_status" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All</option>
                <option value="employed" @selected(request('employment_status') === 'employed')>Employed</option>
                <option value="studying" @selected(request('employment_status') === 'studying')>Studying</option>
                <option value="freelancing" @selected(request('employment_status') === 'freelancing')>Freelancing</option>
                <option value="unemployed" @selected(request('employment_status') === 'unemployed')>Unemployed</option>
            </select>
        </div>
        <div class="w-28">
            <label class="text-xs font-semibold text-slate-500 mb-1 block">Featured</label>
            <select name="is_featured" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All</option>
                <option value="1" @selected(request('is_featured') === '1')>Yes</option>
                <option value="0" @selected(request('is_featured') === '0')>No</option>
            </select>
        </div>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 transition">Apply</button>
        <a href="{{ route('admin.alumni.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition">Clear</a>
    </form>
</div>

{{-- View Toggle --}}
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500">{{ $alumni->total() }} {{ Str::plural('result', $alumni->total()) }}</p>
    <div class="flex gap-1 rounded-lg border border-slate-200 bg-white p-0.5">
        <button @click="toggleView('table')" :class="view==='table' ? 'bg-slate-900 text-white' : 'text-slate-400 hover:text-slate-600'" class="rounded-md p-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        </button>
        <button @click="toggleView('cards')" :class="view==='cards' ? 'bg-slate-900 text-white' : 'text-slate-400 hover:text-slate-600'" class="rounded-md p-1.5 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
        </button>
    </div>
</div>

{{-- TABLE VIEW --}}
<div x-show="view==='table'" x-cloak>
    @if($alumni->count())
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Alumni</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Department &rarr; Program</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Grad Year</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Current Position</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500">Projects</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($alumni as $a)
                    @php
                        $st = $statusMap[$a->employment_status] ?? $statusMap['unknown'];
                        $grad = $gradients[$a->id % 6];
                        $hasMinor = $a->projects->contains('type', 'minor');
                        $hasMajor = $a->projects->contains('type', 'major');
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($a->user?->avatar)
                                    <img src="{{ asset('storage/'.$a->user->avatar) }}" class="h-9 w-9 rounded-xl object-cover ring-2 ring-slate-100"/>
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-xs font-bold text-white">{{ strtoupper(substr($a->user?->name ?? 'A', 0, 1)) }}</div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $a->user?->name }}</p>
                                    <p class="text-xs text-slate-500">ID: {{ $a->id }}
                                        @if($a->is_featured) <span class="ml-1 text-amber-500">★</span> @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-700">{{ $a->department?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $a->program?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">{{ $a->graduation_year }}</td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-slate-900">{{ $a->current_job ?? '—' }}</p>
                            @if($a->company_name)
                                <p class="text-xs text-slate-500">@ {{ $a->company_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-lg px-2 py-0.5 text-xs font-bold {{ $st['cls'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @if($hasMinor)
                                    <span class="rounded bg-cyan-50 px-1.5 py-0.5 text-[10px] font-bold text-cyan-700" title="Minor Project">m</span>
                                @endif
                                @if($hasMajor)
                                    <span class="rounded bg-violet-50 px-1.5 py-0.5 text-[10px] font-bold text-violet-700" title="Major Project">M</span>
                                @endif
                                @if(!$hasMinor && !$hasMajor)
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <x-table-actions
                                :show="route('admin.alumni.show', $a)"
                                :edit="route('admin.alumni.edit', $a)"
                                :destroy="route('admin.alumni.destroy', $a)"
                                name="{{ addslashes($a->user?->name ?? 'this alumni record') }}"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
        </div>
        <h3 class="mt-4 text-lg font-bold text-slate-900">No alumni found</h3>
        <p class="mt-1 text-sm text-slate-500">Alumni are automatically created when academic sessions end, or add manually.</p>
        <a href="{{ route('admin.alumni.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">Add Alumni</a>
    </div>
    @endif
</div>

{{-- CARD VIEW --}}
<div x-show="view==='cards'" x-cloak>
    @if($alumni->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($alumni as $a)
        @php
            $st = $statusMap[$a->employment_status] ?? $statusMap['unknown'];
            $grad = $gradients[$a->id % 6];
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-start gap-3 mb-3">
                @if($a->user?->avatar)
                    <img src="{{ asset('storage/'.$a->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-lg font-bold text-white">{{ strtoupper(substr($a->user?->name ?? 'A', 0, 1)) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <h3 class="font-bold text-slate-900 text-sm truncate">{{ $a->user?->name }}</h3>
                        @if($a->is_featured) <span class="text-amber-500 text-sm">★</span> @endif
                    </div>
                    <p class="text-xs text-slate-500">{{ $a->department?->name }} · {{ $a->graduation_year }}</p>
                </div>
            </div>
            @if($a->current_job)
                <p class="text-xs text-slate-700 mb-1">{{ $a->current_job }}@if($a->company_name) <span class="text-slate-400">@ {{ $a->company_name }}</span>@endif</p>
            @endif
            <div class="flex items-center gap-2 mb-3">
                <span class="rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $st['cls'] }}">{{ $st['label'] }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.alumni.show', $a) }}" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                <a href="{{ route('admin.alumni.edit', $a) }}" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Edit</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Pagination --}}
@if($alumni->hasPages())
<div class="mt-6">{{ $alumni->links() }}</div>
@endif

</div>
@endsection
