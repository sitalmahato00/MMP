@extends('layouts.app')

@section('title', 'Subject Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Subject Details" 
        :subtitle="$subject->code . ' - ' . $subject->name"
        back="{{ route('hod.subjects.index') }}">
        <a href="{{ route('hod.subjects.edit', $subject) }}" 
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Subject
        </a>
    </x-page-header>

    {{-- Subject Information --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Subject Information</h2>
        </div>
        
        <div class="p-5">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Subject Name</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Subject Code</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->code }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Program</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->program->name }}</p>
                    <p class="text-xs text-slate-500">{{ $subject->program->code }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Semester</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Semester {{ $subject->semester }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Type</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 capitalize">{{ $subject->type }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Credit Hours</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->credit_hours ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Status</p>
                    <p class="mt-1">
                        @if($subject->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Marking Scheme --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">CTEVT Marking Scheme</h2>
        </div>
        
        <div class="p-5">
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Theory Component --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Theory Component</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">Internal Theory:</span>
                            <span class="font-medium text-slate-900">
                                {{ $subject->pass_marks_internal_theory ?? 0 }}/{{ $subject->full_marks_internal_theory ?? 0 }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">External Theory:</span>
                            <span class="font-medium text-slate-900">
                                {{ $subject->pass_marks_external_theory ?? 0 }}/{{ $subject->full_marks_external_theory ?? 0 }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold border-t border-slate-200 pt-2">
                            <span class="text-slate-900">Total Theory:</span>
                            <span class="text-slate-900">
                                {{ ($subject->pass_marks_internal_theory ?? 0) + ($subject->pass_marks_external_theory ?? 0) }}/{{ $subject->total_theory_marks }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Practical Component --}}
                <div class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Practical Component</h3>
                    @if($subject->hasPractical())
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Internal Practical:</span>
                                <span class="font-medium text-slate-900">
                                    {{ $subject->pass_marks_internal_practical ?? 0 }}/{{ $subject->full_marks_internal_practical ?? 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">External Practical:</span>
                                <span class="font-medium text-slate-900">
                                    {{ $subject->pass_marks_external_practical ?? 0 }}/{{ $subject->full_marks_external_practical ?? 0 }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm font-semibold border-t border-slate-200 pt-2">
                                <span class="text-slate-900">Total Practical:</span>
                                <span class="text-slate-900">
                                    {{ ($subject->pass_marks_internal_practical ?? 0) + ($subject->pass_marks_external_practical ?? 0) }}/{{ $subject->total_practical_marks }}
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No practical component</p>
                    @endif
                </div>
            </div>

            {{-- Grand Total --}}
            <div class="mt-4 rounded-lg bg-blue-50 p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-blue-900">Grand Total Marks:</span>
                    <span class="text-lg font-bold text-blue-900">
                        {{ $subject->total_pass_marks }}/{{ $subject->total_full_marks }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Teacher Assignment --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Assigned Teachers</h2>
                    <p class="text-xs text-slate-500">
                        @if($currentSession)
                            Academic Session: {{ $currentSession->name }}
                        @else
                            No active academic session
                        @endif
                    </p>
                </div>
                @if($currentSession)
                    <button type="button" 
                            onclick="document.getElementById('assignTeacherForm').classList.toggle('hidden')"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Assign Teacher
                    </button>
                @endif
            </div>
        </div>

        {{-- Assign Teacher Form --}}
        @if($currentSession)
            <div id="assignTeacherForm" class="hidden border-b border-slate-100 bg-slate-50 p-5">
                <form method="POST" action="{{ route('hod.subjects.assign-teacher', $subject) }}">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teacher *</label>
                            <select name="teacher_id" required class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="">Select Teacher</option>
                                @foreach($availableTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->user->name }} ({{ $teacher->employee_id ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Role *</label>
                            <input type="text" name="role" required placeholder="e.g., Theory Teacher, Lab Tech" 
                                   class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Section (Optional)</label>
                            <input type="text" name="section" placeholder="e.g., A, B, Morning" 
                                   class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Assign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- Assigned Teachers List --}}
        <div class="p-5">
            @if($assignedTeachers->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No teachers assigned yet</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($assignedTeachers as $teacher)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $teacher->user->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $teacher->employee_id ?? 'N/A' }}
                                        @if($teacher->pivot->role)
                                            • <span class="font-medium text-blue-600">{{ $teacher->pivot->role }}</span>
                                        @endif
                                        @if($teacher->pivot->section)
                                            • Section: {{ $teacher->pivot->section }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('hod.subjects.remove-teacher', [$subject, $teacher]) }}" 
                                  onsubmit="return confirm('Remove this teacher assignment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
