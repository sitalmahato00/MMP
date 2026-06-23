@extends('layouts.app')

@section('title', $assignment->title)

@section('content')
<div class="space-y-6">
    {{-- Back link --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('student.assignments.index') }}" 
           class="inline-flex items-center gap-1 text-sm text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Assignments
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Assignment Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Description --}}
            <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Assignment Description</h2>
                <div class="prose prose-sm max-w-none text-slate-600 dark:text-slate-400">
                    {!! nl2br(e($assignment->description)) !!}
                </div>
            </section>

            {{-- Assignment File --}}
            @if($assignment->attachment)
            <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Assignment File</h2>
                <a href="{{ asset('storage/' . ltrim($assignment->attachment, '/')) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Assignment File
                </a>
            </section>
            @endif

            {{-- Submission Form --}}
            @if(!$submission || ($submission && $submission->marks_obtained === null))
            <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                    {{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                </h2>
                
                @if($assignment->due_date < now())
                    <div class="mb-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                        <div class="flex items-start gap-2">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="font-medium">This assignment is overdue</p>
                                <p class="mt-1">Late submissions may receive reduced marks.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('student.assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Student Note</label>
                        <textarea name="student_note" rows="6" 
                                  class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Enter your submission note here...">{{ $submission->student_note ?? '' }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Upload File (Optional)</label>
                        <input type="file" name="attachment" 
                               class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Maximum file size: 10MB</p>
                    </div>

                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                    </button>
                </form>
            </section>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Submission Status --}}
            <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4">Submission Status</h2>
                
                @if($submission)
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-600 dark:text-slate-400">Status</span>
                            @if($submission->marks_obtained !== null)
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">
                                    Graded
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700">
                                    Submitted
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-600 dark:text-slate-400">Submitted At</span>
                            <span class="text-xs font-medium text-slate-900 dark:text-slate-100">{{ bsDate($submission->created_at, 'F d, Y') }}</span>
                        </div>

                        @if($submission->marks_obtained !== null)
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-600 dark:text-slate-400">Marks Obtained</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $submission->marks_obtained }}</span>
                            </div>
                        @endif

                        @if($submission->teacher_feedback)
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#1e3a5f]">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Teacher Feedback</span>
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $submission->teacher_feedback }}</p>
                            </div>
                        @endif

                        @if($submission->attachment)
                            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#1e3a5f]">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Your Submission File</span>
                                <a href="{{ asset('storage/' . ltrim($submission->attachment, '/')) }}" 
                                   target="_blank"
                                   class="mt-2 inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Your File
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Not submitted yet</p>
                    </div>
                @endif
            </section>

            {{-- Assignment Info --}}
            <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-4">Assignment Info</h2>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-600 dark:text-slate-400">Subject</span>
                        <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $assignment->subject->name }}</p>
                    </div>
                    
                    <div>
                        <span class="text-xs text-slate-600 dark:text-slate-400">Teacher</span>
                        <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $assignment->teacher->user->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-xs text-slate-600 dark:text-slate-400">Due Date</span>
                        <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">{{ bsDate($assignment->due_date, 'F d, Y') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
