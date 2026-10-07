@extends('layouts.app')

@section('title', $subject->name . ' - Subject Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                    {{ $subject->code }}
                </span>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                    {{ $subject->type === 'theory' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                    {{ $subject->type === 'practical' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : '' }}
                    {{ $subject->type === 'both' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : '' }}">
                    {{ ucfirst($subject->type) }}
                </span>
                @if($subject->is_active)
                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                @else
                    <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Inactive</span>
                @endif
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $subject->name }}</h1>
            <p class="mt-0.5 text-sm text-slate-500">
                {{ $subject->program?->department?->name ?? 'Department' }} &bull; {{ $subject->program?->name ?? 'Program' }} &bull; Semester {{ $subject->semester }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.subjects.edit', $subject) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Subject
            </a>
            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Are you sure you want to delete this subject?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Details & Marking Scheme --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Overview Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Subject Information</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Credit Hours</dt>
                    <dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200">{{ $subject->credit_hours ?? 0 }} Credits</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Full Marks</dt>
                    <dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200">{{ $subject->total_full_marks }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pass Marks</dt>
                    <dd class="mt-0.5 font-bold text-slate-800 dark:text-slate-200">{{ $subject->total_pass_marks }}</dd>
                </div>
                @if($subject->syllabus)
                    <div class="pt-2">
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Syllabus</dt>
                        <dd class="mt-1">
                            <a href="{{ Storage::disk('public')->url($subject->syllabus) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 7.5V19a2 2 0 01-2 2z"/></svg>
                                Download Syllabus (PDF)
                            </a>
                        </dd>
                    </div>
                @endif
                @if($subject->details)
                    <div class="pt-2">
                        <dt class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Description</dt>
                        <dd class="mt-1 text-slate-600 dark:text-slate-300">{{ $subject->details }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Marking Scheme Card --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Marking Scheme Breakdown</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Theory --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/30 p-4 dark:border-blue-900/30 dark:bg-blue-950/20">
                    <h3 class="text-sm font-bold text-blue-900 dark:text-blue-300 mb-3">Theory (Total: {{ $subject->total_theory_marks }})</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between border-b border-blue-100/60 pb-1 dark:border-blue-900/40">
                            <span class="text-slate-500">Internal Full / Pass</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subject->full_marks_internal_theory ?? 0 }} / {{ $subject->pass_marks_internal_theory ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">External Full / Pass</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subject->full_marks_external_theory ?? 0 }} / {{ $subject->pass_marks_external_theory ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                {{-- Practical --}}
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/30 p-4 dark:border-emerald-900/30 dark:bg-emerald-950/20">
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-300 mb-3">Practical (Total: {{ $subject->total_practical_marks }})</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between border-b border-emerald-100/60 pb-1 dark:border-emerald-900/40">
                            <span class="text-slate-500">Internal Full / Pass</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subject->full_marks_internal_practical ?? 0 }} / {{ $subject->pass_marks_internal_practical ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">External Full / Pass</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $subject->full_marks_external_practical ?? 0 }} / {{ $subject->pass_marks_external_practical ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assigned Teachers Section --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Assigned Teachers</h2>
                <p class="text-xs text-slate-500">Current Academic Session: {{ $currentSession?->name ?? 'No active session' }}</p>
            </div>
        </div>

        {{-- Assigned Teachers Table --}}
        @if($assignedTeachers->isNotEmpty())
            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-2.5">Teacher Name</th>
                            <th class="px-4 py-2.5">Department</th>
                            <th class="px-4 py-2.5">Role</th>
                            <th class="px-4 py-2.5">Section</th>
                            <th class="px-4 py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($assignedTeachers as $t)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $t->user?->name ?? 'Unknown Teacher' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $t->department?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $t->pivot->role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ $t->pivot->section ?? 'All Sections' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.subjects.remove-teacher', ['subject' => $subject, 'teacher' => $t]) }}" onsubmit="return confirm('Remove teacher assignment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mb-6 rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500 dark:border-slate-800">
                No teachers assigned to this subject yet.
            </p>
        @endif

        {{-- Assign New Teacher Form --}}
        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-800/40">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Assign Teacher</h3>
            <form method="POST" action="{{ route('admin.subjects.assign-teacher', $subject) }}" class="grid gap-3 sm:grid-cols-4">
                @csrf
                <div class="sm:col-span-2">
                    <select name="teacher_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Select Teacher</option>
                        @foreach($availableTeachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->user?->name }} ({{ $teacher->department?->name ?? 'No Dept' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="role" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="Teacher">Theory Teacher</option>
                        <option value="Lab Technician">Lab Technician</option>
                        <option value="Lecturer">Lecturer</option>
                        <option value="Assistant">Assistant</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <input type="text" name="section" placeholder="Section (Optional)"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 whitespace-nowrap dark:bg-slate-700">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
