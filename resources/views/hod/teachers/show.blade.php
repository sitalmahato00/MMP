@extends('layouts.app')
@section('title', $teacher->user->name)

@section('content')
@php
    $isActive = $teacher->is_active;
    $statusText = $isActive ? 'Active' : 'Inactive';
    $statusClass = $isActive ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200';
    $st = ['label' => $statusText, 'cls' => $statusClass];

    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$teacher->id % 6];
@endphp

<div x-data="{ tab: '{{ request('tab', 'overview') }}' }">

{{-- ── HERO HEADER ─────────────────────────────────────────── --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-[#1d4ed8] to-[#1e40af] shadow-lg">
    <div class="absolute inset-0 opacity-5" style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative px-6 py-7">
        <div class="flex flex-wrap items-start gap-5">
            {{-- Avatar --}}
            @if($teacher->user?->avatar)
                <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt=""
                     class="h-20 w-20 flex-shrink-0 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg"/>
            @else
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-3xl font-black text-white shadow-lg ring-4 ring-white/10">
                    {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                </div>
            @endif

            {{-- Info --}}
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-black text-white leading-tight">{{ $teacher->user?->name }}</h1>
                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $st['cls'] }} ring-1">{{ $st['label'] }}</span>
                </div>
                <p class="mt-1 font-mono text-sm text-blue-100">{{ $teacher->employee_id }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-blue-50">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $teacher->department?->name ?? '—' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500/30 px-3 py-1.5 text-xs font-bold text-violet-100">
                        {{ $teacher->designation }}
                    </span>
                    @if($teacher->specialization)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-blue-100">
                        {{ $teacher->specialization }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-shrink-0 flex-wrap gap-2">
                <a href="{{ route('hod.teachers.edit', $teacher) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <a href="{{ route('hod.teachers.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back
                </a>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $subjectsCount }}</p>
                <p class="mt-0.5 text-[11px] text-blue-200">Subjects</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $attendanceSessionsCount }}</p>
                <p class="mt-0.5 text-[11px] text-blue-200">Sessions</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $assignmentsCount }}</p>
                <p class="mt-0.5 text-[11px] text-blue-200">Assignments</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $teacher->employment_type ? ucfirst($teacher->employment_type) : 'N/A' }}</p>
                <p class="mt-0.5 text-[11px] text-blue-200">Employment</p>
            </div>
        </div>
    </div>
</div>

{{-- ── TAB BAR ─────────────────────────────────────────────── --}}
<div class="sticky top-0 z-20 -mx-1 mb-5 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <nav class="flex min-w-max px-2 gap-0">
        @foreach([
            ['id'=>'overview',     'label'=>'Overview'],
            ['id'=>'teaching',     'label'=>'Teaching'],
            ['id'=>'assignments',  'label'=>'Assignments'],
            ['id'=>'attendance',   'label'=>'Attendance'],
            ['id'=>'professional', 'label'=>'Professional'],
            ['id'=>'timeline',     'label'=>'Timeline'],
        ] as $t)
        <button type="button" @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'border-b-2 border-[#1d4ed8] text-[#1d4ed8] font-bold' : 'border-b-2 border-transparent text-slate-500 hover:text-slate-800'"
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
                    ['Email',        $teacher->user?->email],
                    ['Phone',        $teacher->user?->phone],
                    ['Gender',       ucfirst($teacher->user?->gender ?? '—')],
                    ['Date of Birth',$teacher->user?->dob ? bsDate($teacher->user->dob, 'Y, F d') : '—'],
                    ['Address',      $teacher->user?->address],
                ] as [$label, $value])
                <div class="flex gap-3 py-2.5">
                    <dt class="w-28 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 break-words">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- RIGHT COLUMN (spans 2) --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Employment --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Employment</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 divide-y divide-slate-100 sm:divide-y-0 text-sm">
                @foreach([
                    ['Employee ID',     $teacher->employee_id],
                    ['Department',      $teacher->department?->name],
                    ['Designation',     $teacher->designation],
                    ['Status',          $statusText],
                    ['Join Date',       $teacher->join_date ? bsDate($teacher->join_date, 'Y, F d') : '—'],
                    ['Employment Type', $teacher->employment_type ? ucfirst($teacher->employment_type) : '—'],
                    ['Qualification',   $teacher->qualification ?? '—'],
                    ['Specialization',  $teacher->specialization ?? '—'],
                    ['Created',         bsDate($teacher->created_at, 'Y, F d')],
                    ['Last Updated',    bsDate($teacher->updated_at, 'Y, F d')],
                ] as [$label, $value])
                <div class="flex gap-3 py-2.5 border-b border-slate-100 sm:odd:pr-4 sm:even:pl-4 sm:even:border-l">
                    <dt class="w-32 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                    <dd class="font-medium text-slate-800 min-w-0 truncate">{{ $value ?: '—' }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: TEACHING
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'teaching'" class="space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-bold text-slate-700">Teaching Overview</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl bg-blue-50 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $subjectsCount }}</p>
                <p class="text-xs text-slate-500">Subjects Teaching</p>
            </div>
            <div class="rounded-xl bg-emerald-50 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ $attendanceSessionsCount }}</p>
                <p class="text-xs text-slate-500">Classes Conducted</p>
            </div>
            <div class="rounded-xl bg-violet-50 p-4 text-center">
                <p class="text-2xl font-bold text-violet-600">{{ $assignmentsCount }}</p>
                <p class="text-xs text-slate-500">Assignments Given</p>
            </div>
        </div>
        
        <div class="text-center text-slate-400 py-8">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-sm font-medium">Detailed teaching analytics coming soon</p>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: ASSIGNMENTS
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'assignments'" class="space-y-4">
    @if($recentAssignments->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm font-medium">No assignments created yet</p>
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold text-slate-700">Recent Assignments</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Assignment</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Due Date</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($recentAssignments as $assignment)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $assignment->title }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $assignment->subject_name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ bsDate($assignment->due_date, 'Y, F d') }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ bsDate($assignment->created_at, 'Y, F d') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: ATTENDANCE
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'attendance'" class="space-y-4">
    @if($recentAttendance->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-16 text-slate-400 shadow-sm">
        <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-sm font-medium">No attendance sessions recorded</p>
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <div class="bg-slate-50 border-b border-slate-200 px-5 py-3">
            <h3 class="text-sm font-bold text-slate-700">Recent Attendance Sessions</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Semester</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Section</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($recentAttendance as $session)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $session->subject_name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ bsDate($session->date, 'Y, F d') }}</td>
                    <td class="px-5 py-3 text-slate-600">Semester {{ $session->semester }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $session->section ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: PROFESSIONAL
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'professional'" class="space-y-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Professional Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 divide-y divide-slate-100 sm:divide-y-0 text-sm">
            @foreach([
                ['Qualification',   $teacher->qualification ?? '—'],
                ['Specialization',  $teacher->specialization ?? '—'],
                ['Employment Type', $teacher->employment_type ? ucfirst($teacher->employment_type) : '—'],
            ] as [$label, $value])
            <div class="flex gap-3 py-2.5 border-b border-slate-100 sm:odd:pr-4 sm:even:pl-4 sm:even:border-l">
                <dt class="w-32 flex-shrink-0 text-xs text-slate-500 pt-0.5">{{ $label }}</dt>
                <dd class="font-medium text-slate-800 min-w-0 break-words">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     TAB: TIMELINE
══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'timeline'" class="max-w-3xl">
    @php
        $timelineItems = collect();

        if ($teacher->join_date || $teacher->created_at) {
            $timelineItems->push([
                'date'  => $teacher->join_date ?? $teacher->created_at,
                'icon'  => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                'color' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'title' => 'Teacher joined',
                'sub'   => $teacher->designation . ' • ' . ($teacher->department?->name ?? '—'),
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
                'actor' => 'HOD',
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
@endsection