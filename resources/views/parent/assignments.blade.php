@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Assignments</h1>
        <p class="text-slate-600 mt-1">View all assignments for your children</p>
    </div>

    @if(count($childrenAssignments) === 0)
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                <i class="fas fa-clipboard-list text-slate-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No Children Found</h3>
            <p class="text-slate-600">No children are enrolled under your account.</p>
        </div>
    @else
        @foreach($childrenAssignments as $childData)
            <!-- Child Section -->
            <div class="mb-8">
                <!-- Child Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-sm p-6 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">
                                    {{ strtoupper(substr($childData['child']->user->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $childData['child']->user->name }}</h2>
                                <p class="text-blue-100 text-sm">
                                    {{ $childData['child']->program->name ?? 'N/A' }} - 
                                    Semester {{ $childData['child']->current_semester }} - 
                                    Section {{ $childData['child']->section }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">{{ $childData['total_assignments'] }}</div>
                                <div class="text-blue-100 text-xs">Total</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-300">{{ $childData['pending_count'] }}</div>
                                <div class="text-blue-100 text-xs">Pending</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-300">{{ $childData['submitted_count'] }}</div>
                                <div class="text-blue-100 text-xs">Submitted</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-300">{{ $childData['graded_count'] }}</div>
                                <div class="text-blue-100 text-xs">Graded</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($childData['assignments_by_subject']->isEmpty())
                    <!-- No Assignments -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-100 rounded-full mb-3">
                            <i class="fas fa-clipboard-check text-slate-400 text-xl"></i>
                        </div>
                        <p class="text-slate-600">No assignments found for this student</p>
                    </div>
                @else
                    <!-- Assignments by Subject -->
                    <div class="space-y-4">
                        @foreach($childData['assignments_by_subject'] as $subjectData)
                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                                <!-- Subject Header -->
                                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-800">
                                                {{ $subjectData['subject']->name }}
                                            </h3>
                                            <p class="text-sm text-slate-600">
                                                {{ $subjectData['subject']->code }}
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                            {{ $subjectData['assignments']->count() }} Assignment{{ $subjectData['assignments']->count() !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Assignments List -->
                                <div class="divide-y divide-slate-200">
                                    @foreach($subjectData['assignments'] as $assignment)
                                        <div class="p-6 hover:bg-slate-50 transition-colors">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-start space-x-3">
                                                        <div class="flex-1">
                                                            <h4 class="text-base font-semibold text-slate-800 mb-1">
                                                                {{ $assignment['title'] }}
                                                            </h4>
                                                            
                                                            @if($assignment['description'])
                                                                <p class="text-sm text-slate-600 mb-3">
                                                                    {{ Str::limit($assignment['description'], 200) }}
                                                                </p>
                                                            @endif

                                                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                                                <div class="flex items-center space-x-2">
                                                                    <i class="fas fa-user-tie text-slate-400"></i>
                                                                    <span>{{ $assignment['teacher_name'] }}</span>
                                                                </div>
                                                                
                                                                <div class="flex items-center space-x-2">
                                                                    <i class="fas fa-calendar text-slate-400"></i>
                                                                    <span>Due: {{ bsDate($assignment['due_date']) }}</span>
                                                                </div>

                                                                @if($assignment['attachment'])
                                                                    <a href="{{ asset('storage/' . ltrim($assignment['attachment'], '/')) }}" 
                                                                       target="_blank"
                                                                       class="flex items-center space-x-2 text-blue-600 hover:text-blue-700">
                                                                        <i class="fas fa-paperclip"></i>
                                                                        <span>View Attachment</span>
                                                                    </a>
                                                                @endif
                                                            </div>

                                                            @if($assignment['submission'])
                                                                <!-- Submission Details -->
                                                                <div class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                                                    <div class="flex items-center justify-between mb-2">
                                                                        <span class="text-sm font-medium text-slate-700">Submission Details</span>
                                                                        @if($assignment['submission']->attachment)
                                                                                <a href="{{ asset('storage/' . ltrim($assignment['submission']->attachment, '/')) }}" 
                                                                               target="_blank"
                                                                               class="text-sm text-blue-600 hover:text-blue-700">
                                                                                <i class="fas fa-download mr-1"></i>
                                                                                Download Submission
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    @if($assignment['submission']->student_note)
                                                                        <p class="text-sm text-slate-600 mb-2">
                                                                            <span class="font-medium">Note:</span> {{ $assignment['submission']->student_note }}
                                                                        </p>
                                                                    @endif

                                                                    @if($assignment['status'] === 'graded')
                                                                        <div class="flex items-center space-x-4 mt-2">
                                                                            <div class="flex items-center space-x-2">
                                                                                <i class="fas fa-star text-yellow-500"></i>
                                                                                <span class="text-sm font-semibold text-slate-800">
                                                                                    Marks: {{ $assignment['marks_obtained'] ?? 'N/A' }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        @if($assignment['submission']->teacher_feedback)
                                                                            <div class="mt-2 p-3 bg-blue-50 rounded border border-blue-200">
                                                                                <p class="text-sm text-slate-700">
                                                                                    <span class="font-medium text-blue-700">Teacher Feedback:</span><br>
                                                                                    {{ $assignment['submission']->teacher_feedback }}
                                                                                </p>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status Badge -->
                                                <div class="ml-4">
                                                    @if($assignment['status'] === 'graded')
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            Graded
                                                        </span>
                                                    @elseif($assignment['status'] === 'submitted')
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
                                                            <i class="fas fa-paper-plane mr-1"></i>
                                                            Submitted
                                                        </span>
                                                    @elseif($assignment['is_overdue'])
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            Overdue
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection
