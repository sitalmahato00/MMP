@extends('layouts.app')
@section('title', 'Bulk Student ID Cards')

@section('content')
<div x-data="bulkIdCards()" class="space-y-5">

    {{-- ── Page Header ───────────────────────────────────── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Bulk Student ID Cards</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Select students, configure card settings, then generate all at once.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.id-cards.students.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Single Generator
            </a>
            <a href="{{ route('admin.id-cards.students.reports') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reports
            </a>
        </div>
    </div>

    {{-- ── Selection status bar ───────────────────────────── --}}
    <div x-show="selectedIds.length > 0" x-transition
         class="flex items-center justify-between rounded-xl border border-[#8B0000]/30 bg-[#8B0000]/5 px-4 py-3">
        <p class="text-sm font-semibold text-[#8B0000]">
            <span x-text="selectedIds.length"></span> student(s) selected
        </p>
        <button @click="selectedIds = []; updateForm();" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 transition">
            Clear selection
        </button>
    </div>

    {{-- ── Filters ─────────────────────────────────────────── --}}
    <form method="GET"
          class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Student ID…"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            <select name="department_id"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
            <select name="program_id"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->name }}</option>
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
                <button type="submit" class="flex-1 rounded-xl bg-[#8B0000] py-2 text-sm font-semibold text-white hover:bg-[#a01010] transition">
                    Filter
                </button>
                <a href="{{ route('admin.id-cards.students.bulk-list') }}"
                   class="flex-1 rounded-xl border border-slate-200 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 transition">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- ── Card settings + Generate ──────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Card Configuration</p>
        <form id="bulk-form" method="POST" action="{{ route('admin.id-cards.students.bulk-pdf') }}" target="_blank">
            @csrf
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <select name="template"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="red">🔴 Template: Red</option>
                    <option value="blue">🔵 Template: Blue</option>
                    <option value="green">🟢 Template: Green</option>
                </select>
                <x-bs-date-picker name="valid_upto" placeholder="Valid Up To (BS)" />
                <x-bs-date-picker name="issue_date" placeholder="Issue Date (BS)" />
                <select name="barcode_type"
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="both">Barcode + QR</option>
                    <option value="barcode">Barcode Only</option>
                    <option value="qr">QR Only</option>
                    <option value="none">None</option>
                </select>
                <button type="submit" :disabled="selectedIds.length === 0"
                    class="rounded-xl bg-[#8B0000] py-2 text-sm font-bold text-white hover:bg-[#a01010] disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
                    🖨️ Print (<span x-text="selectedIds.length">0</span>)
                </button>
            </div>
            <div id="selected-ids-container"></div>
        </form>
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $students->total() }} students found
            </span>
            <div class="flex gap-3">
                <button @click="selectAllPage()" class="text-xs font-medium text-[#8B0000] hover:underline">
                    Select All on Page
                </button>
                <button @click="deselectAll()" class="text-xs font-medium text-slate-500 hover:underline">
                    Deselect All
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" @change="togglePage($event)"
                                :checked="allPageSelected"
                                class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-red-300">
                        </th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Student ID</th>
                        <th class="px-4 py-3">Program</th>
                        <th class="px-4 py-3">Semester</th>
                        <th class="px-4 py-3">Session</th>
                        <th class="px-4 py-3 text-right">Quick Print</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($students as $student)
                    @php $sid = $student->id; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
                        :class="selectedIds.includes({{ $sid }}) ? 'bg-red-50/60 dark:bg-red-900/10' : ''">
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="{{ $sid }}"
                                :checked="selectedIds.includes({{ $sid }})"
                                @change="toggle({{ $sid }})"
                                class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-red-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $student->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=S&background=8B0000&color=fff&size=36' }}"
                                    class="h-8 w-8 rounded-lg object-cover" alt="{{ $student->user?->name }}">
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $student->user?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $student->department?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">
                            {{ $student->student_no ?? '—' }}
                            @if($student->registration_number)
                            <p class="text-slate-400">{{ $student->registration_number }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 text-sm">{{ $student->program?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($student->current_semester)
                            <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                Sem {{ $student->current_semester }}
                            </span>
                            @else—@endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $student->academicSession?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.id-cards.students.single-pdf', $student) }}" target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-[#8B0000] hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            No students found. Try adjusting your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
            {{ $students->links() }}
        </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function bulkIdCards() {
    return {
        selectedIds: [],
        pageIds: @json($students->pluck('id')),

        get allPageSelected() {
            return this.pageIds.length > 0 && this.pageIds.every(id => this.selectedIds.includes(id));
        },

        toggle(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx >= 0) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
            this.updateForm();
        },

        togglePage(e) {
            if (e.target.checked) {
                this.selectAllPage();
            } else {
                this.deselectAll();
            }
        },

        selectAllPage() {
            this.pageIds.forEach(id => {
                if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
            });
            this.updateForm();
        },

        deselectAll() {
            this.selectedIds = this.selectedIds.filter(id => !this.pageIds.includes(id));
            this.updateForm();
        },

        updateForm() {
            const container = document.getElementById('selected-ids-container');
            container.innerHTML = '';
            this.selectedIds.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                container.appendChild(inp);
            });
        },
    };
}
</script>
@endpush
@endsection
