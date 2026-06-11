@extends('layouts.app')
@section('title', $student->user->name)

@section('content')
@php
    use Carbon\Carbon;

    $statusMap = [
        'active'    => ['label' => 'Active',     'cls' => 'bg-blue-50 text-blue-700 ring-blue-200'],
        'inactive'  => ['label' => 'Inactive',   'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'],
        'graduated' => ['label' => 'Alumni',     'cls' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'suspended' => ['label' => 'Suspended',  'cls' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'dropped'   => ['label' => 'Dropped',    'cls' => 'bg-red-50 text-red-700 ring-red-200'],
    ];
    $st = $statusMap[$student->status] ?? ['label' => ucfirst($student->status), 'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'];

    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$student->id % 6];

    $absentCount = $attendanceTotal - $attendancePresent;
    $attendancePctColor = $attendancePct === null ? 'text-slate-500' : ($attendancePct >= 75 ? 'text-emerald-600' : ($attendancePct >= 50 ? 'text-amber-600' : 'text-red-600'));
@endphp

<div x-data="{ tab: '{{ request('tab', 'overview') }}' }">

{{-- ── HERO HEADER ─────────────────────────────────────────── --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 shadow-lg">
    <div class="absolute inset-0 opacity-5" style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative px-6 py-7">
        <div class="flex flex-wrap items-start gap-5">
            {{-- Avatar --}}
            @if($student->user?->avatar)
                <img src="{{ asset('storage/'.$student->user->avatar) }}" alt=""
                     class="h-20 w-20 flex-shrink-0 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg"/>
            @else
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-3xl font-black text-white shadow-lg ring-4 ring-white/10">
                    {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
                </div>
            @endif

            {{-- Info --}}
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-black text-white leading-tight">{{ $student->user?->name }}</h1>
                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $st['cls'] }} ring-1">{{ $st['label'] }}</span>
                </div>
                <p class="mt-1 font-mono text-sm text-slate-400">{{ $student->student_no }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $student->department?->name ?? '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        {{ $student->program?->name ?? '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500/30 px-3 py-1.5 text-xs font-bold text-violet-200">
                        Semester {{ $student->current_semester }}
                    </span>
                    @if($student->academicSession)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-300">
                        {{ $student->academicSession->name }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-shrink-0 flex-wrap gap-2">
                <a href="{{ route('admin.students.edit', $student) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <a href="{{ route('admin.students.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back
                </a>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
                $pctColor2 = $attendancePct === null ? 'text-slate-300' : ($attendancePct >= 75 ? 'text-emerald-400' : ($attendancePct >= 50 ? 'text-amber-400' : 'text-red-400'));
            @endphp
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black {{ $pctColor2 }}">{{ $attendancePct !== null ? $attendancePct.'%' : '—' }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Attendance</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $marksTotal }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Exam records</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $submissions->count() }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Submissions</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $student->parents->count() }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Parent accounts</p>
            </div>
        </div>
    </div>
</div>

{{-- ── TAB BAR ─────────────────────────────────────────────── --}}
<div class="sticky top-0 z-20 -mx-1 mb-5 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <nav class="flex min-w-max px-2 gap-0">
        @foreach([
            ['id'=>'overview',     'label'=>'Overview'],
            ['id'=>'attendance',   'label'=>'Attendance'],
            ['id'=>'marks',        'label'=>'Marks & Exams'],
            ['id'=>'assignments',  'label'=>'Assignments'],
            ['id'=>'parent',       'label'=>'Parent Info'],
            ['id'=>'timeline',     'label'=>'Timeline'],
        ] as $t)
        <button type="button" @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'border-b-2 border-[#8B0000] text-[#8B0000] font-bold' : 'border-b-2 border-transparent text-slate-500 hover:text-slate-800'"
                class="whitespace-nowrap px-5 py-3.5 text-sm transition">
            {{ $t['label'] }}
        </button>
        @endforeach
    </nav>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- LEFT COLUMN --}}
    <div class="space-y-5">
        {{-- Personal Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Personal</h3>
            <dl class="divide-y divide-slate-100 text-sm">
                @foreach([
                    ['Email',        $student->user?->email],
                    ['Phone',        $student->user?->phone],
                    ['Gender',       ucfirst($student->user?->gender ?? '—')],
                    ['Date of Birth',$student->user?->dob ? bsDate($student->user->dob, 'Y, F d') : '—'],
                    ['Blood Group',  $student->blood_group ?? '—'],
                    ['Address',      $student->user?->address],
                ] as [$label, $value])
                <div class="flex gap-3 py-2.5">
                    <dt class="w-28 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 break-words">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Emergency Contact --}}
        @if($student->guardian_name || $student->guardian_phone)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-amber-700">Emergency Contact</h3>
            <p class="font-semibold text-slate-800">{{ $student->guardian_name ?? '—' }}</p>
            <p class="mt-0.5 text-sm text-slate-600">{{ $student->guardian_phone ?? '—' }}</p>
        </div>
        @endif
    </div>

    {{-- RIGHT COLUMN (spans 2) --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Enrollment --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Enrollment</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 divide-y divide-slate-100 sm:divide-y-0 text-sm">
                @foreach([
                    ['Student ID',      $student->student_no],
                    ['Reg. Number',     $student->registration_number],
                    ['Department',      $student->department?->name],
                    ['Program',         $student->program?->name],
                    ['Academic Session',$student->academicSession?->name],
                    ['Semester',        'Semester '.$student->current_semester],
                    ['Section',         $student->section],
                    ['Batch / Year',    $student->batch],
                    ['Roll Number',     $student->roll_number ?? 'Set by HOD'],
                    ['Admitted',        $student->admission_date ? bsDate($student->admission_date, 'Y, F d') : '—'],
                    ['Enrolled on',     bsDate($student->created_at, 'Y, F d')],
                    ['Last Updated',    bsDate($student->updated_at, 'Y, F d')],
                ] as [$label, $value])
                <div class="flex gap-3 py-2.5 border-b border-slate-100 sm:odd:pr-4 sm:even:pl-4 sm:even:border-l">
                    <dt class="w-32 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 truncate">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </div>

        {{-- Alumni record (if any) --}}
        @if($student->alumnus)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5">
            <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-emerald-700">Alumni Record</h3>
            <dl class="divide-y divide-emerald-100 text-sm">
                @foreach([
                    ['Graduation Year', $student->alumnus->graduation_year],
                    ['Current Job',     $student->alumnus->current_job],
                    ['Company',         $student->alumnus->company_name],
                    ['Achievements',    $student->alumnus->achievements],
                    ['Verified',        $student->alumnus->is_verified ? 'Yes' : 'Pending'],
                    ['Featured',        $student->alumnus->is_featured ? 'Yes' : 'No'],
                ] as [$label, $value])
                <div class="flex gap-3 py-2.5">
                    <dt class="w-32 flex-shrink-0 text-xs text-emerald-700/70 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 break-words">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
        @else
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 text-sm text-slate-500">
            <span class="font-semibold text-slate-600">No alumni record.</span>
            The student will be promoted automatically when the active session ends at the final semester.
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: ATTENDANCE
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'attendance'" class="space-y-5">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md text-center"
             style="background: linear-gradient(135deg,#2563EB,#3B82F6);">
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
            <p class="relative text-2xl font-black text-white">{{ $attendanceTotal }}</p>
            <p class="relative mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Total Classes</p>
        </div>
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md text-center"
             style="background: linear-gradient(135deg,#10B981,#22C55E);">
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
            <p class="relative text-2xl font-black text-white">{{ $attendancePresent }}</p>
            <p class="relative mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Present</p>
        </div>
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md text-center"
             style="background: linear-gradient(135deg,#DC2626,#EF4444);">
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
            <p class="relative text-2xl font-black text-white">{{ $absentCount }}</p>
            <p class="relative mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Absent</p>
        </div>
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md text-center"
             @php $attPct = $attendancePct; @endphp
             style="background: linear-gradient(135deg,{{ $attPct === null ? '#475569,#64748B' : ($attPct >= 75 ? '#10B981,#22C55E' : ($attPct >= 50 ? '#F59E0B,#FBBF24' : '#DC2626,#EF4444')) }});">
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
            <p class="relative text-2xl font-black text-white">{{ $attPct !== null ? $attPct.'%' : '—' }}</p>
            <p class="relative mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Attendance Rate</p>
        </div>
    </div>

    {{-- Progress bar --}}
    @if($attendancePct !== null)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-700">Overall Attendance Rate</span>
            <span class="text-sm font-black {{ $attClr }}">{{ $attendancePct }}%</span>
        </div>
        <div class="h-4 w-full rounded-full bg-slate-100 overflow-hidden">
            <div class="h-4 rounded-full transition-all duration-700 {{ $attendancePct >= 75 ? 'bg-emerald-500' : ($attendancePct >= 50 ? 'bg-amber-400' : 'bg-red-500') }}"
                 style="width: {{ $attendancePct }}%"></div>
        </div>
        <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
            <span>0%</span>
            <span class="{{ $attendancePct < 75 ? 'text-amber-600 font-semibold' : '' }}">
                {{ $attendancePct < 75 ? '⚠ Below 75% minimum required' : '✓ Above minimum threshold' }}
            </span>
            <span>100%</span>
        </div>
    </div>
    @endif

    {{-- Chart + table --}}
    @if($monthlyAttendance->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-bold text-slate-700">Monthly Chart</h3>
            <canvas id="attendanceChart" height="200"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Month</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Total</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Present</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Absent</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($monthlyAttendance as $row)
                    @php $mp = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row['label'] }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-600">{{ $row['total'] }}</td>
                        <td class="px-4 py-2.5 text-center font-semibold text-emerald-600">{{ $row['present'] }}</td>
                        <td class="px-4 py-2.5 text-center font-semibold text-red-500">{{ $row['absent'] }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <div class="h-1.5 w-16 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $mp >= 75 ? 'bg-emerald-500' : ($mp >= 50 ? 'bg-amber-400' : 'bg-red-500') }}" style="width:{{ $mp }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $mp >= 75 ? 'text-emerald-600' : ($mp >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $mp }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-sm font-medium">No attendance records yet</p>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: MARKS / EXAMS
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'marks'" class="space-y-5">
    {{-- Semester navigation --}}
    @if($allSemesters->count() > 1)
    <div class="flex flex-wrap gap-2">
        @foreach($allSemesters as $sem)
        <a href="{{ request()->fullUrlWithQuery(['mark_sem' => $sem, 'tab' => 'marks']) }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $sem == $activeSem ? 'bg-violet-600 text-white shadow' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Semester {{ $sem }}
        </a>
        @endforeach
    </div>
    @endif

    @if($marksBySemester->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm font-medium">No published exam results yet</p>
    </div>
    @else
    @foreach($marksBySemester as $semester => $subjectGroups)
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-5 py-3.5">
            <span class="rounded-lg bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">Semester {{ $semester }}</span>
            <span class="text-xs text-slate-400">{{ $subjectGroups->flatten()->count() }} record(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Exam</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Int. Theory</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Ext. Theory</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Int. Practical</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Ext. Practical</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Total</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($subjectGroups as $subjectName => $marks)
                    @foreach($marks as $mark)
                    @php
                        $total = ($mark->internal_theory_marks ?? 0)
                               + ($mark->external_theory_marks ?? 0)
                               + ($mark->internal_practical_marks ?? 0)
                               + ($mark->external_practical_marks ?? 0);
                    @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $subjectName }}</td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $mark->exam?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center text-slate-700">{{ $mark->internal_theory_marks !== null ? number_format($mark->internal_theory_marks, 1) : '—' }}</td>
                        <td class="px-5 py-3 text-center text-slate-700">{{ $mark->external_theory_marks !== null ? number_format($mark->external_theory_marks, 1) : '—' }}</td>
                        <td class="px-5 py-3 text-center text-slate-700">{{ $mark->internal_practical_marks !== null ? number_format($mark->internal_practical_marks, 1) : '—' }}</td>
                        <td class="px-5 py-3 text-center text-slate-700">{{ $mark->external_practical_marks !== null ? number_format($mark->external_practical_marks, 1) : '—' }}</td>
                        <td class="px-5 py-3 text-center font-bold text-slate-900">{{ number_format($total, 1) }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($mark->is_absent)
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500">Absent</span>
                            @elseif($mark->is_withheld)
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Withheld</span>
                            @else
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $total >= 40 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                    {{ $total >= 40 ? 'Pass' : 'Fail' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
                <tfoot class="border-t border-slate-200 bg-slate-50/50">
                    @php
                        $semTotal = $subjectGroups->flatten()->sum(fn($m) =>
                            ($m->internal_theory_marks ?? 0) + ($m->external_theory_marks ?? 0) +
                            ($m->internal_practical_marks ?? 0) + ($m->external_practical_marks ?? 0));
                        $semCount = $subjectGroups->flatten()->count();
                    @endphp
                    <tr>
                        <td colspan="6" class="px-5 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Semester Total</td>
                        <td class="px-5 py-2.5 text-center font-black text-slate-900">{{ number_format($semTotal, 1) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: ASSIGNMENTS
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'assignments'" class="space-y-4">
    @if($submissions->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm font-medium">No assignment submissions yet</p>
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Assignment</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden sm:table-cell">Due Date</th>
                    <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Marks</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Feedback</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($submissions as $sub)
                @php
                    $subStatusMap = [
                        'submitted' => 'bg-blue-50 text-blue-700',
                        'graded'    => 'bg-emerald-50 text-emerald-700',
                        'late'      => 'bg-amber-50 text-amber-700',
                        'missing'   => 'bg-red-50 text-red-600',
                    ];
                    $subCls = $subStatusMap[$sub->status] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-3 font-medium text-slate-800 max-w-[200px] truncate">{{ $sub->assignment?->title ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $sub->assignment?->subject?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-slate-500 hidden sm:table-cell">
                        {{ $sub->assignment?->due_date ? bsDate($sub->assignment->due_date, 'Y, F d') : '—' }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="rounded-lg px-2.5 py-1 text-[11px] font-bold {{ $subCls }}">{{ ucfirst($sub->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-center font-bold text-slate-700">
                        {{ $sub->marks_obtained !== null ? $sub->marks_obtained : '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-500 italic hidden lg:table-cell max-w-[240px] truncate">
                        {{ $sub->teacher_feedback ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: PARENT INFO
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'parent'" class="space-y-4">
    @if($student->parents->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p class="text-sm font-medium">No parent accounts linked</p>
        <a href="{{ route('admin.students.edit', $student) }}"
           class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-[#8B0000] px-4 py-2 text-xs font-bold text-white hover:bg-[#7a0000] transition">
            Edit student to link a parent
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($student->parents as $parent)
        @php
            $parentGradients = ['from-emerald-500 to-teal-600','from-blue-500 to-indigo-600','from-violet-500 to-purple-600'];
            $pg = $parentGradients[$loop->index % 3];
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
            <div class="flex items-center gap-4 p-5 bg-slate-50 border-b border-slate-100">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $pg }} text-2xl font-black text-white shadow">
                    {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-slate-800 text-base">{{ $parent->user?->name ?? '—' }}</p>
                    <p class="mt-0.5 text-sm text-slate-500">{{ ucfirst($parent->relation_to_student ?? 'Parent / Guardian') }}</p>
                </div>
            </div>
            <dl class="divide-y divide-slate-100 text-sm p-1">
                @foreach([
                    ['Email',      $parent->user?->email],
                    ['Phone',      $parent->user?->phone],
                    ['Occupation', $parent->occupation],
                    ['Address',    $parent->user?->address],
                ] as [$label, $value])
                @if($value)
                <div class="flex gap-3 px-4 py-2.5">
                    <dt class="w-24 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 break-all">{{ $value }}</dd>
                </div>
                @endif
                @endforeach
            </dl>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: TIMELINE
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'timeline'" class="max-w-3xl">
    @php
        $timelineItems = collect();

        if ($student->admission_date || $student->created_at) {
            $timelineItems->push([
                'date'  => $student->admission_date ?? $student->created_at,
                'icon'  => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                'color' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'title' => 'Student enrolled',
                'sub'   => ($student->program?->name ?? '—').' · Semester '.$student->current_semester,
                'actor' => 'System',
            ]);
        }

        foreach ($timeline as $log) {
            $timelineItems->push([
                'date'  => $log->created_at,
                'icon'  => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                'color' => 'bg-blue-100 text-blue-700 ring-blue-200',
                'title' => ucwords(str_replace(['.', '_', '-'], ' ', $log->action)),
                'sub'   => '',
                'actor' => $log->user?->name ?? 'System',
            ]);
        }

        $timelineItems = $timelineItems->sortByDesc('date');
    @endphp

    @if($timelineItems->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm font-medium">No activity recorded yet</p>
    </div>
    @else
    <ol class="relative space-y-4 border-l-2 border-slate-200 pl-8">
        @foreach($timelineItems as $item)
        @php
            $tDate = $item['date'];
            if (is_string($tDate)) $tDate = \Carbon\Carbon::parse($tDate);
        @endphp
        <li class="relative">
            <div class="absolute -left-11 flex h-6 w-6 items-center justify-center rounded-full ring-4 ring-white {{ $item['color'] }} shadow-sm">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}"/>
                </svg>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-5 py-3.5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-sm font-bold text-slate-800">{{ $item['title'] }}</p>
                    <time class="flex-shrink-0 rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">
                        {{ $tDate ? bsDate($tDate, 'Y, F d h:i A') : $tDate }}
                    </time>
                </div>
                @if($item['sub'])
                <p class="mt-0.5 text-xs text-slate-500">{{ $item['sub'] }}</p>
                @endif
                <p class="mt-1.5 text-[11px] text-slate-400">by {{ $item['actor'] }}</p>
            </div>
        </li>
        @endforeach
    </ol>
    @endif
</div>

</div>{{-- /x-data --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('attendanceChart');
    if (!ctx || !window.Chart) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! $monthlyAttendance->pluck('label')->toJson() !!},
            datasets: [
                {
                    label: 'Present',
                    data: {!! $monthlyAttendance->pluck('present')->toJson() !!},
                    backgroundColor: 'rgba(16,185,129,0.85)',
                    borderRadius: 5,
                },
                {
                    label: 'Absent',
                    data: {!! $monthlyAttendance->pluck('absent')->toJson() !!},
                    backgroundColor: 'rgba(239,68,68,0.75)',
                    borderRadius: 5,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 16 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } }
            }
        }
    });
});
</script>
@endpush
@endsection