@extends('layouts.app')

@section('title', 'Attendance Sessions')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Attendance Sessions</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Complete log of recorded class sessions, teacher attendance entries, and headcounts.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendance.mark') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Mark New Session
            </a>
            <a href="{{ route('admin.attendance.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Analytics
            </a>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.attendance.sessions') }}"
          x-data="{
              deptId: '{{ (string)request('department_id', '') }}',
              progId: '{{ (string)request('program_id', '') }}',
              teacherId: '{{ (string)request('teacher_id', '') }}',
              allPrograms: @js($programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'department_id' => $p->department_id])),
              allTeachers: @js($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->user?->name ?? 'Teacher', 'department_id' => $t->department_id])),
              filterOptions() {
                  const progSel = this.$refs.progSelect;
                  if (progSel) {
                      const curP = this.progId;
                      while (progSel.options.length > 1) { progSel.remove(1); }
                      const pList = !this.deptId ? this.allPrograms : this.allPrograms.filter(p => String(p.department_id) === String(this.deptId));
                      pList.forEach(p => {
                          const opt = new Option(p.name, p.id);
                          if (String(p.id) === String(curP)) opt.selected = true;
                          progSel.add(opt);
                      });
                      if (curP && !pList.some(p => String(p.id) === String(curP))) {
                          this.progId = '';
                          progSel.selectedIndex = 0;
                      }
                  }
                  const teachSel = this.$refs.teacherSelect;
                  if (teachSel) {
                      const curT = this.teacherId;
                      while (teachSel.options.length > 1) { teachSel.remove(1); }
                      const tList = !this.deptId ? this.allTeachers : this.allTeachers.filter(t => String(t.department_id) === String(this.deptId));
                      tList.forEach(t => {
                          const opt = new Option(t.name, t.id);
                          if (String(t.id) === String(curT)) opt.selected = true;
                          teachSel.add(opt);
                      });
                      if (curT && !tList.some(t => String(t.id) === String(curT))) {
                          this.teacherId = '';
                          teachSel.selectedIndex = 0;
                      }
                  }
              },
              init() {
                  this.filterOptions();
                  this.$watch('deptId', () => this.filterOptions());
              }
          }"
          class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <select name="academic_session_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Academic Sessions</option>
                    @foreach($academicSessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                    @endforeach
                </select>
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
            <div>
                <select name="teacher_id" x-ref="teacherSelect" x-model="teacherId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
            <span class="text-xs text-slate-500">Total {{ $sessions->total() }} recorded sessions</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.attendance.sessions') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">Reset</a>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-700">Apply Filter</button>
            </div>
        </div>
    </form>

    {{-- Sessions Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Subject & Code</th>
                        <th class="px-4 py-3">Program & Sem</th>
                        <th class="px-4 py-3">Teacher</th>
                        <th class="px-4 py-3">Period</th>
                        <th class="px-4 py-3">Attendance Count</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sessions as $s)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                {{ $s->date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.attendance.sessions.show', $s) }}" class="font-bold text-slate-800 hover:text-red-700 dark:text-slate-200">
                                    {{ $s->subject?->name ?? '—' }}
                                </a>
                                <p class="font-mono text-xs text-slate-400">{{ $s->subject?->code }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-800 dark:text-slate-200 font-medium">{{ $s->program?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">Sem {{ $s->semester }} @if($s->section) (Sec {{ $s->section }}) @endif</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $s->teacher?->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $s->period }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $s->attendances_count }} students
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.attendance.sessions.show', $s) }}" title="View Sheet"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.attendance.sessions.edit', $s) }}" title="Edit Records"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.attendance.sessions.destroy', $s) }}" onsubmit="return confirm('Delete this attendance session and all student records?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Session"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No attendance sessions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sessions->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
