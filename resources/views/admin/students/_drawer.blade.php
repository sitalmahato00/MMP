@php
    use Carbon\Carbon;

    $statusMap = [
        'active'    => ['label' => 'Active',     'cls' => 'bg-blue-50 text-blue-700'],
        'inactive'  => ['label' => 'Inactive',   'cls' => 'bg-slate-100 text-slate-600'],
        'graduated' => ['label' => 'Alumni',     'cls' => 'bg-emerald-50 text-emerald-700'],
        'suspended' => ['label' => 'Suspended',  'cls' => 'bg-amber-50 text-amber-700'],
        'dropped'   => ['label' => 'Dropped',    'cls' => 'bg-red-50 text-red-700'],
    ];
    $st = $statusMap[$student->status] ?? ['label' => ucfirst($student->status), 'cls' => 'bg-slate-100 text-slate-600'];

    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$student->id % 6];

    $tabs = ['Overview', 'Attendance', 'Marks', 'Assignments', 'Parent', 'Timeline'];

    // Marks: collect all subjects across semesters for a flat summary
    $allMarks = $student->marks->where('status', 'published');
@endphp

<div x-data="{ tab: 'Overview' }">

{{-- ── PROFILE HEADER ──────────────────────────────────────── --}}
<div class="bg-gradient-to-br from-slate-900 to-slate-800 px-6 py-5">
    <div class="flex items-start gap-4">
        @if($student->user?->avatar)
            <img src="{{ asset('storage/'.$student->user->avatar) }}" alt=""
                 class="h-16 w-16 flex-shrink-0 rounded-2xl object-cover ring-2 ring-white/20 shadow-lg"/>
        @else
            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl font-black text-white shadow-lg">
                {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <h3 class="text-lg font-black text-white leading-tight">{{ $student->user?->name }}</h3>
            <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $student->student_no }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $st['cls'] }}">{{ $st['label'] }}</span>
                <span class="rounded-lg bg-violet-500/20 px-2.5 py-1 text-xs font-bold text-violet-200">Sem {{ $student->current_semester }}</span>
                @if($student->program)
                    <span class="rounded-lg bg-white/10 px-2.5 py-1 text-xs text-slate-300">{{ $student->program->name }}</span>
                @endif
            </div>
        </div>
        <div class="flex flex-col gap-1.5 flex-shrink-0">
            <a href="{{ route('admin.students.edit', $student) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('admin.students.show', $student) }}"
               class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Full page
            </a>
        </div>
    </div>
</div>

{{-- ── TAB BAR ─────────────────────────────────────────────── --}}
<div class="border-b border-slate-200 bg-white sticky top-0 z-10">
    <nav class="flex overflow-x-auto px-4 gap-0 scrollbar-none">
        @foreach($tabs as $t)
        <button type="button" @click="tab = '{{ $t }}'"
                :class="tab === '{{ $t }}' ? 'border-b-2 border-[#8B0000] text-[#8B0000] font-bold' : 'text-slate-500 hover:text-slate-800 border-b-2 border-transparent'"
                class="whitespace-nowrap px-4 py-3.5 text-sm transition flex-shrink-0">
            {{ $t }}
        </button>
        @endforeach
    </nav>
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: OVERVIEW
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Overview'" class="p-6 space-y-6">

    {{-- Quick stats --}}
    <div class="grid grid-cols-3 gap-3">
        @php
            $pct = $attendancePct;
            $pctColor = $pct === null ? 'slate' : ($pct >= 75 ? 'emerald' : ($pct >= 50 ? 'amber' : 'red'));
        @endphp
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
            <p class="text-xl font-black {{ $pctColor === 'emerald' ? 'text-emerald-600' : ($pctColor === 'amber' ? 'text-amber-600' : ($pctColor === 'red' ? 'text-red-600' : 'text-slate-500')) }}">
                {{ $pct !== null ? $pct.'%' : '—' }}
            </p>
            <p class="mt-0.5 text-[11px] text-slate-500">Attendance</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
            <p class="text-xl font-black text-slate-800">{{ $allMarks->count() }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Exam records</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
            <p class="text-xl font-black text-slate-800">{{ $submissions->count() }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Assignments</p>
        </div>
    </div>

    {{-- Personal info --}}
    <div>
        <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Personal</h4>
        <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden text-sm">
            @foreach([
                ['Email',        $student->user?->email],
                ['Phone',        $student->user?->phone],
                ['Gender',       ucfirst($student->user?->gender ?? '—')],
                ['Date of Birth', $student->user?->dob ? bsDate($student->user->dob, 'd F Y') : '—'],
                ['Address',      $student->user?->address],
                ['Blood Group',  $student->blood_group],
            ] as [$label, $value])
            <div class="flex gap-4 px-4 py-2.5">
                <dt class="w-32 flex-shrink-0 text-xs text-slate-500">{{ $label }}</dt>
                <dd class="font-medium text-slate-800 min-w-0 truncate">{{ $value ?: '—' }}</dd>
            </div>
            @endforeach
        </dl>
    </div>

    {{-- Academic info --}}
    <div>
        <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Academic</h4>
        <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden text-sm">
            @foreach([
                ['Student ID',      $student->student_no],
                ['Registration No', $student->registration_number],
                ['Department',      $student->department?->name],
                ['Program',         $student->program?->name],
                ['Session',         $student->academicSession?->name],
                ['Semester',        'Semester '.$student->current_semester],
                ['Section',         $student->section],
                ['Batch',           $student->batch],
                ['Admitted',        $student->admission_date ? bsDate($student->admission_date, 'd F Y') : '—'],
            ] as [$label, $value])
            <div class="flex gap-4 px-4 py-2.5">
                <dt class="w-32 flex-shrink-0 text-xs text-slate-500">{{ $label }}</dt>
                <dd class="font-medium text-slate-800 min-w-0 truncate">{{ $value ?: '—' }}</dd>
            </div>
            @endforeach
        </dl>
    </div>

    {{-- Emergency contact --}}
    @if($student->guardian_name || $student->guardian_phone)
    <div>
        <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Emergency Contact</h4>
        <div class="rounded-xl border border-slate-200 bg-amber-50/30 px-4 py-3 text-sm">
            <p class="font-semibold text-slate-800">{{ $student->guardian_name ?? '—' }}</p>
            <p class="text-slate-500 text-xs mt-0.5">{{ $student->guardian_phone ?? '—' }}</p>
        </div>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: ATTENDANCE
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Attendance'" class="p-6 space-y-5">
    {{-- Summary KPIs --}}
    <div class="grid grid-cols-3 gap-3">
        @php
            $absentCount = $attendanceTotal - $attendancePresent;
            $pctColor = $attendancePct === null ? 'text-slate-500' : ($attendancePct >= 75 ? 'text-emerald-600' : ($attendancePct >= 50 ? 'text-amber-600' : 'text-red-600'));
        @endphp
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
            <p class="text-2xl font-black text-slate-800">{{ $attendanceTotal }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Total Classes</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-emerald-50 p-3 text-center">
            <p class="text-2xl font-black text-emerald-600">{{ $attendancePresent }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Present</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-red-50 p-3 text-center">
            <p class="text-2xl font-black text-red-500">{{ $absentCount }}</p>
            <p class="mt-0.5 text-[11px] text-slate-500">Absent</p>
        </div>
    </div>

    {{-- Attendance % bar --}}
    @if($attendancePct !== null)
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-slate-700">Overall Attendance</span>
            <span class="text-sm font-black {{ $pctColor }}">{{ $attendancePct }}%</span>
        </div>
        <div class="h-3 w-full rounded-full bg-slate-100 overflow-hidden">
            <div class="h-3 rounded-full transition-all duration-500 {{ $attendancePct >= 75 ? 'bg-emerald-500' : ($attendancePct >= 50 ? 'bg-amber-400' : 'bg-red-500') }}"
                 style="width: {{ $attendancePct }}%"></div>
        </div>
        @if($attendancePct < 75)
        <p class="mt-2 text-xs text-amber-600 font-medium">⚠ Below 75% minimum required attendance</p>
        @endif
    </div>
    @endif

    {{-- Monthly chart --}}
    @if($monthlyAttendance->isNotEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <h4 class="mb-3 text-sm font-bold text-slate-700">Monthly Breakdown</h4>
        <canvas id="drawerAttendanceChart" height="140"></canvas>
        <script>
        window.initDrawerCharts = function() {
            var ctx = document.getElementById('drawerAttendanceChart');
            if (!ctx || !window.Chart) return;
            if (ctx._chartInstance) ctx._chartInstance.destroy();
            ctx._chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! $monthlyAttendance->pluck('label')->toJson() !!},
                    datasets: [
                        {
                            label: 'Present',
                            data: {!! $monthlyAttendance->pluck('present')->toJson() !!},
                            backgroundColor: 'rgba(16,185,129,0.8)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Absent',
                            data: {!! $monthlyAttendance->pluck('absent')->toJson() !!},
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                    scales: {
                        x: { stacked: false, grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } }
                    }
                }
            });
        };
        </script>
    </div>

    {{-- Monthly table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Month</th>
                    <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Present</th>
                    <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Absent</th>
                    <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($monthlyAttendance as $row)
                @php $mp = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100) : 0; @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row['label'] }}</td>
                    <td class="px-4 py-2.5 text-center text-emerald-600 font-semibold">{{ $row['present'] }}</td>
                    <td class="px-4 py-2.5 text-center text-red-500 font-semibold">{{ $row['absent'] }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="font-bold {{ $mp >= 75 ? 'text-emerald-600' : ($mp >= 50 ? 'text-amber-600' : 'text-red-500') }}">{{ $mp }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-sm font-medium">No attendance records yet</p>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: MARKS / EXAMS
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Marks'" class="p-6 space-y-5">
    @if($marksBySemester->isEmpty())
    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm font-medium">No published exam results yet</p>
    </div>
    @else
    @foreach($marksBySemester as $semester => $subjectGroups)
    <div>
        <div class="mb-3 flex items-center gap-2">
            <span class="rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">Semester {{ $semester }}</span>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Exam</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Theory</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Practical</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Total</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Result</th>
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
                        $theory = ($mark->internal_theory_marks ?? 0) + ($mark->external_theory_marks ?? 0);
                        $practical = ($mark->internal_practical_marks ?? 0) + ($mark->external_practical_marks ?? 0);
                    @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $subjectName }}</td>
                        <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $mark->exam?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-700">{{ $theory > 0 ? number_format($theory, 1) : '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-700">{{ $practical > 0 ? number_format($practical, 1) : '—' }}</td>
                        <td class="px-4 py-2.5 text-center font-bold text-slate-800">{{ number_format($total, 1) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($mark->is_absent)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">Absent</span>
                            @elseif($mark->is_withheld)
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Withheld</span>
                            @else
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $total >= 40 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                    {{ $total >= 40 ? 'Pass' : 'Fail' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: ASSIGNMENTS
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Assignments'" class="p-6 space-y-4">
    @if($submissions->isEmpty())
    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm font-medium">No assignment submissions yet</p>
    </div>
    @else
    @foreach($submissions as $sub)
    @php
        $subStatusMap = [
            'submitted'  => 'bg-blue-50 text-blue-700',
            'graded'     => 'bg-emerald-50 text-emerald-700',
            'late'       => 'bg-amber-50 text-amber-700',
            'missing'    => 'bg-red-50 text-red-600',
        ];
        $subCls = $subStatusMap[$sub->status] ?? 'bg-slate-100 text-slate-600';
    @endphp
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-semibold text-slate-800 text-sm leading-tight">
                    {{ $sub->assignment?->title ?? 'Assignment' }}
                </p>
                <p class="mt-0.5 text-xs text-slate-500">
                    {{ $sub->assignment?->subject?->name ?? '—' }}
                    @if($sub->assignment?->due_date)
                        · Due {{ bsDate($sub->assignment->due_date, 'd M Y') }}
                    @endif
                </p>
            </div>
            <div class="flex flex-shrink-0 flex-col items-end gap-1">
                <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold {{ $subCls }}">{{ ucfirst($sub->status) }}</span>
                @if($sub->marks_obtained !== null)
                    <span class="text-xs font-bold text-slate-700">{{ $sub->marks_obtained }} pts</span>
                @endif
            </div>
        </div>
        @if($sub->teacher_feedback)
        <p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 italic">
            "{{ $sub->teacher_feedback }}"
        </p>
        @endif
    </div>
    @endforeach
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: PARENT INFO
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Parent'" class="p-6 space-y-4">
    @if($student->parents->isEmpty())
    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p class="text-sm font-medium">No parent accounts linked</p>
        <p class="mt-1 text-xs">You can link a parent account from the <a href="{{ route('admin.students.edit', $student) }}" class="text-[#8B0000] font-semibold hover:underline">edit page</a>.</p>
    </div>
    @else
    @foreach($student->parents as $parent)
    @php
        $parentGradients = ['from-emerald-500 to-teal-600','from-blue-500 to-indigo-600','from-violet-500 to-purple-600'];
        $pg = $parentGradients[$loop->index % 3];
    @endphp
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="flex items-center gap-4 p-4 bg-slate-50/60 border-b border-slate-100">
            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $pg }} text-xl font-black text-white shadow">
                {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-slate-800">{{ $parent->user?->name ?? '—' }}</p>
                <p class="text-xs text-slate-500">{{ ucfirst($parent->relation_to_student ?? 'Parent / Guardian') }}</p>
            </div>
        </div>
        <dl class="divide-y divide-slate-100 text-sm">
            @foreach([
                ['Email',      $parent->user?->email],
                ['Phone',      $parent->user?->phone],
                ['Occupation', $parent->occupation],
                ['Address',    $parent->user?->address],
            ] as [$label, $value])
            @if($value)
            <div class="flex gap-4 px-4 py-2.5">
                <dt class="w-24 flex-shrink-0 text-xs text-slate-500">{{ $label }}</dt>
                <dd class="font-medium text-slate-800 min-w-0 break-all">{{ $value }}</dd>
            </div>
            @endif
            @endforeach
        </dl>
    </div>
    @endforeach
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════
     TAB: TIMELINE
═══════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'Timeline'" class="p-6">
    @php
        $timelineItems = collect();

        // Admission event
        if ($student->admission_date || $student->created_at) {
            $timelineItems->push([
                'date'    => $student->admission_date ?? $student->created_at,
                'icon'    => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                'color'   => 'bg-emerald-100 text-emerald-700',
                'title'   => 'Student enrolled',
                'sub'     => $student->program?->name.' · Sem '.$student->current_semester,
                'actor'   => 'System',
            ]);
        }

        // Audit log items
        foreach ($timeline as $log) {
            $timelineItems->push([
                'date'    => $log->created_at,
                'icon'    => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                'color'   => 'bg-blue-100 text-blue-700',
                'title'   => ucwords(str_replace(['.', '_', '-'], ' ', $log->action)),
                'sub'     => '',
                'actor'   => $log->user?->name ?? 'System',
            ]);
        }

        $timelineItems = $timelineItems->sortByDesc('date');
    @endphp

    @if($timelineItems->isEmpty())
    <div class="flex flex-col items-center justify-center py-12 text-center text-slate-400">
        <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm font-medium">No activity recorded yet</p>
    </div>
    @else
    <ol class="relative space-y-4 border-l border-slate-200 pl-6">
        @foreach($timelineItems as $item)
        @php
            $date = $item['date'];
            if (is_string($date)) { $date = \Carbon\Carbon::parse($date); }
        @endphp
        <li class="relative">
            <div class="absolute -left-9 flex h-6 w-6 items-center justify-center rounded-full {{ $item['color'] }} shadow-sm ring-2 ring-white">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-800">{{ $item['title'] }}</p>
                    <time class="flex-shrink-0 text-[11px] text-slate-400">
                        {{ bsDate($date, 'd M Y') }}
                    </time>
                </div>
                @if($item['sub'])
                <p class="mt-0.5 text-xs text-slate-500">{{ $item['sub'] }}</p>
                @endif
                <p class="mt-1 text-[11px] text-slate-400">by {{ $item['actor'] }}</p>
            </div>
        </li>
        @endforeach
    </ol>
    @endif
</div>

</div>{{-- /x-data tab wrapper --}}
