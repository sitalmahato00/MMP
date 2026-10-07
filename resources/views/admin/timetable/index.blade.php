@extends('layouts.app')

@section('title', 'Timetables Management')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Timetables Management</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Manage routine schedules and classroom lecture slots across all academic programs.
            </p>
        </div>
        <a href="{{ route('admin.timetable.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create Timetable
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Timetables</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $totalTimetables }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Timetables</p>
            <p class="mt-1 text-2xl font-black text-emerald-600">{{ $activeTimetables }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Scheduled Slots</p>
            <p class="mt-1 text-2xl font-black text-blue-600">{{ $totalSlots }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.timetable.index') }}"
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
            <div>
                <select name="academic_session_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Sessions</option>
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
                <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
            <span class="text-xs text-slate-500">Showing {{ $timetables->total() }} timetables</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.timetable.index') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">Reset</a>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-700">Apply Filter</button>
            </div>
        </div>
    </form>

    {{-- Timetable Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Program & Department</th>
                        <th class="px-4 py-3">Session</th>
                        <th class="px-4 py-3">Semester</th>
                        <th class="px-4 py-3">Section</th>
                        <th class="px-4 py-3">Effective Date</th>
                        <th class="px-4 py-3">Slots</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($timetables as $timetable)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                <a href="{{ route('admin.timetable.show', $timetable) }}" class="hover:text-red-700">
                                    {{ $timetable->program?->name ?? '—' }}
                                </a>
                                <p class="text-xs text-slate-400">{{ $timetable->program?->department?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $timetable->academicSession?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">
                                Semester {{ $timetable->semester }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $timetable->section ?? 'All Sections' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                {{ $timetable->effective_from?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $timetable->slots->count() }} slots
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($timetable->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.timetable.show', $timetable) }}" title="View & Edit Routine"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.timetable.export', $timetable) }}" target="_blank" title="Print Routine"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.timetable.edit', $timetable) }}" title="Edit Info"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.timetable.destroy', $timetable) }}" onsubmit="return confirm('Delete this timetable routine?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Timetable"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No timetables found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($timetables->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $timetables->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
