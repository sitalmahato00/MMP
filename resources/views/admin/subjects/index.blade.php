@extends('layouts.app')

@section('title', 'Subjects Management')

@section('content')
<div x-data="{
    drawer: false,
    drawerLoading: false,
    drawerHtml: '',
    drawerSubjectId: null,
    openDrawer(id) {
        if (this.drawerSubjectId === id && this.drawer) return;
        this.drawerSubjectId = id;
        this.drawer = true;
        this.drawerLoading = true;
        this.drawerHtml = '';
        fetch('/admin/subjects/' + id + '/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(r => r.text())
        .then(html => { this.drawerHtml = html; this.drawerLoading = false; })
        .catch(() => { this.drawerHtml = '<p class=\'p-8 text-center text-red-500\'>Failed to load.</p>'; this.drawerLoading = false; });
    },
    closeDrawer() { this.drawer = false; this.drawerSubjectId = null; },
}" class="space-y-5" @keydown.escape.window="closeDrawer()">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Subjects Management</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Manage curriculum subjects, marking schemes, syllabus files, and teacher assignments across all departments.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.subjects.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Create Subject
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Subjects</p>
            <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $totalSubjects }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Theory Subjects</p>
            <p class="mt-1 text-2xl font-black text-blue-600">{{ $theoryCount }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Practical Subjects</p>
            <p class="mt-1 text-2xl font-black text-emerald-600">{{ $practicalCount }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Subjects</p>
            <p class="mt-1 text-2xl font-black text-amber-600">{{ $activeCount }}</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('admin.subjects.index') }}"
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
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            {{-- Search --}}
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or code..."
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            {{-- Department Filter --}}
            <div>
                <select name="department_id" x-model="deptId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Program Filter --}}
            <div>
                <select name="program_id" x-ref="progSelect" x-model="progId" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Semester Filter --}}
            <div>
                <select name="semester" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Semesters</option>
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" {{ request('semester') == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>

            {{-- Type Filter --}}
            <div>
                <select name="type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">All Types</option>
                    <option value="theory" {{ request('type') == 'theory' ? 'selected' : '' }}>Theory</option>
                    <option value="practical" {{ request('type') == 'practical' ? 'selected' : '' }}>Practical</option>
                    <option value="both" {{ request('type') == 'both' ? 'selected' : '' }}>Both</option>
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
            <span class="text-xs text-slate-500">Showing {{ $subjects->total() }} results</span>
            <div class="flex gap-2">
                <a href="{{ route('admin.subjects.index') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Reset</a>
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-700">Apply Filter</button>
            </div>
        </div>
    </form>

    {{-- Subjects Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Subject Name</th>
                        <th class="px-4 py-3">Department & Program</th>
                        <th class="px-4 py-3">Sem</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Full Marks</th>
                        <th class="px-4 py-3">Pass Marks</th>
                        <th class="px-4 py-3">Teachers</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $subject->code }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.subjects.show', $subject) }}" class="font-semibold text-slate-900 hover:text-red-700 dark:text-white">
                                    {{ $subject->name }}
                                </a>
                                @if($subject->credit_hours)
                                    <span class="ml-1 text-xs text-slate-400">({{ $subject->credit_hours }} Cr)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $subject->program?->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400">{{ $subject->program?->department?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">
                                Sem {{ $subject->semester }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                    {{ $subject->type === 'theory' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                    {{ $subject->type === 'practical' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : '' }}
                                    {{ $subject->type === 'both' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : '' }}">
                                    {{ ucfirst($subject->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $subject->total_full_marks }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $subject->total_pass_marks }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    {{ $subject->teachers->count() }} assigned
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openDrawer({{ $subject->id }})" title="Quick View"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <a href="{{ route('admin.subjects.show', $subject) }}" title="Manage Details"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" title="Edit Subject"
                                       class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Are you sure you want to delete {{ $subject->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete Subject"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950/40">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500">
                                No subjects found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subjects->hasPages())
            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                {{ $subjects->links() }}
            </div>
        @endif
    </div>

    {{-- Slide-over Drawer for Quick Inspection --}}
    <div x-show="drawer" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="closeDrawer()"></div>
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div class="w-screen max-w-md bg-white shadow-xl dark:bg-slate-900 flex flex-col">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Subject Overview</h2>
                    <button type="button" @click="closeDrawer()" class="rounded-lg p-1 text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <template x-if="drawerLoading">
                        <div class="flex h-48 items-center justify-center">
                            <div class="h-8 w-8 animate-spin rounded-full border-4 border-red-600 border-t-transparent"></div>
                        </div>
                    </template>
                    <div x-show="!drawerLoading" x-html="drawerHtml"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
