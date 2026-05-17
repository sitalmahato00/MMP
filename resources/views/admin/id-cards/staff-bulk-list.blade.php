@extends('layouts.app')
@section('title', 'Bulk Staff ID Cards')

@section('content')
<div x-data="bulkIdCards()" class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Bulk Staff ID Cards</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Select staff members and generate ID cards in bulk.</p>
        </div>
        <a href="{{ route('admin.id-cards.staff.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Generator
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Name or Code…"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
            <select name="department" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d }}" @selected(request('department') == $d)>{{ $d }}</option>
                @endforeach
            </select>
            <select name="designation" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                <option value="">All Designations</option>
                @foreach($designations as $d)
                <option value="{{ $d }}" @selected(request('designation') == $d)>{{ $d }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-blue-900 py-2 text-sm font-semibold text-white hover:bg-blue-950 transition">Filter</button>
                <a href="{{ route('admin.id-cards.staff.bulk-list') }}" class="flex-1 rounded-xl border border-slate-200 py-2 text-center text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 transition">Reset</a>
            </div>
        </div>
    </form>

    {{-- Card config + Generate button --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <p class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Card Settings</p>
        <form id="bulk-form" method="POST" action="{{ route('admin.id-cards.staff.bulk-pdf') }}" target="_blank">
            @csrf
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <select name="template" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="blue">Template: Blue</option>
                    <option value="red">Template: Red</option>
                    <option value="green">Template: Green</option>
                </select>
                <input type="text" name="valid_upto" placeholder="Valid Up To (BS)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                <input type="text" name="issue_date" placeholder="Issue Date (BS)" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                <select name="barcode_type" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                    <option value="both">Barcode &amp; QR</option>
                    <option value="barcode">Barcode Only</option>
                    <option value="qr">QR Only</option>
                    <option value="none">None</option>
                </select>
                <button type="submit" :disabled="selectedIds.length === 0"
                    class="rounded-xl bg-blue-900 py-2 text-sm font-bold text-white hover:bg-blue-950 disabled:opacity-40 transition">
                    Generate (<span x-text="selectedIds.length"></span>)
                </button>
            </div>
            <div id="selected-ids-container"></div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                {{ $staff->total() }} staff found
            </span>
            <button @click="toggleAll()" class="text-xs font-medium text-blue-700 hover:underline">
                <span x-text="allSelected ? 'Deselect All' : 'Select All on Page'"></span>
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <th class="px-4 py-3 w-10"></th>
                        <th class="px-4 py-3">Staff</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Designation</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($staff as $member)
                    @php $mid = $member->id; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition" :class="selectedIds.includes({{ $mid }}) ? 'bg-blue-50 dark:bg-blue-900/10' : ''">
                        <td class="px-4 py-3">
                            <input type="checkbox" :value="{{ $mid }}"
                                :checked="selectedIds.includes({{ $mid }})"
                                @change="toggle({{ $mid }})"
                                class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $member->photo_url }}"
                                    class="h-8 w-8 rounded-lg object-cover">
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $member->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $member->employment_type }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $member->staff_code ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $member->designation ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $member->department ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.id-cards.staff.single-pdf', $member) }}" target="_blank"
                               class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">No staff found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
        <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
            {{ $staff->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function bulkIdCards() {
    return {
        selectedIds: [],
        allSelected: false,
        pageIds: @json($staff->pluck('id')),

        toggle(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx >= 0) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
            this.updateForm();
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedIds = this.selectedIds.filter(id => !this.pageIds.includes(id));
            } else {
                this.pageIds.forEach(id => {
                    if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
                });
            }
            this.allSelected = !this.allSelected;
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
