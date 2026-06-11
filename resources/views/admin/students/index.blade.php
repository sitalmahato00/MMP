@extends('layouts.app')
@section('title', 'Students')

@section('content')
@php
    $semColors = ['1'=>'violet','2'=>'blue','3'=>'indigo','4'=>'cyan','5'=>'teal','6'=>'emerald'];
    $statusMap = [
        'active'    => ['label'=>'Active',     'cls'=>'bg-blue-50 text-blue-700'],
        'inactive'  => ['label'=>'Inactive',   'cls'=>'bg-slate-100 text-slate-600'],
        'graduated' => ['label'=>'Alumni',     'cls'=>'bg-emerald-50 text-emerald-700'],
        'suspended' => ['label'=>'Suspended',  'cls'=>'bg-amber-50 text-amber-700'],
        'dropped'   => ['label'=>'Dropped',    'cls'=>'bg-red-50 text-red-700'],
    ];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_students_view') ?? 'table',
    selected: [],
    drawer: false,
    drawerLoading: false,
    drawerHtml: '',
    drawerStudentId: null,
    promoteModal: false,
    promoteLoading: false,
    promoteResult: null,
    setView(v) { this.view = v; localStorage.setItem('mmp_students_view', v); },
    toggleAll(ids) {
        if (this.selected.length === ids.length) { this.selected = []; }
        else { this.selected = ids; }
    },
    openDrawer(id) {
        if (this.drawerStudentId === id && this.drawer) return;
        this.drawerStudentId = id;
        this.drawer = true;
        this.drawerLoading = true;
        this.drawerHtml = '';
        fetch('/admin/students/' + id + '/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(r => r.text())
        .then(html => { this.drawerHtml = html; this.drawerLoading = false; })
        .catch(() => { this.drawerHtml = '<p class=\'p-8 text-center text-red-500\'>Failed to load.</p>'; this.drawerLoading = false; });
    },
    closeDrawer() { this.drawer = false; this.drawerStudentId = null; },
    confirmPromote() { this.promoteResult = null; this.promoteModal = true; },
    async bulkPromote() {
        this.promoteLoading = true;
        try {
            const res = await fetch('{{ route('admin.students.bulk-promote') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids: this.selected }),
            });
            const data = await res.json();
            this.promoteResult = data;
            this.promoteLoading = false;
            if (data.success) {
                this.selected = [];
                setTimeout(() => { window.location.reload(); }, 1800);
            }
        } catch (e) {
            this.promoteResult = { success: false, message: 'Request failed. Please try again.' };
            this.promoteLoading = false;
        }
    },
}" class="space-y-5"
   @keydown.escape.window="closeDrawer()"
   x-init="$watch('drawerHtml', () => { if (drawerHtml) $nextTick(() => { if (window.initDrawerCharts) window.initDrawerCharts(); }) })"
>

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Students</h1>
        <p class="mt-0.5 text-sm text-slate-500">Manage and monitor all students across departments and semesters</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.students.index', array_merge(request()->query(), ['export'=>1])) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </a>
        <a href="{{ route('admin.students.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Student
        </a>
    </div>
</div>

{{-- ── KPI CARDS ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Students',   'value'=>$totalStudents,  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color'=>'blue',   'tag'=>'Total'],
            ['label'=>'Active Students',  'value'=>$activeStudents, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                'color'=>'green',  'tag'=>'Active'],
            ['label'=>'This Session',     'value'=>$newThisSession, 'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                                                                                                                                                                                   'color'=>'violet', 'tag'=>'New'],
            ['label'=>'Alumni',           'value'=>$alumniCount,    'icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',                                                                                                                                                                                  'color'=>'amber',  'tag'=>'Graduated'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50">
                <svg class="w-5 h-5 text-{{ $kpi['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
            <span class="rounded-full bg-{{ $kpi['color'] }}-50 px-2 py-0.5 text-[11px] font-bold text-{{ $kpi['color'] }}-700">{{ $kpi['tag'] }}</span>
        </div>
        <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($kpi['value']) }}</p>
        <p class="mt-0.5 text-xs text-slate-500">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── FILTERS ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.students.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        {{-- Search --}}
        <div class="relative xl:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, ID, email…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"/>
        </div>
        {{-- Department --}}
        <select name="department_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Programs</option>
            @foreach($programs as $prog)
                <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->name }}</option>
            @endforeach
        </select>
        {{-- Semester --}}
        <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Semesters</option>
            @for($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}" @selected(request('semester') == $i)>Semester {{ $i }}</option>
            @endfor
        </select>
        {{-- Status + Apply --}}
        <div class="flex gap-2">
            <select name="status" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Status</option>
                <option value="active"    @selected(request('status') === 'active')>Active</option>
                <option value="inactive"  @selected(request('status') === 'inactive')>Inactive</option>
                <option value="graduated" @selected(request('status') === 'graduated')>Alumni</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                <option value="dropped"   @selected(request('status') === 'dropped')>Dropped</option>
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search','department_id','program_id','semester','status','academic_session_id']))
            <a href="{{ route('admin.students.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Panel header: result count + bulk actions + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-3 min-w-0">
            <template x-if="selected.length > 0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-800" x-text="selected.length + ' selected'"></span>
                    <button type="button" @click="confirmPromote()"
                            class="rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition">Promote</button>
                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Change Status</button>
                    <button type="button" @click="selected = []" class="rounded-lg border border-red-100 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">Clear</button>
                </div>
            </template>
            <template x-if="selected.length === 0">
                <p class="text-sm text-slate-500">
                    @if($students->total() > 0)
                        Showing <span class="font-semibold text-slate-700">{{ $students->firstItem() }}–{{ $students->lastItem() }}</span>
                        of <span class="font-semibold text-slate-700">{{ number_format($students->total()) }}</span> students
                    @else
                        No students match your filters
                    @endif
                </p>
            </template>
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
        @if($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No students found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters, or enroll a new student.</p>
                <a href="{{ route('admin.students.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Student
                </a>
            </div>
        @else
        @php $allIds = $students->pluck('id')->toJson(); @endphp
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="w-10 px-5 py-3 text-left">
                            <input type="checkbox" @change="toggleAll({{ $allIds }})"
                                   :checked="selected.length === {{ $students->count() }} && {{ $students->count() }} > 0"
                                   class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]"/>
                        </th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Student</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Department / Program</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Semester</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Session</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Enrolled</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden xl:table-cell">Guardian</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($students as $student)
                    @php
                        $st   = $statusMap[$student->status] ?? ['label'=>ucfirst($student->status),'cls'=>'bg-slate-100 text-slate-600'];
                        $initials = strtoupper(substr($student->user?->name ?? 'S', 0, 1));
                        $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
                        $grad = $gradients[$student->id % 6];
                    @endphp
                    <tr class="group hover:bg-slate-50/60 transition-colors"
                        :class="selected.includes({{ $student->id }}) ? 'bg-red-50/30' : ''">
                        <td class="px-5 py-3.5">
                            <input type="checkbox" :value="{{ $student->id }}" x-model="selected"
                                   class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]"/>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($student->user?->avatar)
                                    <img src="{{ asset('storage/'.$student->user->avatar) }}" alt=""
                                         class="h-9 w-9 flex-shrink-0 rounded-xl object-cover ring-2 ring-white shadow-sm"/>
                                @else
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-sm font-black text-white shadow-sm">{{ $initials }}</div>
                                @endif
                                <div class="min-w-0">
                                    <button type="button" @click="openDrawer({{ $student->id }})"
                                            class="block font-semibold text-slate-900 hover:text-[#8B0000] truncate transition text-sm text-left">{{ $student->user?->name }}</button>
                                    <p class="font-mono text-[11px] text-slate-400 truncate">{{ $student->student_no ?? '—' }}</p>
                                    <p class="text-[11px] text-slate-400 truncate hidden sm:block">{{ $student->user?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm font-medium text-slate-700 truncate max-w-[160px]">{{ $student->program?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $student->department?->name ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                                Sem {{ $student->current_semester }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                            {{ $student->academicSession?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold {{ $st['cls'] }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                {{ $st['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                            {{ bsDate($student->created_at, 'Y, F d') }}
                        </td>
                        <td class="px-5 py-3.5 text-xs hidden xl:table-cell">
                            @if($student->guardian_name)
                                <p class="font-medium text-slate-700">{{ $student->guardian_name }}</p>
                                <p class="text-slate-400">{{ $student->guardian_phone ?: 'Guardian contact' }}</p>
                            @else
                                <span class="text-slate-300 text-xs">Not linked</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <x-table-actions
                                :show="route('admin.students.show', $student)"
                                :edit="route('admin.students.edit', $student)"
                                :destroy="route('admin.students.destroy', $student)"
                                name="{{ addslashes($student->user?->name ?? 'this student') }}"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $students->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No students found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or enroll a new student.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($students as $student)
            @php
                $st   = $statusMap[$student->status] ?? ['label'=>ucfirst($student->status),'cls'=>'bg-slate-100 text-slate-600'];
                $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
                $grad = $gradients[$student->id % 6];
            @endphp
            <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                {{-- Checkbox --}}
                <div class="absolute top-3.5 right-3.5">
                    <input type="checkbox" :value="{{ $student->id }}" x-model="selected"
                           class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]"/>
                </div>
                {{-- Avatar --}}
                <div class="flex flex-col items-center text-center">
                    @if($student->user?->avatar)
                        <img src="{{ asset('storage/'.$student->user->avatar) }}" alt=""
                             class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow"/>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl font-black text-white shadow">
                            {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                    <button type="button" @click="openDrawer({{ $student->id }})"
                            class="mt-3 text-sm font-bold text-slate-900 hover:text-[#8B0000] transition leading-tight pr-5 text-center">{{ $student->user?->name }}</button>
                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $student->student_no ?? '—' }}</p>
                </div>
                {{-- Badges --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                    <span class="rounded-lg bg-violet-50 px-2 py-0.5 text-[11px] font-bold text-violet-700">Sem {{ $student->current_semester }}</span>
                    <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $st['cls'] }}">{{ $st['label'] }}</span>
                </div>
                {{-- Program info --}}
                <div class="mt-3 space-y-0.5 text-center">
                    <p class="text-xs text-slate-600 font-medium truncate">{{ $student->program?->name ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $student->department?->name ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400">{{ $student->academicSession?->name ?? '—' }}</p>
                </div>
                {{-- Actions --}}
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.students.show', $student) }}"
                       class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                    <a href="{{ route('admin.students.edit', $student) }}"
                       class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                </div>
            </div>
            @endforeach
        </div>
        @if($students->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $students->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

{{-- ── STUDENT DETAIL DRAWER ──────────────────────────────── --}}
{{-- Backdrop --}}
<div x-show="drawer"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="closeDrawer()"
     class="fixed inset-0 z-40 bg-black/30 backdrop-blur-[2px]"
     x-cloak></div>

{{-- Panel --}}
<div x-show="drawer"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full"
     class="fixed inset-y-0 right-0 z-50 flex w-full max-w-2xl flex-col bg-white shadow-2xl"
     x-cloak>

    {{-- Close button --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 flex-shrink-0">
        <h2 class="text-base font-bold text-slate-900">Student Profile</h2>
        <button type="button" @click="closeDrawer()"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto">
        <div x-show="drawerLoading" class="flex items-center justify-center py-24">
            <svg class="h-8 w-8 animate-spin text-[#8B0000]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
        </div>
        <div x-show="!drawerLoading" x-html="drawerHtml"></div>
    </div>
</div>

{{-- ── PROMOTE CONFIRM MODAL ──────────────────────────────── --}}
<div x-show="promoteModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     @keydown.escape.window="if(!promoteLoading) promoteModal = false">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"
         @click="if(!promoteLoading) promoteModal = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Result state --}}
        <template x-if="promoteResult">
            <div class="p-8 text-center">
                <div :class="promoteResult.success ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'"
                     class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl">
                    <svg x-show="promoteResult.success" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="!promoteResult.success" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <p class="text-base font-bold text-slate-800" x-text="promoteResult.success ? 'Done!' : 'Error'"></p>
                <p class="mt-1 text-sm text-slate-500" x-text="promoteResult.message"></p>
                <template x-if="!promoteResult.success">
                    <button type="button" @click="promoteModal = false; promoteResult = null"
                            class="mt-5 rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Close</button>
                </template>
            </div>
        </template>

        {{-- Confirm state --}}
        <template x-if="!promoteResult">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-600 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <h3 class="text-base font-black text-slate-900">Promote Students?</h3>
                <p class="mt-1.5 text-sm text-slate-500">
                    <span x-text="selected.length"></span> selected student(s) will be moved to the next semester.
                    Students already in <strong>Semester 6</strong> will be marked as <strong>Graduated</strong>.
                </p>
                <p class="mt-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-2.5 text-xs text-amber-700">
                    This action is <strong>not reversible</strong> without manually editing each student.
                </p>
                <div class="mt-5 flex gap-3">
                    <button type="button" :disabled="promoteLoading" @click="promoteModal = false"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition disabled:opacity-50">
                        Cancel
                    </button>
                    <button type="button" :disabled="promoteLoading" @click="bulkPromote()"
                            class="flex-1 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition disabled:opacity-60 flex items-center justify-center gap-2">
                        <svg x-show="promoteLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        <span x-text="promoteLoading ? 'Promoting…' : 'Yes, Promote'"></span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

</div>{{-- /x-data --}}
@endsection
