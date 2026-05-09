@extends('layouts.app')
@section('title', 'Teachers')

@section('content')
@php
    $desigMap = [
        'HOD'     => ['label' => 'HOD',     'cls' => 'bg-purple-50 text-purple-700 ring-purple-200'],
        'Teacher' => ['label' => 'Teacher', 'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'],
    ];
    $empMap = [
        'permanent' => ['label' => 'Permanent', 'cls' => 'bg-emerald-50 text-emerald-700'],
        'contract'  => ['label' => 'Contract',  'cls' => 'bg-amber-50 text-amber-700'],
        'part-time' => ['label' => 'Part-time', 'cls' => 'bg-sky-50 text-sky-700'],
    ];
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_hod_teachers_view') ?? 'table',
    selected: [],
    drawer: false,
    drawerLoading: false,
    drawerHtml: '',
    drawerTeacherId: null,
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_teachers_view', v); },
    toggleAll(ids) {
        if (this.selected.length === ids.length) this.selected = [];
        else this.selected = [...ids];
    },
    openDrawer(id) {
        if (this.drawerTeacherId === id && this.drawer) return;
        this.drawerTeacherId = id;
        this.drawer = true;
        this.drawerLoading = true;
        this.drawerHtml = '';
        fetch('/hod/teachers/' + id + '/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(r => r.text())
        .then(html => { this.drawerHtml = html; this.drawerLoading = false; })
        .catch(() => { this.drawerHtml = '<p class=\'p-8 text-center text-red-500\'>Failed to load.</p>'; this.drawerLoading = false; });
    },
    closeDrawer() { this.drawer = false; this.drawerTeacherId = null; },
}" class="space-y-5"
   @keydown.escape.window="closeDrawer()"
>

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Teachers</h1>
        <p class="mt-0.5 text-sm text-slate-500">{{ $department->name }} Department</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('hod.teachers.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Teacher
        </a>
    </div>
</div>

{{-- ── KPI CARDS ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Teachers',   'value'=>$totalTeachers,  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'blue'],
            ['label'=>'Active',           'value'=>$activeTeachers, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'green'],
            ['label'=>'HOD',              'value'=>$hodCount,       'icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'color'=>'purple'],
            ['label'=>'Regular Teachers', 'value'=>$regularTeachers,'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color'=>'indigo'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50">
                <svg class="w-5 h-5 text-{{ $kpi['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
        </div>
        <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
        <p class="mt-0.5 text-sm text-slate-500">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── FILTER BAR ──────────────────────────────────────────── --}}
<form method="GET" action="{{ route('hod.teachers.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="relative min-w-[220px] flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, email, employee ID…"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"/>
        </div>
        <select name="designation" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
            <option value="">All Designations</option>
            <option value="Teacher" {{ request('designation') === 'Teacher' ? 'selected' : '' }}>Teacher</option>
            <option value="HOD"     {{ request('designation') === 'HOD'     ? 'selected' : '' }}>HOD</option>
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
            <option value="">All Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="rounded-xl bg-[#1d4ed8] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition shadow-sm">Filter</button>
        @if(request()->hasAny(['search', 'designation', 'status']))
        <a href="{{ route('hod.teachers.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
            Clear
        </a>
        @endif
    </div>
</form>

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-3 min-w-0">
            <p class="text-sm text-slate-500">
                @if($teachers->total() > 0)
                    Showing <span class="font-semibold text-slate-700">{{ $teachers->firstItem() }}–{{ $teachers->lastItem() }}</span>
                    of <span class="font-semibold text-slate-700">{{ number_format($teachers->total()) }}</span> teachers
                @else
                    No teachers found
                @endif
            </p>
        </div>
        <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('table')" :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                Table
            </button>
            <button type="button" @click="setView('cards')" :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="view === 'table'" x-cloak>
        @if($teachers->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">No teachers found</h3>
            <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or add a new teacher.</p>
            <a href="{{ route('hod.teachers.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white hover:bg-[#1e40af] transition">+ Add Teacher</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Teacher</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Designation</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Contact</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($teachers as $teacher)
                    @php
                        $grad = $gradients[$teacher->id % 6];
                        $desig = $desigMap[$teacher->designation] ?? ['label' => $teacher->designation ?? 'Teacher', 'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'];
                    @endphp
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($teacher->user?->avatar)
                                    <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt="" class="h-9 w-9 rounded-xl object-cover flex-shrink-0 ring-2 ring-white shadow-sm"/>
                                @else
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-sm font-black text-white shadow-sm">
                                        {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <button type="button" @click="openDrawer({{ $teacher->id }})"
                                            class="font-semibold text-slate-800 hover:text-[#1d4ed8] transition truncate block text-left">
                                        {{ $teacher->user?->name }}
                                    </button>
                                    <p class="text-xs text-slate-400 truncate">{{ $teacher->employee_id ?? $teacher->user?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $desig['cls'] }}">{{ $desig['label'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-slate-700">{{ $teacher->user?->email }}</p>
                            @if($teacher->user?->phone)
                                <p class="text-xs text-slate-400">{{ $teacher->user->phone }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $isActive = $teacher->is_active;
                                $statusText = $isActive ? 'Active' : 'Inactive';
                                $statusClass = $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="openDrawer({{ $teacher->id }})" class="rounded-lg bg-slate-100 p-1.5 text-slate-500 hover:bg-slate-200 transition" title="Quick view">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="{{ route('hod.teachers.show', $teacher) }}" class="rounded-lg bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 transition" title="Full page">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <a href="{{ route('hod.teachers.edit', $teacher) }}" class="rounded-lg bg-violet-50 p-1.5 text-violet-600 hover:bg-violet-100 transition" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('hod.teachers.destroy', $teacher) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ addslashes($teacher->user?->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-100 transition" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($teachers->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $teachers->links() }}</div>
        @endif
        @endif
    </div>

    {{-- CARD VIEW --}}
    <div x-show="view === 'cards'" x-cloak class="p-5">
        @if($teachers->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm font-medium text-slate-500">No teachers found.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($teachers as $teacher)
            @php
                $grad = $gradients[$teacher->id % 6];
                $desig = $desigMap[$teacher->designation] ?? ['label' => 'Teacher', 'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'];
            @endphp
            <div class="group relative rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition-all cursor-pointer"
                 @click="openDrawer({{ $teacher->id }})">
                <div class="h-1 w-full {{ $teacher->is_active ? 'bg-emerald-400' : 'bg-slate-300' }}"></div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-3">
                            @if($teacher->user?->avatar)
                                <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt="" class="h-12 w-12 rounded-xl object-cover ring-2 ring-white shadow"/>
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-lg font-black text-white shadow">
                                    {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-bold text-slate-800 truncate">{{ $teacher->user?->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $teacher->employee_id ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold ring-1 {{ $desig['cls'] }}">{{ $desig['label'] }}</span>
                        @if($teacher->employment_type)
                            @php $emp = $empMap[$teacher->employment_type] ?? ['label' => ucfirst($teacher->employment_type), 'cls' => 'bg-slate-100 text-slate-600']; @endphp
                            <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $emp['cls'] }}">{{ $emp['label'] }}</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Status</span>
                        @php
                            $isActive = $teacher->is_active;
                            $statusText = $isActive ? 'Active' : 'Inactive';
                            $statusClass = $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ── DRAWER ──────────────────────────────────────────────── --}}
<div x-show="drawer" x-cloak
     class="fixed inset-0 z-50 overflow-hidden"
     @keydown.escape.window="closeDrawer()">
    <div class="absolute inset-0 bg-black/20" @click="closeDrawer()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Teacher Details</h2>
                <button type="button" @click="closeDrawer()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto">
                <div x-show="drawerLoading" class="flex items-center justify-center py-12">
                    <div class="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-[#1d4ed8]"></div>
                </div>
                <div x-show="!drawerLoading" x-html="drawerHtml"></div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /x-data --}}
@endsection