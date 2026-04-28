@extends('layouts.app')
@section('title', 'Programs')

@section('content')
@php
    $gradients = [
        'from-[#8B0000] to-rose-700',
        'from-violet-600 to-purple-700',
        'from-blue-600 to-indigo-700',
        'from-emerald-600 to-teal-700',
        'from-amber-500 to-orange-600',
        'from-cyan-600 to-sky-700',
    ];
    $icons = ['📘','🎓','🔬','💻','🏛️','🧪','📐','🌐','⚙️','🎨'];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp.programs.view') || 'card',
    bulkIds: [],
    bulkAction: '',
    confirmBulk: false,
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.bulkIds = {{ $programs->pluck('id')->toJson() }};
        } else {
            this.bulkIds = [];
        }
    },
    runBulk() {
        if (!this.bulkIds.length || !this.bulkAction) return;
        fetch('{{ route('admin.programs.bulk-action') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids: this.bulkIds, action: this.bulkAction })
        }).then(r => r.json()).then(() => window.location.reload());
    }
}" x-init="$watch('view', v => localStorage.setItem('mmp.programs.view', v))"
   class="space-y-6">

    {{-- ── PAGE HEADER ── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Programs</h1>
            <p class="mt-0.5 text-sm text-slate-500">Manage academic programs, syllabi, and curriculum structure.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.programs.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Program
            </a>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
        </div>
    </div>

    {{-- ── KPI BAR ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $kpis = [
            ['icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
              'label'=>'Total Programs', 'value'=>$totalPrograms, 'sub'=>$activePrograms.' active', 'color'=>'bg-[#8B0000]/10 text-[#8B0000]'],
            ['icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
              'label'=>'Departments Active', 'value'=>$deptCount, 'sub'=>'with programs', 'color'=>'bg-violet-100 text-violet-700'],
            ['icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0',
              'label'=>'Total Students', 'value'=>$totalStudents, 'sub'=>'across programs', 'color'=>'bg-blue-100 text-blue-700'],
            ['icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
              'label'=>'Total Subjects', 'value'=>$totalSubjects, 'sub'=>'across all semesters', 'color'=>'bg-emerald-100 text-emerald-700'],
            ['icon'=>'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
              'label'=>'Active Programs', 'value'=>$activePrograms, 'sub'=>($totalPrograms-$activePrograms).' inactive', 'color'=>'bg-amber-100 text-amber-700'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $kpi['color'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $kpi['icon'] }}"/>
                    </svg>
                </div>
                <span class="text-2xl font-black text-slate-900">{{ number_format($kpi['value']) }}</span>
            </div>
            <p class="text-xs font-bold text-slate-700">{{ $kpi['label'] }}</p>
            <p class="mt-0.5 text-[11px] text-slate-400">{{ $kpi['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── FILTER BAR ── --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.programs.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or code…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-800 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                </div>
            </div>
            <div class="min-w-[160px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Department</label>
                <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-800 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Duration</label>
                <select name="duration_years" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-800 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                    <option value="">Any</option>
                    @foreach([1,2,3,4] as $yr)
                        <option value="{{ $yr }}" {{ request('duration_years') == $yr ? 'selected' : '' }}>{{ $yr }} Year{{ $yr > 1 ? 's' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Semesters</label>
                <select name="total_semesters" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-800 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                    <option value="">Any</option>
                    @foreach([3,4,6,8] as $sem)
                        <option value="{{ $sem }}" {{ request('total_semesters') == $sem ? 'selected' : '' }}>{{ $sem }} Sem.</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-800 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">Filter</button>
                <a href="{{ route('admin.programs.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Reset</a>
            </div>
        </form>
    </div>

    {{-- ── VIEW TOGGLE + BULK BAR ── --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-500 font-medium">{{ $programs->total() }} {{ Str::plural('program', $programs->total()) }}</span>
            <div x-show="bulkIds.length > 0" x-cloak class="flex items-center gap-2">
                <span class="text-xs font-bold text-[#8B0000]" x-text="bulkIds.length + ' selected'"></span>
                <select x-model="bulkAction" class="rounded-lg border border-slate-200 bg-white py-1.5 px-2 text-xs font-semibold text-slate-700 focus:outline-none">
                    <option value="">Bulk Action…</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="button" @click="confirmBulk = true"
                        :disabled="!bulkAction"
                        class="rounded-lg bg-[#8B0000] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#7a0000] transition disabled:opacity-40">Apply</button>
                <button type="button" @click="bulkIds = []; selectAll = false"
                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Clear</button>
            </div>
        </div>
        <div class="flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
            <button type="button" @click="view = 'card'"
                    :class="view === 'card' ? 'bg-[#8B0000] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
            <button type="button" @click="view = 'table'"
                    :class="view === 'table' ? 'bg-[#8B0000] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 4v16M3 4h18a1 1 0 011 1v14a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                Table
            </button>
        </div>
    </div>

    {{-- ═══════════ CARD VIEW ═══════════ --}}
    <div x-show="view === 'card'" x-cloak>
        @if($programs->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <p class="text-base font-bold text-slate-700">No programs found</p>
            <p class="mt-1 text-sm text-slate-400">Try adjusting your filters or add a new program.</p>
            <a href="{{ route('admin.programs.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">+ Add Program</a>
        </div>
        @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($programs as $program)
            @php
                $grad = $gradients[$program->id % count($gradients)];
                $icon = $icons[$program->id % count($icons)];
            @endphp
            <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                {{-- Bulk checkbox --}}
                <div class="absolute top-3 left-3 z-10">
                    <input type="checkbox" :value="{{ $program->id }}" x-model="bulkIds"
                           class="h-4 w-4 rounded border-white/50 bg-white/30 accent-[#8B0000] cursor-pointer"/>
                </div>

                {{-- Card banner --}}
                <div class="relative h-36 bg-gradient-to-br {{ $grad }} overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.07]" style="background-image:url('data:image/svg+xml,%3Csvg width%3D%2260%22 height%3D%2260%22 viewBox%3D%220 0 60 60%22 xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg fill%3D%22%23fff%22 fill-opacity%3D%221%22%3E%3Cpath d%3D%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-5xl drop-shadow-lg">{{ $icon }}</span>
                    </div>
                    <div class="absolute top-3 right-3">
                        @if($program->is_active)
                            <span class="rounded-full bg-emerald-400/30 backdrop-blur-sm border border-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white">● Active</span>
                        @else
                            <span class="rounded-full bg-black/25 backdrop-blur-sm px-2.5 py-0.5 text-[10px] font-bold text-white/60">● Inactive</span>
                        @endif
                    </div>
                    <div class="absolute bottom-0 inset-x-0 px-4 pb-3">
                        <span class="inline-block rounded-lg bg-black/25 backdrop-blur-sm px-2.5 py-1 text-[10px] font-bold text-white truncate max-w-full">
                            {{ $program->department?->name ?? 'No Department' }}
                        </span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="flex flex-1 flex-col p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="text-sm font-black text-slate-900 line-clamp-2 leading-tight">{{ $program->name }}</h3>
                            <p class="mt-0.5 font-mono text-[11px] font-semibold text-slate-400">{{ $program->code }}</p>
                        </div>
                        @if($program->affiliation_type)
                        <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500">{{ $program->affiliation_type }}</span>
                        @endif
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-slate-50 p-2 text-center">
                            <p class="text-base font-black text-slate-900">{{ $program->duration_years }}Y</p>
                            <p class="text-[9px] text-slate-400 font-medium">Duration</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-2 text-center">
                            <p class="text-base font-black text-slate-900">{{ $program->total_semesters }}</p>
                            <p class="text-[9px] text-slate-400 font-medium">Semesters</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-2 text-center">
                            <p class="text-base font-black text-slate-900">{{ $program->subjects_count }}</p>
                            <p class="text-[9px] text-slate-400 font-medium">Subjects</p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-xs font-semibold text-slate-600">{{ $program->students_count }} students</span>
                        </div>
                        @if($program->department?->hod)
                        <div class="flex items-center gap-1.5">
                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br {{ $grad }} text-[9px] font-black text-white">
                                {{ strtoupper(substr($program->department->hod->name, 0, 1)) }}
                            </div>
                            <span class="max-w-[80px] truncate text-[10px] text-slate-500">{{ explode(' ', $program->department->hod->name)[0] }}</span>
                        </div>
                        @endif
                    </div>

                    @if($program->syllabus)
                    <div class="mt-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-red-400 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm0 2l4 4h-4V4zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        <span class="text-[10px] text-slate-400">Syllabus available</span>
                    </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    <a href="{{ route('admin.programs.show', $program) }}"
                       class="flex-1 rounded-lg bg-[#8B0000]/10 px-2.5 py-1.5 text-center text-xs font-bold text-[#8B0000] hover:bg-[#8B0000]/20 transition">
                        View
                    </a>
                    <a href="{{ route('admin.programs.edit', $program) }}"
                       class="flex-1 rounded-lg bg-slate-100 px-2.5 py-1.5 text-center text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                        Edit
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                                class="rounded-lg bg-slate-100 p-1.5 text-slate-500 hover:bg-slate-200 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                        </button>
                        <div x-show="open" x-cloak
                             class="absolute right-0 bottom-full mb-1 z-20 w-40 rounded-xl border border-slate-100 bg-white py-1 shadow-xl"
                             x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            @if($program->syllabus)
                            <a href="{{ asset('storage/'.$program->syllabus) }}" target="_blank"
                               class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                View Syllabus
                            </a>
                            @endif
                            <hr class="my-1 border-slate-100">
                            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($program->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="flex w-full items-center gap-2 px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($programs->hasPages())
        <div class="mt-4">{{ $programs->links() }}</div>
        @endif
    </div>

    {{-- ═══════════ TABLE VIEW ═══════════ --}}
    <div x-show="view === 'table'" x-cloak>
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="mmp-table-wrap">
                <table class="mmp-table divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80 backdrop-blur-sm">
                        <tr>
                            <th class="px-4 py-3.5 text-left">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                       class="h-4 w-4 rounded border-slate-300 accent-[#8B0000] cursor-pointer"/>
                            </th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Program</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Department</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Duration</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Sem.</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Subjects</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Students</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">HOD</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Syllabus</th>
                            <th class="px-4 py-3.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-4 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($programs as $program)
                        @php $grad = $gradients[$program->id % count($gradients)]; @endphp
                        <tr class="group hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3.5">
                                <input type="checkbox" :value="{{ $program->id }}" x-model="bulkIds"
                                       class="h-4 w-4 rounded border-slate-300 accent-[#8B0000] cursor-pointer"/>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-base">
                                        {{ $icons[$program->id % count($icons)] }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 leading-tight truncate max-w-[200px]">{{ $program->name }}</p>
                                        <p class="font-mono text-[11px] text-slate-400">{{ $program->code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $program->department?->name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $program->duration_years }} yr</td>
                            <td class="px-4 py-3.5 text-sm font-semibold text-slate-700">{{ $program->total_semesters }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-slate-900">{{ $program->subjects_count }}</td>
                            <td class="px-4 py-3.5 text-sm font-bold text-slate-900">{{ $program->students_count }}</td>
                            <td class="px-4 py-3.5">
                                @if($program->department?->hod)
                                <div class="flex items-center gap-2">
                                    @if($program->department->hod->avatar)
                                    <img src="{{ asset('storage/'.$program->department->hod->avatar) }}" class="h-6 w-6 rounded-full object-cover ring-1 ring-slate-200"/>
                                    @else
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br {{ $grad }} text-[9px] font-black text-white">
                                        {{ strtoupper(substr($program->department->hod->name, 0, 1)) }}
                                    </div>
                                    @endif
                                    <span class="text-xs text-slate-600">{{ explode(' ',$program->department->hod->name)[0] }}</span>
                                </div>
                                @else
                                <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($program->syllabus)
                                <a href="{{ asset('storage/'.$program->syllabus) }}" target="_blank"
                                   class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    PDF
                                </a>
                                @else
                                <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($program->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.programs.show', $program) }}" class="rounded-lg bg-slate-100 p-1.5 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.programs.edit', $program) }}" class="rounded-lg bg-violet-50 p-1.5 text-violet-600 hover:bg-violet-100 transition" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.programs.destroy', $program) }}"
                                          onsubmit="return confirm('Delete {{ addslashes($program->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 p-1.5 text-red-500 hover:bg-red-100 transition" title="Delete">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="px-4 py-16 text-center text-sm text-slate-400">No programs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($programs->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $programs->links() }}</div>
            @endif
        </div>
    </div>

    {{-- ── BULK CONFIRM MODAL ── --}}
    <div x-show="confirmBulk" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
         @keydown.escape.window="confirmBulk = false">
        <div class="absolute inset-0 bg-black/50" @click="confirmBulk = false"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
        <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-base font-black text-slate-900">Confirm Bulk Action</h3>
            <p class="mt-2 text-sm text-slate-500">Apply <strong x-text="bulkAction"></strong> to <strong x-text="bulkIds.length"></strong> program(s)?</p>
            <div class="mt-5 flex gap-3">
                <button type="button" @click="confirmBulk = false"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                <button type="button" @click="confirmBulk = false; runBulk()"
                        class="flex-1 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">Confirm</button>
            </div>
        </div>
    </div>

</div>
@endsection
