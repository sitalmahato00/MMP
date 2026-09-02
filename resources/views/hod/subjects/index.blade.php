@extends('layouts.app')

@section('title', 'Subjects Management')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_hod_subjects_view') ?? 'table',
    selected: [],
    drawer: false,
    drawerLoading: false,
    drawerHtml: '',
    drawerSubjectId: null,
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_subjects_view', v); },
    toggleAll(ids) {
        if (this.selected.length === ids.length) { this.selected = []; }
        else { this.selected = ids; }
    },
    openDrawer(id) {
        if (this.drawerSubjectId === id && this.drawer) return;
        this.drawerSubjectId = id;
        this.drawer = true;
        this.drawerLoading = true;
        this.drawerHtml = '';
        fetch('/hod/subjects/' + id + '/drawer', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
        .then(r => r.text())
        .then(html => { this.drawerHtml = html; this.drawerLoading = false; })
        .catch(() => { this.drawerHtml = '<p class=\'p-8 text-center text-red-500\'>Failed to load.</p>'; this.drawerLoading = false; });
    },
    closeDrawer() { this.drawer = false; this.drawerSubjectId = null; },
}" class="space-y-5"
   @keydown.escape.window="closeDrawer()"
>

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Subjects Management</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $department->name }} — manage subjects, marking schemes, and teacher assignments
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('hod.subjects.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create Subject
        </a>
    </div>
</div>

{{-- ── FILTERS ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('hod.subjects.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search subject name or code…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            @foreach($programs as $prog)
                <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->code }} - {{ $prog->name }}</option>
            @endforeach
        </select>
        {{-- Semester + Apply --}}
        <div class="flex gap-2">
            <select name="semester" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All Semesters</option>
                @for($i = 1; $i <= 6; $i++)
                    <option value="{{ $i }}" @selected(request('semester') == $i)>Semester {{ $i }}</option>
                @endfor
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search', 'program_id', 'semester']))
            <a href="{{ route('hod.subjects.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Panel header: result count + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($subjects->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $subjects->firstItem() }}–{{ $subjects->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($subjects->total()) }}</span> subjects
            @else
                No subjects match your filters
            @endif
        </p>

        {{-- View toggle --}}
        <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('table')"
                    :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                Table
            </button>
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

    {{-- ── TABLE VIEW ─────────────────────────────────────── --}}
    <div x-show="view === 'table'" x-cloak>
        @if($subjects->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No subjects found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters, or create a new subject.</p>
                <a href="{{ route('hod.subjects.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white hover:bg-[#1e40af] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Subject
                </a>
            </div>
        @else
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Program</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Semester</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden xl:table-cell">Resources</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Theory Marks</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Practical Marks</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($subjects as $subject)
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <button type="button" @click="openDrawer({{ $subject->id }})"
                                    class="text-left">
                                <div class="text-sm font-semibold text-slate-900 hover:text-[#1d4ed8] transition">{{ $subject->name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $subject->code }}</div>
                            </button>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-sm text-slate-900">{{ $subject->program->code }}</div>
                            <div class="text-xs text-slate-500 truncate max-w-[160px]">{{ $subject->program->name }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                Sem {{ $subject->semester }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center hidden xl:table-cell">
                            <div class="flex flex-col items-center gap-1 text-[11px]">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-semibold {{ $subject->syllabus ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $subject->syllabus ? 'Syllabus PDF' : 'No Syllabus' }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-semibold {{ filled($subject->details) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ filled($subject->details) ? 'Details Added' : 'No Details' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center hidden lg:table-cell">
                            <div class="text-xs text-slate-600">
                                <div>Int: {{ $subject->pass_marks_internal_theory ?? 0 }}/{{ $subject->full_marks_internal_theory ?? 0 }}</div>
                                <div>Ext: {{ $subject->pass_marks_external_theory ?? 0 }}/{{ $subject->full_marks_external_theory ?? 0 }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-center hidden lg:table-cell">
                            <div class="text-xs text-slate-600">
                                @if($subject->hasPractical())
                                    <div>Int: {{ $subject->pass_marks_internal_practical ?? 0 }}/{{ $subject->full_marks_internal_practical ?? 0 }}</div>
                                    <div>Ext: {{ $subject->pass_marks_external_practical ?? 0 }}/{{ $subject->full_marks_external_practical ?? 0 }}</div>
                                @else
                                    <span class="text-slate-400">No Practical</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('hod.subjects.show', $subject) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('hod.subjects.edit', $subject) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('hod.subjects.destroy', $subject) }}" method="POST"
                                      onsubmit="return confirm('Delete \'{{ addslashes($subject->name) }}\'? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($subjects->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $subjects->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if($subjects->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No subjects found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or create a new subject.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($subjects as $subject)
            <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                {{-- Icon --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700">Sem {{ $subject->semester }}</span>
                </div>
                
                {{-- Subject Info --}}
                <button type="button" @click="openDrawer({{ $subject->id }})"
                        class="text-left w-full mb-3">
                    <h3 class="text-sm font-bold text-slate-900 hover:text-[#1d4ed8] transition leading-tight line-clamp-2">{{ $subject->name }}</h3>
                    <p class="mt-0.5 font-mono text-[11px] text-slate-400">{{ $subject->code }}</p>
                </button>
                
                {{-- Program --}}
                <div class="mb-3 space-y-0.5">
                    <p class="text-xs text-slate-600 font-medium truncate">{{ $subject->program->name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $subject->program->code }}</p>
                </div>

                <div class="mb-3 flex flex-wrap gap-1.5">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $subject->syllabus ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $subject->syllabus ? 'Syllabus PDF' : 'No Syllabus' }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ filled($subject->details) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ filled($subject->details) ? 'Details Added' : 'No Details' }}
                    </span>
                </div>
                
                {{-- Marks Summary --}}
                <div class="mb-3 rounded-lg bg-slate-50 p-2 text-[11px]">
                    <div class="flex justify-between mb-1">
                        <span class="text-slate-500">Theory:</span>
                        <span class="font-semibold text-slate-700">{{ $subject->total_theory_marks }}</span>
                    </div>
                    @if($subject->hasPractical())
                    <div class="flex justify-between">
                        <span class="text-slate-500">Practical:</span>
                        <span class="font-semibold text-slate-700">{{ $subject->total_practical_marks }}</span>
                    </div>
                    @endif
                </div>
                
                {{-- Actions --}}
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('hod.subjects.show', $subject) }}"
                       class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                    <a href="{{ route('hod.subjects.edit', $subject) }}"
                       class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                    <form action="{{ route('hod.subjects.destroy', $subject) }}" method="POST"
                          onsubmit="return confirm('Delete \'{{ addslashes($subject->name) }}\'? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-lg border border-red-200 bg-red-50 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 transition">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if($subjects->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $subjects->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

{{-- ── SUBJECT DETAIL DRAWER ──────────────────────────────── --}}
{{-- Backdrop --}}
<div x-show="drawer"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="closeDrawer()"
     class="fixed inset-0 z-40 bg-black/30 backdrop-blur-[2px]"
     x-cloak></div>

{{-- Panel --}}
<div x-show="drawer"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full"
     class="fixed inset-y-0 right-0 z-50 w-full max-w-2xl bg-white shadow-2xl overflow-y-auto"
     x-cloak>
    
    {{-- Header --}}
    <div class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
        <h2 class="text-lg font-bold text-slate-900">Subject Details</h2>
        <button type="button" @click="closeDrawer()"
                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Content --}}
    <div class="p-6">
        <template x-if="drawerLoading">
            <div class="flex items-center justify-center py-20">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-[#1d4ed8]"></div>
            </div>
        </template>
        <div x-html="drawerHtml"></div>
    </div>
</div>

</div>{{-- /x-data --}}
@endsection
