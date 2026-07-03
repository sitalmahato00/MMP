@extends('layouts.app')
@section('title', 'ID Card Reports')

@section('content')
<div x-data="reportPage()" class="space-y-6">

    {{-- ── Page Header ────────────────────────────────────────── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                ID Card Reports
            </h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Summary statistics and exportable student ID card data.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.id-cards.students.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                ID Generator
            </a>
            <a href="{{ route('admin.id-cards.students.bulk-list') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Bulk Print
            </a>
            <a href="{{ route('admin.id-cards.students.reports.export') }}?{{ http_build_query(request()->all()) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('admin.id-cards.students.report-print', request()->query()) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a01010] transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Report
            </a>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <form method="GET" id="filter-form"
          class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name / student ID…"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            <select name="program_id"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="department_id"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <select name="academic_session_id"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="">All Sessions</option>
                @foreach($sessions as $s)
                <option value="{{ $s->id }}" @selected(request('academic_session_id') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-[#8B0000] py-2 text-sm font-semibold text-white hover:bg-[#a01010] transition">
                    Filter
                </button>
                <a href="{{ route('admin.id-cards.students.reports') }}"
                   class="flex-1 rounded-xl border border-slate-200 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 transition">
                    Reset
                </a>
            </div>
        </div>
    </form>



    {{-- ── Student Table ────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">Student List</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $students->total() }} students match current filters</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.id-cards.students.bulk-list') }}?{{ http_build_query(request()->only('program_id','department_id','academic_session_id','semester')) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-[#8B0000]/10 px-3 py-1.5 text-xs font-semibold text-[#8B0000] hover:bg-[#8B0000]/20 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Bulk Print These
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">Student ID</th>
                        <th class="px-5 py-3">Program</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Semester</th>
                        <th class="px-5 py-3">Session</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($students as $i => $student)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="px-5 py-3 text-xs text-slate-400">{{ $students->firstItem() + $i }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $student->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=S&background=8B0000&color=fff&size=36' }}"
                                     class="h-8 w-8 rounded-lg object-cover flex-shrink-0" alt="{{ $student->user?->name }}">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $student->user?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $student->student_no ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $student->program?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-300">{{ $student->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($student->current_semester)
                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                Sem {{ $student->current_semester }}
                            </span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-500 dark:text-slate-400 text-xs">{{ $student->academicSession?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- View / print single --}}
                                <a href="{{ route('admin.id-cards.students.index') }}?student_id={{ $student->id }}"
                                   title="View Card"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-[#8B0000]/10 hover:text-[#8B0000] transition dark:bg-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.id-cards.students.single-pdf', $student) }}" target="_blank"
                                   title="Download PDF"
                                   class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-[#8B0000] hover:bg-red-100 transition dark:bg-red-900/20 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-400">No students match your filters.</p>
                                <a href="{{ route('admin.id-cards.students.reports') }}"
                                   class="text-xs font-semibold text-[#8B0000] hover:underline">Clear filters</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="border-t border-slate-100 px-5 py-3 dark:border-slate-800">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Print Styles ────────────────────────────────────── --}}
<style>
@media print {
    .sidebar, nav, header, .no-print { display: none !important; }
    body { background: #fff; }
    .rounded-2xl { border-radius: 0 !important; }
    .shadow-sm { box-shadow: none !important; }
}
</style>

@push('scripts')
<script>
function reportPage() {
    return {};
}
</script>
@endpush
@endsection
