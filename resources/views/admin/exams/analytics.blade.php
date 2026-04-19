@extends('layouts.app')
@section('title', 'Exam Analytics')

@section('content')
@php
    $selectedSession = $sessions->firstWhere('id', $filters['sessionId'] ?? null);
    $selectedSessionLabel = $selectedSession?->name_bs ?: $selectedSession?->name ?: 'Current session';
    $selectedDepartment = $departments->firstWhere('id', $filters['departmentId'] ?? null);
    $selectedProgram = $programs->firstWhere('id', $filters['programId'] ?? null);

    $kpiStyles = [
        'blue' => ['card' => 'bg-gradient-to-br from-sky-50 to-white border-sky-100', 'badge' => 'bg-sky-100 text-sky-700', 'spark' => 'text-sky-600'],
        'amber' => ['card' => 'bg-gradient-to-br from-amber-50 to-white border-amber-100', 'badge' => 'bg-amber-100 text-amber-700', 'spark' => 'text-amber-600'],
        'emerald' => ['card' => 'bg-gradient-to-br from-emerald-50 to-white border-emerald-100', 'badge' => 'bg-emerald-100 text-emerald-700', 'spark' => 'text-emerald-600'],
        'violet' => ['card' => 'bg-gradient-to-br from-violet-50 to-white border-violet-100', 'badge' => 'bg-violet-100 text-violet-700', 'spark' => 'text-violet-600'],
    ];

    $filterStatusOptions = [
        '' => 'All statuses',
        'upcoming' => 'Upcoming',
        'ongoing' => 'Ongoing',
        'marks_pending' => 'Marks Pending',
        'verifying' => 'Verifying',
        'published' => 'Published',
    ];
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.07)]">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/60"></div>
        <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-emerald-200/30 blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 h-52 w-52 rounded-full bg-sky-200/30 blur-3xl"></div>

        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-emerald-700">
                        <i class="fas fa-chart-line"></i>
                        Global exam analytics
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Exam performance analytics</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                            Compare departments, spot difficult subjects, and review result trends before you publish the next cycle.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Session: {{ $selectedSessionLabel }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Department: {{ $selectedDepartment?->name ?? 'All departments' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Program: {{ $selectedProgram?->name ?? 'All programs' }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Back to exams
                    </a>
                    <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        Create exam
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.exams.analytics') }}" class="mt-6 rounded-[1.5rem] border border-slate-200 bg-white/90 p-4 shadow-sm backdrop-blur">
                <div class="grid gap-3 xl:grid-cols-7">
                    <div class="xl:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Search</label>
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Exam, subject, teacher, program..."
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session</label>
                        <select name="year" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">Current session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" @selected(($filters['sessionId'] ?? null) === $session->id)>
                                    {{ $session->name_bs ?: $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                        <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(($filters['departmentId'] ?? null) === $department->id)>
                                    {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Program</label>
                        <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(($filters['programId'] ?? null) === $program->id)>
                                    {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Type</label>
                        <select name="type" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All types</option>
                            @foreach($typeOptions as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" @selected(($filters['type'] ?? null) === $typeKey)>{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            @foreach($filterStatusOptions as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" @selected(($filters['status'] ?? '') === $statusKey)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.exams.analytics') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Clear
                    </a>
                    <span class="text-xs font-semibold text-slate-400">Reviewing {{ $kpis[0]['value'] ?? '0' }} department average with current filters.</span>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            @php $tone = $kpiStyles[$kpi['tone']] ?? $kpiStyles['blue']; @endphp
            <article class="rounded-[1.5rem] border {{ $tone['card'] }} p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $kpi['value'] }}</p>
                        <div class="mt-1 flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <span>{{ $kpi['trend'] }}</span>
                            <span class="text-slate-400">{{ $kpi['direction'] }}</span>
                        </div>
                    </div>
                    <div class="rounded-full {{ $tone['badge'] }} px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em]">{{ $kpi['label'] }}</div>
                </div>
                <div class="mt-3 rounded-2xl bg-white/70 p-2">
                    <svg viewBox="0 0 96 28" class="h-8 w-full">
                        <path d="{{ $kpi['sparkline'] ?? 'M 4 24 L 20 24 L 36 24 L 52 24 L 68 24 L 84 24' }}" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="{{ $tone['spark'] }}"></path>
                    </svg>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Department Ranking</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Pass rate by department</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">Horizontal bar chart</span>
            </div>
            <div class="mt-5 h-[320px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                <canvas id="analyticsDepartmentChart"></canvas>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Back Exam Trend</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Partial/back exam movement</h2>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Cycle line</span>
            </div>
            <div class="mt-5 h-[320px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                <canvas id="analyticsBackTrendChart"></canvas>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Grade Spread</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Published marks distribution</h2>
                </div>
                <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">Outcome mix</span>
            </div>
            <div class="mt-5 h-[320px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                <canvas id="analyticsGradeChart"></canvas>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Year Trend</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Aggregate pass rate over time</h2>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Line chart</span>
            </div>
            <div class="mt-5 h-[320px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                <canvas id="analyticsYearChart"></canvas>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Top Programs</p>
                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Highest result averages</h3>
                </div>
                <span class="rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">Program rank</span>
            </div>
            <div class="mt-4 max-h-[480px] overflow-y-auto space-y-3 pr-1">
                @forelse($analytics['topPrograms'] ?? [] as $row)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $row['program'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $row['code'] ?: 'No code' }} · {{ $row['count'] }} results</p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200">{{ number_format($row['pass_rate'], 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="No program data" message="Program rankings will appear once marks are published."/>
                @endforelse
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Hardest Subjects</p>
                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Highest fail rates</h3>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Difficulty</span>
            </div>
            <div class="mt-4 max-h-[480px] overflow-y-auto overflow-x-auto rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div>
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/95 backdrop-blur">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Fails</th>
                                <th class="px-4 py-3 text-left">Average</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($analytics['subjectDifficulty'] ?? [] as $row)
                                <tr>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-950">{{ $row['subject'] }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $row['code'] ?: 'No code' }} · {{ $row['count'] }} marks</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ number_format($row['fail_rate'], 1) }}%</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ number_format($row['average'], 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><x-empty-state title="No subject data" message="Subject difficulty appears after marks are entered."/></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Teacher Contribution</p>
                    <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Average score by teacher</h3>
                </div>
                <span class="rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-[#8B0000]">Faculty</span>
            </div>
            <div class="mt-4 max-h-[480px] overflow-y-auto space-y-3 pr-1">
                @forelse($analytics['teacherContribution'] ?? [] as $row)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            @if($row['avatar'])
                                <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-white">
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-black text-white">{{ strtoupper(substr($row['teacher'], 0, 1)) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-950">{{ $row['teacher'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $row['count'] }} marks evaluated</p>
                            </div>
                            <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-200">{{ number_format($row['score'], 1) }}%</span>
                        </div>
                    </div>
                @empty
                    <x-empty-state title="No teacher data" message="Teacher analytics appear when marks are linked to evaluators."/>
                @endforelse
            </div>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) {
        return;
    }

    const departmentData = @json($analytics['departmentPerformance'] ?? []);
    const backTrendData = @json($analytics['backExamTrend'] ?? []);
    const gradeData = @json($analytics['gradeDistribution'] ?? []);
    const yearData = @json($analytics['yearTrend'] ?? []);

    const departmentCanvas = document.getElementById('analyticsDepartmentChart');
    if (departmentCanvas) {
        new Chart(departmentCanvas, {
            type: 'bar',
            data: {
                labels: departmentData.map(item => item.department ?? item.label ?? 'N/A'),
                datasets: [{
                    label: 'Pass rate',
                    data: departmentData.map(item => item.pass_rate ?? item.score ?? 0),
                    borderRadius: 10,
                    borderSkipped: false,
                    maxBarThickness: 24,
                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, suggestedMax: 100, ticks: { callback: value => value + '%' } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    const backTrendCanvas = document.getElementById('analyticsBackTrendChart');
    if (backTrendCanvas) {
        const ctx = backTrendCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(245, 158, 11, 0.22)');
        gradient.addColorStop(1, 'rgba(245, 158, 11, 0.03)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: backTrendData.map(item => item.label ?? 'N/A'),
                datasets: [{
                    label: 'Pass rate',
                    data: backTrendData.map(item => item.pass_rate ?? 0),
                    borderColor: '#b45309',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#b45309',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    tension: 0.38,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, suggestedMax: 100, ticks: { callback: value => value + '%' } },
                },
            },
        });
    }

    const gradeCanvas = document.getElementById('analyticsGradeChart');
    if (gradeCanvas) {
        new Chart(gradeCanvas, {
            type: 'doughnut',
            data: {
                labels: gradeData.map(item => item.label ?? 'N/A'),
                datasets: [{
                    data: gradeData.map(item => item.count ?? 0),
                    backgroundColor: ['rgba(16, 185, 129, 0.85)', 'rgba(59, 130, 246, 0.85)', 'rgba(245, 158, 11, 0.85)', 'rgba(139, 0, 0, 0.85)'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                },
            },
        });
    }

    const yearCanvas = document.getElementById('analyticsYearChart');
    if (yearCanvas) {
        const ctx = yearCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.03)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearData.map(item => item.label ?? 'N/A'),
                datasets: [{
                    label: 'Pass rate',
                    data: yearData.map(item => item.pass_rate ?? 0),
                    borderColor: '#047857',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#047857',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    tension: 0.38,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, suggestedMax: 100, ticks: { callback: value => value + '%' } },
                },
            },
        });
    }
});
</script>
@endpush