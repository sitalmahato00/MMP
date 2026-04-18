@extends('layouts.app')
@section('title', 'Teachers')

@section('content')
@php
    $desigMap = [
        'HOD'         => ['label' => 'HOD',         'cls' => 'bg-purple-50 text-purple-700 ring-purple-200'],
        'Coordinator' => ['label' => 'Coordinator',  'cls' => 'bg-blue-50 text-blue-700 ring-blue-200'],
        'Teacher'     => ['label' => 'Teacher',      'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'],
    ];
    $empMap = [
        'permanent' => ['label' => 'Permanent', 'cls' => 'bg-emerald-50 text-emerald-700'],
        'contract'  => ['label' => 'Contract',  'cls' => 'bg-amber-50 text-amber-700'],
        'part-time' => ['label' => 'Part-time', 'cls' => 'bg-sky-50 text-sky-700'],
    ];
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_teachers_view') ?? 'table',
    selected: [],
    drawer: false,
    drawerLoading: false,
    drawerHtml: '',
    drawerTeacherId: null,
    bulkModal: false,
    bulkAction: '',
    bulkLoading: false,
    bulkResult: null,
    setView(v) { this.view = v; localStorage.setItem('mmp_teachers_view', v); },
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
        fetch('/admin/teachers/' + id + '/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(r => r.text())
        .then(html => { this.drawerHtml = html; this.drawerLoading = false; })
        .catch(() => { this.drawerHtml = '<p class=\'p-8 text-center text-red-500\'>Failed to load.</p>'; this.drawerLoading = false; });
    },
    closeDrawer() { this.drawer = false; this.drawerTeacherId = null; },
    confirmBulk(action) { this.bulkAction = action; this.bulkResult = null; this.bulkModal = true; },
    async doBulkAction() {
        this.bulkLoading = true;
        try {
            const res = await fetch('{{ route('admin.teachers.bulk-action') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: this.selected, action: this.bulkAction }),
            });
            const data = await res.json();
            this.bulkResult = data;
            this.bulkLoading = false;
            if (data.success) { this.selected = []; setTimeout(() => window.location.reload(), 1600); }
        } catch(e) {
            this.bulkResult = { success: false, message: 'Request failed. Please try again.' };
            this.bulkLoading = false;
        }
    },
}" class="space-y-5"
   @keydown.escape.window="closeDrawer()"
   x-init=""
>

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Teachers</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage teacher profiles, workloads, subjects, and performance analytics.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.teachers.index', array_merge(request()->query(), ['export'=>1])) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </a>
        <a href="{{ route('admin.teachers.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Teacher
        </a>
    </div>
</div>

{{-- ── KPI CARDS ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Teachers',     'value'=>$totalTeachers,  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'blue',   'tag'=>'Faculty'],
            ['label'=>'Active',             'value'=>$activeTeachers, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                'color'=>'green',  'tag'=>'Active'],
            ['label'=>'HODs',               'value'=>$hodCount,       'icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',                                                                                                                                                                                  'color'=>'purple', 'tag'=>'Department Heads'],
            ['label'=>'Subjects Assigned',  'value'=>$totalSubjects,  'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',                                        'color'=>'indigo', 'tag'=>'Total'],
            ['label'=>'Avg Sessions/Month', 'value'=>$avgSessions,   'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                                                                                                                                                                                   'color'=>'amber',  'tag'=>'Attendance'],
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
            <span class="rounded-full bg-{{ $kpi['color'] }}-50 px-2 py-0.5 text-[11px] font-bold text-{{ $kpi['color'] }}-600">{{ $kpi['tag'] }}</span>
        </div>
        <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
        <p class="mt-0.5 text-sm text-slate-500">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── FILTER BAR ──────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.teachers.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-3">
        <div class="relative min-w-[220px] flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, email, employee ID…"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"/>
        </div>
        <select name="department_id" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
        <select name="designation" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
            <option value="">All Roles</option>
            <option value="Teacher"     {{ request('designation') === 'Teacher'     ? 'selected' : '' }}>Teacher</option>
            <option value="HOD"         {{ request('designation') === 'HOD'         ? 'selected' : '' }}>HOD</option>
            <option value="Coordinator" {{ request('designation') === 'Coordinator' ? 'selected' : '' }}>Coordinator</option>
        </select>
        <select name="employment_type" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
            <option value="">All Types</option>
            <option value="permanent" {{ request('employment_type') === 'permanent' ? 'selected' : '' }}>Permanent</option>
            <option value="contract"  {{ request('employment_type') === 'contract'  ? 'selected' : '' }}>Contract</option>
            <option value="part-time" {{ request('employment_type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <select name="semester" class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-8 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
            <option value="">All Semesters</option>
            @for($s = 1; $s <= 6; $s++)
            <option value="{{ $s }}" {{ request('semester') == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
            @endfor
        </select>
        <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition shadow-sm">Apply</button>
        @if(request()->hasAny(['search','department_id','designation','employment_type','status','semester']))
        <a href="{{ route('admin.teachers.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear">✕</a>
        @endif
    </div>
</form>

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-3 min-w-0">
            <template x-if="selected.length > 0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-bold text-slate-800" x-text="selected.length + ' selected'"></span>
                    <button type="button" @click="confirmBulk('activate')"      class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 transition">Activate</button>
                    <button type="button" @click="confirmBulk('deactivate')"    class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition">Deactivate</button>
                    <button type="button" @click="confirmBulk('set_hod')"       class="rounded-lg border border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-100 transition">Make HOD</button>
                    <button type="button" @click="confirmBulk('set_coordinator')" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">Make Coordinator</button>
                    <button type="button" @click="confirmBulk('set_teacher')"   class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Set Teacher</button>
                    <button type="button" @click="selected = []"                class="rounded-lg border border-red-100 px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 transition">Clear</button>
                </div>
            </template>
            <template x-if="selected.length === 0">
                <p class="text-sm text-slate-500">
                    @if($teachers->total() > 0)
                        Showing <span class="font-semibold text-slate-700">{{ $teachers->firstItem() }}–{{ $teachers->lastItem() }}</span>
                        of <span class="font-semibold text-slate-700">{{ number_format($teachers->total()) }}</span> teachers
                    @else
                        No teachers match your filters
                    @endif
                </p>
            </template>
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
            <a href="{{ route('admin.teachers.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">+ Add Teacher</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60">
                    <tr>
                        <th class="w-10 px-4 py-3">
                            @php $allIds = $teachers->pluck('id')->all(); @endphp
                            <input type="checkbox" class="rounded border-slate-300"
                                   :checked="selected.length === {{ count($allIds) }} && {{ count($allIds) }} > 0"
                                   @change="toggleAll({{ json_encode($allIds) }})"/>
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Teacher</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden md:table-cell">Department</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Role</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Subjects</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Semesters</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden xl:table-cell">Employment</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($teachers as $teacher)
                    @php
                        $grad    = $gradients[$teacher->id % 6];
                        $desig   = $desigMap[$teacher->designation] ?? ['label' => $teacher->designation ?? 'Teacher', 'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'];
                        $emp     = $empMap[$teacher->employment_type] ?? ['label' => ucfirst($teacher->employment_type ?? ''), 'cls' => 'bg-slate-100 text-slate-600'];
                        $semesters = $teacher->subjects->pluck('semester')->unique()->sort()->values();
                    @endphp
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="rounded border-slate-300"
                                   :checked="selected.includes({{ $teacher->id }})"
                                   @change="selected.includes({{ $teacher->id }}) ? selected = selected.filter(i => i !== {{ $teacher->id }}) : selected.push({{ $teacher->id }})"/>
                        </td>
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
                                            class="font-semibold text-slate-800 hover:text-[#8B0000] transition truncate block text-left">
                                        {{ $teacher->user?->name }}
                                    </button>
                                    <p class="text-xs text-slate-400 truncate">{{ $teacher->employee_id ?? $teacher->user?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <p class="text-sm font-medium text-slate-700">{{ $teacher->department?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $desig['cls'] }}">{{ $desig['label'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            <span class="font-bold text-slate-700">{{ $teacher->subjects->count() }}</span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            @if($semesters->isEmpty())
                                <span class="block text-center text-slate-400 text-xs">—</span>
                            @else
                                <div class="flex flex-wrap justify-center gap-1">
                                    @foreach($semesters as $sem)
                                    <span class="rounded-full bg-violet-50 px-1.5 py-0.5 text-[10px] font-bold text-violet-600">{{ $sem }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 hidden xl:table-cell">
                            <span class="rounded-lg px-2 py-1 text-xs font-semibold {{ $emp['cls'] }}">{{ $emp['label'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($teacher->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" @click="openDrawer({{ $teacher->id }})" class="rounded-lg bg-slate-100 p-1.5 text-slate-500 hover:bg-slate-200 transition" title="Quick view">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="rounded-lg bg-blue-50 p-1.5 text-blue-600 hover:bg-blue-100 transition" title="Full page">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="rounded-lg bg-violet-50 p-1.5 text-violet-600 hover:bg-violet-100 transition" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}"
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
                $emp   = $empMap[$teacher->employment_type] ?? ['label' => ucfirst($teacher->employment_type ?? ''), 'cls' => 'bg-slate-100 text-slate-600'];
                $semesters = $teacher->subjects->pluck('semester')->unique()->sort()->values();
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
                        <input type="checkbox" class="rounded border-slate-300 mt-0.5 flex-shrink-0"
                               :checked="selected.includes({{ $teacher->id }})"
                               @click.stop @change.stop="selected.includes({{ $teacher->id }}) ? selected = selected.filter(i => i !== {{ $teacher->id }}) : selected.push({{ $teacher->id }})"/>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold ring-1 {{ $desig['cls'] }}">{{ $desig['label'] }}</span>
                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $emp['cls'] }}">{{ $emp['label'] }}</span>
                    </div>
                    <p class="mb-2 text-xs text-slate-500 truncate">
                        <svg class="inline w-3.5 h-3.5 mr-1 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $teacher->department?->name ?? '—' }}
                    </p>
                    @if($semesters->isNotEmpty())
                    <div class="mb-2 flex flex-wrap gap-1">
                        @foreach($semesters as $sem)
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-600">Sem {{ $sem }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3">
                        <div class="text-center">
                            <p class="text-lg font-black text-slate-800">{{ $teacher->subjects->count() }}</p>
                            <p class="text-[10px] text-slate-400">Subjects</p>
                        </div>
                        <div class="text-center border-l border-slate-100">
                            <p class="text-lg font-black {{ $teacher->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $teacher->is_active ? 'Active' : 'Inactive' }}</p>
                            <p class="text-[10px] text-slate-400">Status</p>
                        </div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 flex items-center justify-end gap-1.5 px-4 py-2.5 bg-white border-t border-slate-100
                            opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-200">
                    <a href="{{ route('admin.teachers.show', $teacher) }}" @click.stop class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 transition">View</a>
                    <a href="{{ route('admin.teachers.edit', $teacher) }}" @click.stop class="rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-600 hover:bg-violet-100 transition">Edit</a>
                </div>
            </div>
            @endforeach
        </div>
        @if($teachers->hasPages())
        <div class="mt-6">{{ $teachers->links() }}</div>
        @endif
        @endif
    </div>
</div>

{{-- DRAWER --}}
<div x-show="drawer" x-cloak>
    <div class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm" @click="closeDrawer()"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
    <div class="fixed right-0 top-0 z-50 h-full w-full max-w-2xl overflow-y-auto bg-white shadow-2xl"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-full">
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/95 backdrop-blur-sm px-5 py-4">
            <p class="font-bold text-slate-800">Teacher Profile</p>
            <button type="button" @click="closeDrawer()" class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div x-show="drawerLoading" class="flex items-center justify-center py-20">
            <svg class="w-8 h-8 animate-spin text-[#8B0000]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </div>
        <div x-show="!drawerLoading" x-html="drawerHtml"></div>
    </div>
</div>

{{-- BULK MODAL --}}
<div x-show="bulkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
     @keydown.escape.window="if(!bulkLoading) bulkModal = false">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="if(!bulkLoading) bulkModal = false"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
    <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <template x-if="bulkResult">
            <div class="p-8 text-center">
                <div :class="bulkResult.success ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'" class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl">
                    <svg x-show="bulkResult.success" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="!bulkResult.success" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <p class="text-base font-bold text-slate-800" x-text="bulkResult.success ? 'Done!' : 'Error'"></p>
                <p class="mt-1 text-sm text-slate-500" x-text="bulkResult.message"></p>
                <template x-if="!bulkResult.success">
                    <button type="button" @click="bulkModal = false; bulkResult = null" class="mt-5 rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Close</button>
                </template>
            </div>
        </template>
        <template x-if="!bulkResult">
            <div class="p-6">
                <h3 class="text-base font-black text-slate-900">Confirm Bulk Action</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    Apply <strong x-text="bulkAction.replace(/_/g,' ')"></strong> to <strong x-text="selected.length"></strong> selected teacher(s)?
                </p>
                <div class="mt-5 flex gap-3">
                    <button type="button" :disabled="bulkLoading" @click="bulkModal = false" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition disabled:opacity-50">Cancel</button>
                    <button type="button" :disabled="bulkLoading" @click="doBulkAction()" class="flex-1 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition disabled:opacity-60 flex items-center justify-center gap-2">
                        <svg x-show="bulkLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        <span x-text="bulkLoading ? 'Applying…' : 'Confirm'"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

</div>{{-- /x-data --}}
@endsection
