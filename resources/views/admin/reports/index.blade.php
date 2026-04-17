@extends('layouts.app')

@section('title', 'Reports')

@section('content')
@php
    $exportQuery = array_filter([
        'report_type' => $selectedType,
        'academic_session_id' => $filters['academic_session_id'] ?? null,
        'department_id' => $filters['department_id'] ?? null,
        'program_id' => $filters['program_id'] ?? null,
        'date_from' => $filters['date_from_bs'] ?? null,
        'date_to' => $filters['date_to_bs'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-6">
    <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <h1 class="text-3xl font-black tracking-tight text-slate-950">Reports</h1>
        <p class="mt-2 text-sm text-slate-600">Generate and download academic and administrative reports</p>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="mt-6 space-y-5">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Report Type</p>
                <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($reportTypes as $key => $label)
                        @php
                            $active = $selectedType === $key;
                        @endphp
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border px-4 py-3 text-sm font-semibold transition {{ $active ? 'border-red-300 bg-red-50 text-[#8B0000]' : 'border-slate-200 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50/30' }}">
                            <input type="radio" name="report_type" value="{{ $key }}" class="h-4 w-4 border-slate-300 text-[#8B0000] focus:ring-red-200" @checked($active)>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Academic Session</label>
                    <select name="academic_session_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="">All sessions</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" @selected(($filters['academic_session_id'] ?? null) === $session->id)>
                                {{ $session->name }}@if($session->name_bs) / {{ $session->name_bs }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                    <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="">All departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) === $department->id)>
                                {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Program</label>
                    <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="">All programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) === $program->id)>
                                {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Date From (BS)</label>
                        <x-bs-date-picker name="date_from" :value="$filters['date_from_bs'] ?? ''" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Date To (BS)</label>
                        <x-bs-date-picker name="date_to" :value="$filters['date_to_bs'] ?? ''" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="generated" value="1">
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#6e0000]">
                    Generate Report
                </button>
            </div>
        </form>
    </section>

    @if($generated)
        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-black tracking-tight text-slate-950">Report Preview</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.reports.export', array_merge(['format' => 'pdf'], $exportQuery)) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-slate-700 hover:border-red-200 hover:text-[#8B0000]">Export PDF</a>
                    <a href="{{ route('admin.reports.export', array_merge(['format' => 'csv'], $exportQuery)) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-slate-700 hover:border-red-200 hover:text-[#8B0000]">Export CSV</a>
                    <a href="{{ route('admin.reports.export', array_merge(['format' => 'excel'], $exportQuery)) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-slate-700 hover:border-red-200 hover:text-[#8B0000]">Export Excel</a>
                </div>
            </div>

            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.08em] text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Attendance %</th>
                            <th class="px-4 py-3">Marks / Grade</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @forelse($rows as $row)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['student_name'] }}</td>
                                <td class="px-4 py-3">{{ $row['attendance'] }}</td>
                                <td class="px-4 py-3">{{ $row['marks_grade'] }}</td>
                                <td class="px-4 py-3">{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">No rows matched the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rows instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="mt-4">
                    {{ $rows->onEachSide(1)->links() }}
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
