@extends('layouts.app')
@section('title', 'Reports Center')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#8B0000]/10 text-[#8B0000] dark:bg-[#8B0000]/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Reports Center</h1>
            </div>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Institutional data hub — generate, filter, analyze with visual charts, and export formal reports across departments.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.reports.export.csv', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-emerald-600/30 bg-emerald-50 px-4 py-2.5 text-xs font-bold text-emerald-800 shadow-sm transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-950/40 dark:text-emerald-300">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel / CSV
            </a>
            <a href="{{ route('admin.reports.export.print', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#6b0000]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print / Official PDF
            </a>
        </div>
    </div>

    {{-- Module Navigation Tabs --}}
    <div class="flex overflow-x-auto border-b border-slate-200 pb-2 dark:border-slate-800">
        <nav class="flex space-x-2" aria-label="Tabs">
            @php
                $tabs = [
                    'attendance' => ['label' => 'Attendance Records', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'students'   => ['label' => 'Student Demographics', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    'exams'      => ['label' => 'Examination Schedules', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    'marks'      => ['label' => 'Marks & Results', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'subjects'   => ['label' => 'Subjects & Courses', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ];
            @endphp
            @foreach($tabs as $tabKey => $tabMeta)
                <a href="{{ route('admin.reports.index', ['type' => $tabKey]) }}"
                   class="inline-flex items-center gap-2 whitespace-nowrap rounded-xl px-4 py-2.5 text-xs font-bold transition-all duration-150 {{ $type === $tabKey ? 'bg-[#8B0000] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tabMeta['icon'] }}"/>
                    </svg>
                    {{ $tabMeta['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- KPI Cards --}}
    @if(!empty($kpis))
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @php
                $kpiColors = [
                    'from-rose-500 to-[#8B0000]',
                    'from-blue-600 to-indigo-700',
                    'from-emerald-500 to-teal-700',
                    'from-amber-500 to-orange-600',
                ];
            @endphp
            @foreach($kpis as $idx => $kpi)
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $kpiColors[$idx % count($kpiColors)] }} p-4 shadow-sm text-white">
                    <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-white/80 truncate">{{ $kpi['label'] }}</p>
                    <p class="mt-1 text-2xl font-black tracking-tight">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Visual Analytics: Charts and Graphs --}}
    @if(!empty($charts))
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            {{-- Chart 1 Card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $charts['chart1']['title'] }}</h3>
                        <p class="text-[11px] text-slate-400">Proportional distribution analysis</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ ucfirst($charts['chart1']['type']) }}
                    </span>
                </div>
                <div class="mt-4 flex items-center justify-center relative" style="height: 250px;">
                    <canvas id="reportChart1"></canvas>
                </div>
            </div>

            {{-- Chart 2 Card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $charts['chart2']['title'] }}</h3>
                        <p class="text-[11px] text-slate-400">Term-wise comparison metrics</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ ucfirst($charts['chart2']['type']) }}
                    </span>
                </div>
                <div class="mt-4 flex items-center justify-center relative" style="height: 250px;">
                    <canvas id="reportChart2"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" action="{{ route('admin.reports.index') }}"
              x-data="{
                  deptId: '{{ (string)$departmentId }}',
                  progId: '{{ (string)$programId }}',
                  allPrograms: @js($programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'department_id' => $p->department_id])),
                  filterPrograms() {
                      const sel = this.$refs.progSelect;
                      if (!sel) return;
                      const cur = this.progId;
                      while (sel.options.length > 1) { sel.remove(1); }
                      const list = !this.deptId ? this.allPrograms : this.allPrograms.filter(p => String(p.department_id) === String(this.deptId));
                      list.forEach(p => {
                          const opt = new Option(p.name, p.id);
                          if (String(p.id) === String(cur)) { opt.selected = true; }
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
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- Department --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Department</label>
                <select name="department_id" x-model="deptId" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string)$departmentId === (string)$dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Program (Hierarchical: depends on Department) --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Program</label>
                <select name="program_id" x-ref="progSelect" x-model="progId" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ (string)$programId === (string)$prog->id ? 'selected' : '' }}>
                            {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Semester (Restricted strictly to 1..6) --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Semester</label>
                <select name="semester" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">All Semesters (1–6)</option>
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" {{ (string)$semester === (string)$i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>

            {{-- Academic Session --}}
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Session</label>
                <select name="academic_session_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <option value="">All Academic Sessions</option>
                    @foreach($academicSessions as $sess)
                        <option value="{{ $sess->id }}" {{ (string)$academicSessionId === (string)$sess->id ? 'selected' : '' }}>
                            {{ $sess->name }} {{ $sess->is_active ? '(Active)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Exam (if type == marks) or Search --}}
            @if($type === 'marks')
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Exam</label>
                    <select name="exam_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <option value="">All Examinations</option>
                        @foreach($exams as $ex)
                            <option value="{{ $ex->id }}" {{ (string)$examId === (string)$ex->id ? 'selected' : '' }}>
                                {{ $ex->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Search Keyword</label>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Name, roll, code..."
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#8B0000] px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Apply Filter
                </button>
                <a href="{{ route('admin.reports.index', ['type' => $type]) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main Data Presentation --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @if($type === 'attendance')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Department & Program</th>
                            <th class="px-4 py-3 text-center">Sem</th>
                            <th class="px-4 py-3 text-center">Total Held</th>
                            <th class="px-4 py-3 text-center">Present</th>
                            <th class="px-4 py-3 text-center">Absent</th>
                            <th class="px-4 py-3 text-center">Late</th>
                            <th class="px-4 py-3 text-center">Excused</th>
                            <th class="px-4 py-3 text-center">Attendance %</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($data as $st)
                            <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    <div class="font-bold">{{ $st->user?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">Roll: {{ $st->roll_number ?? '—' }} | No: {{ $st->student_no ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $st->program?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $st->department?->name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ $st->current_semester ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $st->total_sessions }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-600">
                                    {{ $st->present_sessions }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-rose-600">
                                    {{ $st->absent_sessions }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-amber-600">
                                    {{ $st->late_sessions }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-600">
                                    {{ $st->excused_sessions }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div class="h-full rounded-full {{ $st->attendance_rate >= 80 ? 'bg-emerald-500' : ($st->attendance_rate >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                 style="width: {{ min(100, $st->attendance_rate) }}%"></div>
                                        </div>
                                        <span class="font-black {{ $st->attendance_rate >= 80 ? 'text-emerald-700 dark:text-emerald-400' : ($st->attendance_rate >= 60 ? 'text-amber-700 dark:text-amber-400' : 'text-rose-700 dark:text-rose-400') }}">
                                            {{ $st->attendance_rate }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $st->attendance_rate >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : ($st->attendance_rate >= 60 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300') }}">
                                        {{ $st->attendance_rate >= 80 ? 'Regular' : ($st->attendance_rate >= 60 ? 'Warning' : 'Critical') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    No attendance records found matching the active criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($type === 'students')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Student No / Roll</th>
                            <th class="px-4 py-3">Department & Program</th>
                            <th class="px-4 py-3 text-center">Semester</th>
                            <th class="px-4 py-3">Academic Session</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($data as $st)
                            <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                                    <div class="font-bold">{{ $st->user?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $st->user?->email ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                    <div>{{ $st->student_no ?? '—' }}</div>
                                    <div class="text-[11px] font-normal text-slate-400">Roll: {{ $st->roll_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $st->program?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $st->department?->name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    Semester {{ $st->current_semester ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $st->academicSession?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $st->status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ ucfirst($st->status ?? 'active') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    No student records found matching the active criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($type === 'exams')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-3">Examination Name</th>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Academic Session</th>
                            <th class="px-4 py-3 text-center">Type</th>
                            <th class="px-4 py-3 text-center">Exam Date</th>
                            <th class="px-4 py-3 text-center">Marks Recorded</th>
                            <th class="px-4 py-3 text-right">Publication</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($data as $ex)
                            <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                    {{ $ex->name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $ex->department?->name ?? 'All Departments' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    {{ $ex->academicSession?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center uppercase font-bold text-slate-700 dark:text-slate-300">
                                    {{ $ex->type ?? 'terminal' }}
                                </td>
                                <td class="px-4 py-3 text-center text-slate-600 dark:text-slate-300">
                                    {{ $ex->exam_date ? \Carbon\Carbon::parse($ex->exam_date)->format('M d, Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-blue-600">
                                    {{ number_format($ex->marks_count ?? 0) }} entries
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $ex->is_published ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' }}">
                                        {{ $ex->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    No examinations found matching the active criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($type === 'marks')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-3">Examination</th>
                            <th class="px-4 py-3">Student Name & Roll</th>
                            <th class="px-4 py-3">Subject</th>
                            <th class="px-4 py-3 text-center">Semester</th>
                            <th class="px-4 py-3 text-center">Theory</th>
                            <th class="px-4 py-3 text-center">Practical</th>
                            <th class="px-4 py-3 text-center">Total Score</th>
                            <th class="px-4 py-3 text-right">Evaluation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($data as $mk)
                            <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $mk->exam?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $mk->student?->user?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">Roll: {{ $mk->student?->roll_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div class="font-bold">{{ $mk->subject?->name ?? '—' }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $mk->subject?->code ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    Sem {{ $mk->semester }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $mk->total_theory }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $mk->total_practical }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-black text-slate-900 dark:text-white">
                                    {{ $mk->total_marks }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($mk->is_absent)
                                        <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-800 dark:bg-rose-900/50 dark:text-rose-300">
                                            Absent
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                            Graded
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    No mark entries found matching the active criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @elseif($type === 'subjects')
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-3">Subject Code</th>
                            <th class="px-4 py-3">Subject Title</th>
                            <th class="px-4 py-3">Program & Department</th>
                            <th class="px-4 py-3 text-center">Semester</th>
                            <th class="px-4 py-3 text-center">Credit Hours</th>
                            <th class="px-4 py-3 text-center">Type</th>
                            <th class="px-4 py-3">Assigned Faculty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($data as $sb)
                            <tr class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono font-black text-[#8B0000] dark:text-rose-400">
                                    {{ $sb->code }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                    {{ $sb->name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $sb->program?->name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $sb->program?->department?->name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    Semester {{ $sb->semester }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                    {{ $sb->credit_hours ?? 3 }} cr
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $sb->type === 'theory' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : ($sb->type === 'practical' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300') }}">
                                        {{ ucfirst($sb->type ?? 'both') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                    @if($sb->teachers && $sb->teachers->isNotEmpty())
                                        {{ $sb->teachers->map(fn($t) => $t->user?->name)->filter()->join(', ') }}
                                    @else
                                        <span class="text-slate-400 italic">Unassigned</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    No subjects found matching the active criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Pagination Container with Details --}}
        @if(method_exists($data, 'hasPages') && $data->hasPages())
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-200 p-4 dark:border-slate-800">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Showing <span class="font-bold text-slate-800 dark:text-slate-200">{{ $data->firstItem() }}</span> to
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $data->lastItem() }}</span> of
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $data->total() }}</span> entries
                </div>
                <div>
                    {{ $data->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(!empty($charts['chart1']))
    const ctx1 = document.getElementById('reportChart1');
    if (ctx1) {
        new Chart(ctx1, {
            type: '{{ $charts['chart1']['type'] }}',
            data: {
                labels: {!! json_encode($charts['chart1']['labels']) !!},
                datasets: [{
                    label: '{{ $charts['chart1']['title'] }}',
                    data: {!! json_encode($charts['chart1']['data']) !!},
                    backgroundColor: {!! json_encode($charts['chart1']['colors'] ?? ($charts['chart1']['color'] ?? '#8B0000')) !!},
                    borderRadius: {{ $charts['chart1']['type'] === 'bar' ? 6 : 0 }},
                    borderWidth: 1,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: '{{ $charts['chart1']['type'] === 'doughnut' ? 'bottom' : 'top' }}',
                        labels: {
                            font: { family: 'Inter', size: 11, weight: '600' },
                            boxWidth: 12,
                            padding: 12,
                        }
                    }
                },
                @if($charts['chart1']['type'] === 'bar')
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.15)' },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
                @endif
            }
        });
    }
    @endif

    @if(!empty($charts['chart2']))
    const ctx2 = document.getElementById('reportChart2');
    if (ctx2) {
        new Chart(ctx2, {
            type: '{{ $charts['chart2']['type'] }}',
            data: {
                labels: {!! json_encode($charts['chart2']['labels']) !!},
                datasets: [{
                    label: '{{ $charts['chart2']['title'] }}',
                    data: {!! json_encode($charts['chart2']['data']) !!},
                    backgroundColor: {!! json_encode($charts['chart2']['colors'] ?? ($charts['chart2']['color'] ?? '#8B0000')) !!},
                    borderRadius: {{ $charts['chart2']['type'] === 'bar' ? 6 : 0 }},
                    borderWidth: 1,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: '{{ $charts['chart2']['type'] === 'doughnut' ? 'bottom' : 'top' }}',
                        labels: {
                            font: { family: 'Inter', size: 11, weight: '600' },
                            boxWidth: 12,
                            padding: 12,
                        }
                    }
                },
                @if($charts['chart2']['type'] === 'bar')
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.15)' },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
                @endif
            }
        });
    }
    @endif
});
</script>
@endpush
