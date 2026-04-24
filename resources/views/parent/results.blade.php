@extends('layouts.app')

@section('title', 'Results')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Exam Results</h1>
        <p class="mt-0.5 text-sm text-slate-500">View published exam results for your children</p>
    </div>

    @if(count($childrenResults) === 0)
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-full mb-4">
                <i class="fas fa-chart-bar text-slate-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No Children Found</h3>
            <p class="text-slate-600">No children are enrolled under your account.</p>
        </div>
    @else
        @foreach($childrenResults as $childData)
            <!-- Child Section -->
            <div class="mb-8">
                <!-- Child Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-sm p-6 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">
                                    {{ strtoupper(substr($childData['child']->user->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $childData['child']->user->name }}</h2>
                                <p class="text-purple-100 text-sm">
                                    {{ $childData['child']->program->name ?? 'N/A' }} - 
                                    Semester {{ $childData['child']->current_semester }} - 
                                    Section {{ $childData['child']->section }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ $childData['total_exams'] }}</div>
                            <div class="text-purple-100 text-xs">Published Results</div>
                        </div>
                    </div>
                </div>

                @if(count($childData['exam_results']) === 0)
                    <!-- No Results -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-100 rounded-full mb-3">
                            <i class="fas fa-file-alt text-slate-400 text-xl"></i>
                        </div>
                        <p class="text-slate-600">No published results found for this student</p>
                    </div>
                @else
                    <!-- Results List -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="divide-y divide-slate-200">
                            @foreach($childData['exam_results'] as $result)
                                <div class="p-6 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="text-lg font-semibold text-slate-900">{{ $result['exam']->name }}</h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $result['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $result['passed'] ? 'Passed' : 'Failed' }}
                                                </span>
                                            </div>
                                            
                                            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600 mb-3">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-calendar text-slate-400"></i>
                                                    <span>{{ bsDate($result['exam']->published_at ?? $result['exam']->created_at) }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-tag text-slate-400"></i>
                                                    <span>{{ $result['exam']->category_label }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-book text-slate-400"></i>
                                                    <span>{{ $result['marks_count'] }} Subject{{ $result['marks_count'] !== 1 ? 's' : '' }}</span>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-6">
                                                <div>
                                                    <p class="text-xs text-slate-500">Total Marks</p>
                                                    <p class="text-lg font-bold text-slate-900">{{ number_format($result['total_obtained'], 2) }} / {{ number_format($result['total_full'], 2) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-slate-500">Percentage</p>
                                                    <p class="text-lg font-bold text-purple-600">{{ $result['percentage'] }}%</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ml-6">
                                            <a href="{{ route('parent.results.show', [$childData['child']->id, $result['exam']->id]) }}" 
                                               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition">
                                                <i class="fas fa-eye mr-2"></i>
                                                View Marksheet
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection
