@extends('layouts.app')

@section('title', $assignment->title . ' - Assignment Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                    {{ $assignment->subject?->code }}
                </span>
                <span class="text-xs text-slate-500">
                    {{ $assignment->program?->name }} &bull; Sem {{ $assignment->semester }}
                    @if($assignment->section) (Section {{ $assignment->section }}) @endif
                </span>
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $assignment->title }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">
                Assigned by: <strong class="text-slate-700 dark:text-slate-300">{{ $assignment->teacher?->user?->name ?? 'Teacher' }}</strong> &bull;
                Due Date (BS): <strong class="{{ $assignment->due_date && $assignment->due_date->isPast() ? 'text-rose-600' : 'text-slate-700 dark:text-slate-300' }}">{{ $assignment->due_date ? bsDate($assignment->due_date, 'F d, Y') . ' (' . bsDate($assignment->due_date) . ' BS)' : '—' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.assignments.edit', $assignment) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Assignment
            </a>
            <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this assignment?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Details Card --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2">Instructions</h2>
        <div class="prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-300">
            {!! nl2br(e($assignment->description ?? 'No description provided.')) !!}
        </div>
        @if($assignment->attachment)
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($assignment->attachment) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 7.5V19a2 2 0 01-2 2z"/></svg>
                    Download Assignment Attachment
                </a>
            </div>
        @endif
    </div>

    {{-- Student Submissions Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Student Submissions</h2>
                <p class="text-xs text-slate-500">Total {{ $assignment->submissions->count() }} submissions received.</p>
            </div>
        </div>

        @if($assignment->submissions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3">Student Name</th>
                            <th class="px-4 py-3">Roll / ID</th>
                            <th class="px-4 py-3">Submitted At</th>
                            <th class="px-4 py-3">File</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Marks</th>
                            <th class="px-4 py-3 text-right">Grade / Evaluate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($assignment->submissions as $sub)
                            <tr x-data="{ editing: false }">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $sub->student?->user?->name ?? 'Unknown Student' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">
                                    {{ $sub->student?->roll_number ?? $sub->student?->student_no ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $sub->created_at ? bsDateTime($sub->created_at) : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($sub->attachment)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sub->attachment) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                                            📎 View File
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Text only</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        {{ $sub->status === 'graded' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : '' }}
                                        {{ $sub->status === 'submitted' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                        {{ $sub->status === 'resubmit' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : '' }}">
                                        {{ ucfirst($sub->status ?? 'submitted') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $sub->marks_obtained !== null ? $sub->marks_obtained : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" @click="editing = !editing"
                                            class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">
                                        <span x-show="!editing">Grade</span>
                                        <span x-show="editing">Close</span>
                                    </button>

                                    {{-- Quick grading popover / row --}}
                                    <div x-show="editing" x-cloak class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-left dark:bg-slate-800 dark:border-slate-700">
                                        <form method="POST" action="{{ route('admin.assignments.submissions.grade', ['assignment' => $assignment, 'submission' => $sub]) }}" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Marks</label>
                                                    <input type="number" step="0.5" name="marks_obtained" value="{{ $sub->marks_obtained }}" placeholder="Marks"
                                                           class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-xs dark:bg-slate-700 dark:border-slate-600">
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-bold text-slate-500 uppercase">Status</label>
                                                    <select name="status" class="w-full rounded-lg border border-slate-200 px-2 py-1 text-xs dark:bg-slate-700 dark:border-slate-600">
                                                        <option value="graded" {{ $sub->status === 'graded' ? 'selected' : '' }}>Graded</option>
                                                        <option value="submitted" {{ $sub->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                        <option value="resubmit" {{ $sub->status === 'resubmit' ? 'selected' : '' }}>Resubmit</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-500 uppercase">Feedback</label>
                                                <input type="text" name="teacher_feedback" value="{{ $sub->teacher_feedback }}" placeholder="Feedback notes..."
                                                       class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-xs dark:bg-slate-700 dark:border-slate-600">
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-bold text-white hover:bg-emerald-700">
                                                    Save Grade
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500 dark:border-slate-800">
                No students have submitted this assignment yet.
            </p>
        @endif
    </div>
</div>
@endsection
