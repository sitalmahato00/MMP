@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div class="space-y-6" x-data="{
    markAll(status) {
        document.querySelectorAll('input[type=radio][value=' + status + ']').forEach(r => {
            r.checked = true;
            r.dispatchEvent(new Event('change'));
        });
    }
}">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Mark Attendance</h1>
            <p class="mt-0.5 text-sm text-slate-500">Record classroom or laboratory student attendance sessions.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendance.sessions') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                All Sessions
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Attendance Dashboard
            </a>
        </div>
    </div>

    {{-- Class Selection Filter --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
         x-data="{
             deptId: '{{ (string)request('department_id', '') }}',
             progId: '{{ (string)request('program_id', '') }}',
             allPrograms: @js($programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'department_id' => $p->department_id])),
             filterPrograms() {
                 const sel = this.$refs.progSelect;
                 if (!sel) return;
                 const cur = this.progId;
                 while (sel.options.length > 1) { sel.remove(1); }
                 const list = !this.deptId ? this.allPrograms : this.allPrograms.filter(p => String(p.department_id) === String(this.deptId));
                 list.forEach(p => {
                     const opt = new Option(p.name, p.id);
                     if (String(p.id) === String(cur)) opt.selected = true;
                     sel.add(opt);
                 });
                 if (cur && !list.some(p => String(p.id) === String(cur))) {
                     this.progId = '';
                     sel.selectedIndex = 0;
                 }
             },
             init() {
                 this.filterPrograms();
                 this.$watch('deptId', () => this.filterPrograms());
             }
         }">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">1. Select Class & Subject</h2>
        <form method="GET" action="{{ route('admin.attendance.mark') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Department</label>
                <select name="department_id" x-model="deptId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Program *</label>
                <select name="program_id" x-ref="progSelect" x-model="progId" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">Select Program</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Semester *</label>
                <select name="semester" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" {{ request('semester', 1) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Section</label>
                <input type="text" name="section" value="{{ request('section') }}" placeholder="e.g. A"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 dark:bg-slate-700">
                    Load Students
                </button>
            </div>
        </form>
    </div>

    {{-- Attendance Entry Form --}}
    @if($students->isNotEmpty())
        @php
            $activeProg = $programs->firstWhere('id', request('program_id'));
            $activeDeptId = request('department_id') ?: $activeProg?->department_id;
            $filteredTeachers = $activeDeptId ? $teachers->filter(fn($t) => $t->department_id == $activeDeptId) : $teachers;
            if ($filteredTeachers->isEmpty()) { $filteredTeachers = $teachers; }
        @endphp
        <form method="POST" action="{{ route('admin.attendance.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="department_id" value="{{ $activeDeptId }}">
            <input type="hidden" name="program_id" value="{{ request('program_id') }}">
            <input type="hidden" name="semester" value="{{ request('semester') }}">
            <input type="hidden" name="section" value="{{ request('section') }}">

            {{-- Session Details --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">2. Session Details</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Session *</label>
                        <select name="academic_session_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="{{ $academicSession?->id }}">{{ $academicSession?->name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject *</label>
                        <select name="subject_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select Subject</option>
                            @php
                                $matchedSubjects = $subjects->filter(fn($subj) => (string)$subj->program_id === (string)request('program_id') && (string)$subj->semester === (string)request('semester'));
                                if ($matchedSubjects->isEmpty()) {
                                    $matchedSubjects = $subjects->filter(fn($subj) => (string)$subj->program_id === (string)request('program_id'));
                                }
                                if ($matchedSubjects->isEmpty()) {
                                    $matchedSubjects = $subjects;
                                }
                            @endphp
                            @foreach($matchedSubjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->code }} - {{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Teacher *</label>
                        <select name="teacher_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <option value="">Select Teacher</option>
                            @foreach($filteredTeachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Date (BS) *</label>
                        <x-bs-date-picker name="date" :value="old('date', bsDate(today(), 'Y-m-d'))" :required="true" placeholder="YYYY-MM-DD"
                                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"/>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Period / Time *</label>
                        <input type="text" name="period" value="Period 1" required
                               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Student List with Quick Bulk Controls --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">3. Mark Students</h2>
                        <p class="text-xs text-slate-500">Loaded {{ $students->count() }} active students.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mark all:</span>
                        <button type="button" @click="markAll('present')" class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                            All Present
                        </button>
                        <button type="button" @click="markAll('absent')" class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 hover:bg-rose-100">
                            All Absent
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-2.5">Roll</th>
                                <th class="px-4 py-2.5">Student Name</th>
                                <th class="px-4 py-2.5 text-center">Present</th>
                                <th class="px-4 py-2.5 text-center">Absent</th>
                                <th class="px-4 py-2.5 text-center">Late</th>
                                <th class="px-4 py-2.5">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($students as $st)
                                <tr>
                                    <td class="px-4 py-2.5 font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                        {{ $st->roll_number ?? $st->student_no ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold text-slate-900 dark:text-white">
                                        {{ $st->user?->name ?? 'Unknown Student' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="attendances[{{ $st->id }}]" value="present" checked
                                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                                        </label>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="attendances[{{ $st->id }}]" value="absent"
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500">
                                        </label>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="attendances[{{ $st->id }}]" value="late"
                                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500">
                                        </label>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input type="text" name="remarks[{{ $st->id }}]" placeholder="Remarks (optional)"
                                               class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-xs dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="rounded-xl bg-[#8B0000] px-8 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                        Submit Attendance Records
                    </button>
                </div>
            </div>
        </form>
    @elseif(request('program_id'))
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900">
            No active students found in this program and semester.
        </div>
    @endif
</div>
@endsection
