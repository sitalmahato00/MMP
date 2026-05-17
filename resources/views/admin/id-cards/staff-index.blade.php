@extends('layouts.app')
@section('title', 'Staff ID Cards')

@section('content')
<div x-data="{
    selected: [],
    allIds: {{ $staff->pluck('id')->toJson() }},
    toggleAll() {
        if (this.selected.length === this.allIds.length) { this.selected = []; }
        else { this.selected = [...this.allIds]; }
    },
    isAllSelected() { return this.selected.length > 0 && this.selected.length === this.allIds.length; },
    generatePdf() {
        if (this.selected.length === 0) return;
        const form = document.getElementById('bulk-pdf-form');
        form.querySelectorAll('input[name=\'ids[]\']').forEach(el => el.remove());
        this.selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    }
}">

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Staff ID Cards</h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Filter, select staff members and generate printable ID card PDFs.</p>
        </div>
        <button
            @click="generatePdf()"
            :disabled="selected.length === 0"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-800 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-900 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M17 9V7a2 2 0 00-2-2H9a2 2 0 00-2 2v2"/>
            </svg>
            Generate PDF
            <span x-show="selected.length > 0" x-text="'(' + selected.length + ')'" class="ml-0.5"></span>
        </button>
    </div>

    {{-- Hidden bulk form --}}
    <form id="bulk-pdf-form" method="POST" action="{{ route('admin.id-cards.staff.bulk-pdf') }}" target="_blank">
        @csrf
    </form>

    {{-- Filters --}}
    <form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name, code or designation…"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
        </div>
        <div>
            <select name="department" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="designation" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                <option value="">All Designations</option>
                @foreach ($designations as $desig)
                    <option value="{{ $desig }}" @selected(request('designation') === $desig)>{{ $desig }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition">
                Filter
            </button>
            @if (request()->anyFilled(['search','department','designation','status']))
                <a href="{{ route('admin.id-cards.staff.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 px-4 py-3 bg-slate-50 dark:bg-slate-800/60">
            <label class="flex items-center gap-2 cursor-pointer select-none text-sm font-medium text-slate-700 dark:text-slate-300">
                <input
                    type="checkbox"
                    @click="toggleAll()"
                    :checked="isAllSelected()"
                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Select all on this page
            </label>
            <span class="text-xs text-slate-400">{{ $staff->total() }} staff member(s) found</span>
        </div>

        @if ($staff->isEmpty())
            <div class="px-6 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 dark:text-slate-400">No active staff found.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Staff Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Employment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Contact</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($staff as $member)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition" :class="selected.includes({{ $member->id }}) ? 'bg-blue-50 dark:bg-blue-900/10' : ''">
                        <td class="px-4 py-3">
                            <input
                                type="checkbox"
                                :value="{{ $member->id }}"
                                x-model="selected"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $member->photo_url }}"
                                    alt=""
                                    class="h-9 w-9 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $member->name }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $member->staff_code ?: '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                            {{ $member->department ?: '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-slate-700 dark:text-slate-300">{{ $member->designation ?: '—' }}</p>
                            <p class="text-xs text-slate-400">{{ ucfirst($member->employment_type ?? '—') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $member->email ?: '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $member->phone ?: '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('admin.id-cards.staff.single-pdf', $member) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($staff->hasPages())
            <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-3">
                {{ $staff->links() }}
            </div>
            @endif
        @endif
    </div>

</div>
@endsection
