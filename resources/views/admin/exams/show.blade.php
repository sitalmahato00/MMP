@extends('layouts.app')
@section('title', $exam->name)

@section('content')
@php
    $typeLabel = match ($exam->type) {
        'regular' => 'Regular Semester Exam',
        'back' => 'Back / Partial Exam',
        'internal' => 'Internal / Monthly Test',
        'practical' => 'Practical Exam',
        default => ucfirst($exam->type),
    };

    $statusClasses = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $departmentLabel = $exam->department?->code ? $exam->department->code . ' - ' . $exam->department->name : ($exam->department?->name ?? 'Common');
    $uploadedMarksTotal = $uploadedMarkGroups->sum('marks_count');
@endphp

<div class="space-y-6" x-data="{
        markSearch: '',
        filterDepartment: 'all',
        filterSemester: 'all',
        filterSubject: 'all',
        filterTeacher: 'all',
        departments: @js($subjectRows->pluck('department_name')->filter()->unique()->values()->all()),
        semesters: @js($subjectRows->pluck('semester')->filter()->unique()->sort()->values()->all()),
        subjects: @js($subjectRows->pluck('subject_name')->filter()->unique()->sort()->values()->all()),
        teachers: @js($subjectRows->pluck('teacher_name')->filter()->unique()->values()->all()),
    }">
    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Total records</p>
                <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $allMarksCount ?? 0 }}</p>
                <p class="mt-1 text-sm text-slate-500">All uploaded mark records.</p>
            </article>
            <article class="rounded-[1.5rem] border border-slate-200 bg-emerald-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-emerald-700">Passed</p>
                <p class="mt-2 text-2xl font-black tracking-tight text-emerald-950">{{ $subjectRows->sum('passed_count') }}</p>
                <p class="mt-1 text-sm text-emerald-700">Total pass entries across subjects.</p>
            </article>
            <article class="rounded-[1.5rem] border border-slate-200 bg-rose-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#8B0000]">Failed</p>
                <p class="mt-2 text-2xl font-black tracking-tight text-[#8B0000]">{{ $subjectRows->sum('failed_count') }}</p>
                <p class="mt-1 text-sm text-[#8B0000]">Total fail entries across subjects.</p>
            </article>
            <article class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-400">Subjects</p>
                <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $subjectRows->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">Subjects with mark upload activity.</p>
            </article>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:flex-wrap">
            <div class="min-w-0 flex-1 lg:max-w-[28rem]">
                <label class="relative block w-full">
                    <span class="sr-only">Search uploaded marks</span>
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400"><i class="fas fa-search"></i></span>
                    <input type="search" x-model.debounce.250="markSearch" placeholder="Search all fields..." class="w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                </label>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:flex-1">
                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Department</span>
                    <select x-model="filterDepartment" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="all">Department</option>
                        <template x-for="department in departments" :key="department">
                            <option :value="department" x-text="department"></option>
                        </template>
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Semester</span>
                    <select x-model="filterSemester" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="all">Semester</option>
                        <template x-for="semester in semesters" :key="semester">
                            <option :value="semester" x-text="semester"></option>
                        </template>
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Subject</span>
                    <select x-model="filterSubject" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="all">Subject</option>
                        <template x-for="subject in subjects" :key="subject">
                            <option :value="subject" x-text="subject"></option>
                        </template>
                    </select>
                </label>
                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Teacher</span>
                    <select x-model="filterTeacher" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="all">Teacher</option>
                        <template x-for="teacher in teachers" :key="teacher">
                            <option :value="teacher" x-text="teacher"></option>
                        </template>
                    </select>
                </label>
            </div>
            <button type="button" @click="markSearch = ''" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear</button>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                    <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Program</th>
                        <th class="px-4 py-3 text-left">Semester</th>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Uploaded by</th>
                        <th class="px-4 py-3 text-left">Uploaded at</th>
                        <th class="px-4 py-3 text-right">Passes</th>
                        <th class="px-4 py-3 text-right">Failed</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($subjectRows as $row)
                        <tr data-department="{{ $row['department_name'] }}" data-program="{{ $row['program_name'] }}" data-semester="{{ $row['semester'] }}" data-subject="{{ $row['subject_name'] }}" data-teacher="{{ $row['teacher_name'] }}" x-show="(
                                (filterDepartment === 'all' || $el.dataset.department === filterDepartment) &&
                                (filterSemester === 'all' || $el.dataset.semester === filterSemester) &&
                                (filterSubject === 'all' || $el.dataset.subject === filterSubject) &&
                                (filterTeacher === 'all' || $el.dataset.teacher === filterTeacher)
                            ) && (markSearch.trim() === '' || (
                                ($el.dataset.department + ' ' + $el.dataset.program + ' ' + $el.dataset.semester + ' ' + $el.dataset.subject + ' ' + $el.dataset.teacher).toLowerCase().includes(markSearch.toLowerCase())
                            ))" class="transition hover:bg-slate-50/70">
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['department_code'] ? $row['department_code'] . ' - ' : '' }}{{ $row['department_name'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['program_code'] ? $row['program_code'] . ' - ' : '' }}{{ $row['program_name'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['semester'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['subject_code'] ? $row['subject_code'] . ' - ' : '' }}{{ $row['subject_name'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['teacher_name'] }}</td>
                            <td class="px-4 py-3.5 text-slate-700">{{ $row['last_updated'] }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-emerald-700">{{ $row['passed_count'] }}</td>
                            <td class="px-4 py-3.5 text-right font-semibold text-[#8B0000]">{{ $row['failed_count'] }}</td>
                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusClasses[$row['status_tone']] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $row['status_label'] }}</span></td>
                            <td class="px-4 py-3.5 text-right">
                                @if(! empty($row['subject_mark_id']))
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.exams.subjects.marks', ['exam' => $exam, 'subject' => $row['subject_id']]) }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100">View sheet</a>
                                        <a href="{{ route('admin.exams.marks.export', ['exam' => $exam, 'format' => 'excel']) }}?subject_id={{ $row['subject_id'] }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Excel</a>
                                        <a href="{{ route('admin.exams.marks.export', ['exam' => $exam, 'format' => 'pdf']) }}?subject_id={{ $row['subject_id'] }}" target="_blank" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100">PDF</a>
                                        <a href="{{ route('admin.exams.marks.edit', [$exam, $row['subject_mark_id']]) }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:bg-slate-100">Edit</a>
                                        <form method="POST" action="{{ route('admin.exams.marks.destroy', ['exam' => $exam, 'subject' => $row['subject_id']]) }}" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete all uploaded marks for this subject?');" class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100">Delete</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">No marks</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                                <x-empty-state title="No uploaded subject marks" message="Mark uploads by subject will appear here after teachers submit their entries."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
