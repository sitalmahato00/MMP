@extends('layouts.app')
@section('title', 'Student ID Cards')

@section('content')
<div x-data="{
    selected: [],
    allIds: {{ $students->pluck('id')->toJson() }},
    toggleAll() {
        if (this.selected.length === this.allIds.length) { this.selected = []; }
        else { this.selected = [...this.allIds]; }
    },
    isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length; },
    async generatePdf() {
        if (this.selected.length === 0) return;
        const form = document.getElementById('bulk-pdf-form');
        // Clear existing inputs
        form.querySelectorAll('input[name=\'ids[]\']').forEach(el => el.remove());
        this.selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    }
}">

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Student ID Cards</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Filter, select students and generate printable ID card PDFs.</p>
        </div>
        <button
            @click="generatePdf()"
            :disabled="selected.length === 0"
            class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M17 9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v2"/>
            </svg>
            Generate PDF
            <span x-show="selected.length > 0" x-text="'(' + selected.length + ')'" class="ml-0.5"></span>
        </button>
    </div>

    {{-- Hidden bulk form --}}
    <form id="bulk-pdf-form" method="POST" action="{{ route('admin.id-cards.students.bulk-pdf') }}" target="_blank">
        @csrf
    </form>

    {{-- Filters --}}
    <form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name or student no…"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm placeholder-slate-400 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
        </div>
        <div>
            <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">All Programs</option>
                @foreach ($programs as $prog)
                    <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <select name="semester" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">All Semesters</option>
                @for ($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" @selected(request('semester') == $i)>Semester {{ $i }}</option>
                @endfor
            </select>
            <button type="submit" class="rounded-xl bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                Filter
            </button>
            @if (request()->anyFilled(['search','department_id','program_id','semester','academic_session_id']))
                <a href="{{ route('admin.id-cards.students.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 overflow-hidden">
        {{-- Table head with select-all --}}
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 px-4 py-3 bg-slate-50 dark:bg-slate-800/60">
            <label class="flex items-center gap-2 cursor-pointer select-none text-sm font-medium text-slate-700 dark:text-slate-300">
                <input
                    type="checkbox"
                    @click="toggleAll()"
                    :checked="isAllSelected()"
                    class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                Select all on this page
            </label>
            <span class="text-xs text-slate-400">{{ $students->total() }} student(s) found</span>
        </div>

        @if ($students->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 dark:text-slate-400">No active students found.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Program / Dept</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Section</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Blood</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($students as $student)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition" :class="selected.includes({{ $student->id }}) ? 'bg-red-50 dark:bg-red-900/10' : ''">
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                :value="{{ $student->id }}"
                                x-model="selected"
                                class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $student->user?->avatar_url }}"
                                    alt=""
                                    class="h-9 w-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $student->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $student->student_no }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ $student->program?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $student->department?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                            {{ $student->current_semester ? 'Sem ' . $student->current_semester : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                            {{ $student->section ?: '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($student->blood_group)
                                <span class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    {{ $student->blood_group }}
                                </span>
                            @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('admin.id-cards.students.single-pdf', $student) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($students->hasPages())
            <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-3">
                {{ $students->links() }}
            </div>
            @endif
        @endif
    </div>

</div>
@endsection
