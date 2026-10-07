@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Attendance Reports</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Detailed student-wise attendance records, participation ratios, and performance analytics.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.attendance.mark') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Mark Attendance
            </a>
            <a href="{{ route('admin.attendance.sessions') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Session Logs
            </a>
            <a href="{{ route('admin.attendance.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Analytics Dashboard
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Overall Attendance Rate</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ $overallRate }}%</span>
                <span class="text-xs font-semibold text-emerald-600">Institutional Average</span>
            </div>
            <div class="mt-3 h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-2 rounded-full bg-[#8B0000]" style="width: {{ min(100, $overallRate) }}%"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Recorded Sessions</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($totalSessionsCount) }}</span>
                <span class="text-xs text-slate-500">Class periods</span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Across all departments & programs</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Present Marks</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600">{{ number_format($totalPresentCount) }}</span>
                <span class="text-xs text-slate-500">of {{ number_format($totalRecordsCount) }} entries</span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Verified attendances</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Filtered Students</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($students->total()) }}</span>
                <span class="text-xs text-slate-500">in current view</span>
            </div>
            <p class="mt-3 text-xs text-slate-400">Showing page {{ $students->currentPage() }} of {{ $students->lastPage() }}</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.attendance.reports') }}"
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
          }"
          class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by student name, roll, reg no..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            <div>
                <select name="department_id" x-model="deptId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="program_id" x-ref="progSelect" x-model="progId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="semester" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Semesters</option>
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" {{ request('semester') == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
            <a href="{{ route('admin.attendance.reports') }}"
               class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                Clear Filters
            </a>
            <button type="submit"
                    class="rounded-xl bg-[#8B0000] px-4 py-2 text-xs font-bold text-white hover:bg-[#6b0000] transition">
                Apply Filter
            </button>
        </div>
    </form>

    {{-- Report Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Student Attendance Summary</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Total sessions, presence counts, and calculated attendance rates</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="border-b border-slate-100 bg-slate-50/50 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:border-slate-800 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3.5">Student Details</th>
                        <th class="px-4 py-3.5">Program & Department</th>
                        <th class="px-4 py-3.5 text-center">Semester</th>
                        <th class="px-4 py-3.5 text-center">Total Sessions</th>
                        <th class="px-4 py-3.5 text-center">Present</th>
                        <th class="px-4 py-3.5 text-center">Absent</th>
                        <th class="px-4 py-3.5 text-center">Late</th>
                        <th class="px-5 py-3.5 text-center">Attendance Rate</th>
                        <th class="px-4 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 flex-shrink-0 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <img src="{{ $student->user?->avatar_url ?? asset('images/default-avatar.png') }}"
                                             alt="{{ $student->user?->name }}"
                                             class="h-full w-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">
                                            {{ $student->user?->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            Roll: {{ $student->roll_number ?? '-' }} &bull; Reg: {{ $student->registration_number ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $student->program?->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $student->department?->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                Sem {{ $student->current_semester }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-slate-900 dark:text-white">
                                {{ $student->total_sessions }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-emerald-600">
                                {{ $student->present_sessions }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-rose-600">
                                {{ $student->absent_sessions }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold text-amber-600">
                                {{ $student->late_sessions }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="text-xs font-bold {{ $student->attendance_rate >= 75 ? 'text-emerald-600' : ($student->attendance_rate >= 50 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $student->attendance_rate }}%
                                    </span>
                                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div class="h-full rounded-full {{ $student->attendance_rate >= 75 ? 'bg-emerald-500' : ($student->attendance_rate >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                             style="width: {{ min(100, $student->attendance_rate) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.students.show', $student) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-300">No student attendance records found</p>
                                <p class="text-xs text-slate-400">Try modifying your search or filter criteria</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
