@extends('layouts.app')
@section('title', 'Administrative Staff')

@section('content')
@php
    $employmentMap = [
        'full_time' => ['label' => 'Full Time', 'cls' => 'bg-blue-50 text-blue-700'],
        'part_time' => ['label' => 'Part Time', 'cls' => 'bg-violet-50 text-violet-700'],
        'contract' => ['label' => 'Contract', 'cls' => 'bg-amber-50 text-amber-700'],
        'temporary' => ['label' => 'Temporary', 'cls' => 'bg-slate-100 text-slate-600'],
    ];
    $statusMap = [
        'active' => ['label' => 'Active', 'cls' => 'bg-emerald-50 text-emerald-700'],
        'leave' => ['label' => 'Leave', 'cls' => 'bg-amber-50 text-amber-700'],
        'resigned' => ['label' => 'Resigned', 'cls' => 'bg-rose-50 text-rose-700'],
    ];
    $gradients = ['from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600', 'from-cyan-500 to-sky-600'];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_staff_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_staff_view', v); },
}" class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Administrative Staff</h1>
            <p class="mt-0.5 text-sm text-slate-500">Manage staff profiles, working schedules, documents, and public visibility.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.staff.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Staff
            </a>
            <a href="{{ route('admin.staff.export.csv', request()->except('page')) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.staff.export.pdf', request()->except('page')) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.staff.import') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        @csrf
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Import CSV</label>
                <input type="file" name="csv" accept=".csv,text/csv"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[#8B0000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-[#8B0000] focus:ring-[#8B0000]/20">
            </div>
            <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">Import Staff CSV</button>
            <p class="text-sm text-slate-500">Upload the staff CSV schema to create or update records.</p>
        </div>
    </form>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @php
            $kpis = [
                ['label' => 'Total Staff', 'value' => $totalStaff, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'blue'],
                ['label' => 'Active', 'value' => $activeStaff, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'green'],
                ['label' => 'Resigned', 'value' => $resignedStaff, 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'color' => 'amber'],
                ['label' => 'This Year', 'value' => $addedThisYear, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'violet'],
                ['label' => 'Top Department', 'value' => $topDepartment?->department ?? 'None', 'meta' => $topDepartment?->total ? $topDepartment->total . ' staff' : 'No records yet', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'color' => 'slate'],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50">
                    <svg class="h-5 w-5 text-{{ $kpi['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                    </svg>
                </div>
                <span class="rounded-full bg-{{ $kpi['color'] }}-50 px-2 py-0.5 text-[11px] font-bold text-{{ $kpi['color'] }}-700">{{ $kpi['label'] === 'Top Department' ? ($kpi['meta'] ?? 'Staff') : 'Staff' }}</span>
            </div>
            @if(is_numeric($kpi['value']))
                <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($kpi['value']) }}</p>
            @else
                <p class="mt-3 text-2xl font-black tracking-tight text-slate-900">{{ $kpi['value'] }}</p>
            @endif
            <p class="mt-0.5 text-sm text-slate-500">{{ $kpi['label'] }}</p>
            @if(! is_numeric($kpi['value']) && ! empty($kpi['meta']))
                <p class="mt-1 text-xs text-slate-400">{{ $kpi['meta'] }}</p>
            @endif
        </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('admin.staff.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="relative xl:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search staff code, name, email..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            </div>

            <select name="department" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                @endforeach
            </select>

            <select name="designation" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Designations</option>
                @foreach($designations as $designation)
                    <option value="{{ $designation }}" @selected(request('designation') === $designation)>{{ $designation }}</option>
                @endforeach
            </select>

            <select name="employment_status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Status</option>
                @foreach(['active' => 'Active', 'leave' => 'Leave', 'resigned' => 'Resigned'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('employment_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="joined_year" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All BS Years</option>
                @foreach($joinedYears as $year)
                    <option value="{{ $year }}" @selected(request('joined_year') === $year)>{{ $year }}</option>
                @endforeach
            </select>

            <select name="featured" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Featured</option>
                <option value="1" @selected(request('featured') === '1')>Featured only</option>
                <option value="0" @selected(request('featured') === '0')>Not featured</option>
            </select>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                @if($staff->total() > 0)
                    Showing <span class="font-semibold text-slate-700">{{ $staff->firstItem() }}–{{ $staff->lastItem() }}</span>
                    of <span class="font-semibold text-slate-700">{{ number_format($staff->total()) }}</span> staff records
                @else
                    No staff records match your filters
                @endif
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#7a0000] shadow-sm">Apply Filters</button>
                <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Staff directory</h2>
                <p class="text-sm text-slate-500">Review records, toggle visibility, and jump to documents.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-500">{{ $staff->total() }} records</div>
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
        </div>

        {{-- TABLE VIEW --}}
        <div x-show="view === 'table'" x-cloak>
        <div class="mmp-table-wrap">
            <table class="mmp-table divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50/80">
                    <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-6 py-4">Staff</th>
                        <th class="px-6 py-4">Employment</th>
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Visibility</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($staff as $member)
                        @php
                            $employment = $employmentMap[$member->employment_type] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $member->employment_type)) ?: 'Unspecified', 'cls' => 'bg-slate-100 text-slate-600'];
                            $status = $statusMap[$member->employment_status] ?? ['label' => ucfirst((string) $member->employment_status) ?: 'Active', 'cls' => 'bg-slate-100 text-slate-600'];
                            $gradient = $gradients[$member->id % count($gradients)];
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-5 align-top">
                                <div class="flex items-start gap-4">
                                    <div class="h-14 w-14 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                                        @if($member->photo_url)
                                            <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br {{ $gradient }} text-sm font-black text-white">
                                                {{ strtoupper(substr($member->name ?? 'S', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $member->name }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $member->staff_code }}</div>
                                        <div class="mt-2 text-sm font-medium text-[#8B0000]">{{ $member->designation ?? 'Staff' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 align-top text-sm text-slate-600">
                                <div>{{ $member->department ?: 'General Administration' }}</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $employment['cls'] }}">{{ $employment['label'] }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $status['cls'] }}">{{ $status['label'] }}</span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">Joined {{ $member->join_date ? bsDate($member->join_date, 'Y F d') : '—' }}</div>
                            </td>
                            <td class="px-6 py-5 align-top text-sm text-slate-600">
                                <div>{{ $member->email ?: '—' }}</div>
                                <div class="mt-1">{{ $member->phone ?: '—' }}</div>
                                <div class="mt-2 text-xs text-slate-500">{{ $member->documents_count ?? 0 }} documents</div>
                            </td>
                            <td class="px-6 py-5 align-top">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $member->public_visible ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500' }}">{{ $member->public_visible ? 'Public' : 'Hidden' }}</span>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $member->featured ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ $member->featured ? 'Featured' : 'Standard' }}</span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">
                                    {{ $member->show_email_public ? 'Email visible' : 'Email private' }} · {{ $member->show_phone_public ? 'Phone visible' : 'Phone private' }}
                                </div>
                            </td>
                            <td class="px-6 py-5 align-top">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.staff.show', $member) }}" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">View</a>
                                    <a href="{{ route('admin.staff.edit', $member) }}" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Edit</a>
                                    <a href="{{ route('admin.staff.documents', $member) }}" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Docs</a>

                                    <form method="POST" action="{{ route('admin.staff.toggle-public', $member) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{{ $member->public_visible ? 'Hide' : 'Publish' }}</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.staff.toggle-featured', $member) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{{ $member->featured ? 'Unfeature' : 'Feature' }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-sm text-slate-500">No staff records match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $staff->links() }}
        </div>
        </div>

        {{-- CARD VIEW --}}
        <div x-show="view === 'cards'" x-cloak class="p-5">
            @if($staff->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <p class="text-sm font-medium text-slate-500">No staff records found.</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($staff as $member)
                    @php
                        $employment = $employmentMap[$member->employment_type] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $member->employment_type)) ?: 'Unspecified', 'cls' => 'bg-slate-100 text-slate-600'];
                        $status = $statusMap[$member->employment_status] ?? ['label' => ucfirst((string) $member->employment_status) ?: 'Active', 'cls' => 'bg-slate-100 text-slate-600'];
                        $gradient = $gradients[$member->id % count($gradients)];
                    @endphp
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                        <div class="flex flex-col items-center text-center">
                            <div class="h-16 w-16 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                                @if($member->photo_url)
                                    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br {{ $gradient }} text-xl font-black text-white">
                                        {{ strtoupper(substr($member->name ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-slate-900">{{ $member->name }}</h3>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $member->staff_code }}</p>
                            <p class="mt-1 text-xs font-medium text-[#8B0000]">{{ $member->designation ?? 'Staff' }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $employment['cls'] }}">{{ $employment['label'] }}</span>
                            <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $status['cls'] }}">{{ $status['label'] }}</span>
                        </div>
                        <div class="mt-3 space-y-0.5 text-center">
                            <p class="text-xs text-slate-600">{{ $member->department ?: 'General Administration' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $member->email ?: '—' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $member->phone ?: '—' }}</p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $member->public_visible ? 'bg-sky-50 text-sky-700' : 'bg-slate-100 text-slate-500' }}">{{ $member->public_visible ? 'Public' : 'Hidden' }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $member->featured ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ $member->featured ? 'Featured' : 'Standard' }}</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.staff.show', $member) }}" class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                            <a href="{{ route('admin.staff.edit', $member) }}" class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-100 mt-5 pt-4">
                {{ $staff->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
