@extends('layouts.app')

@section('title', 'Results')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Exam Results</h1>
        <p class="mt-0.5 text-sm text-slate-500">View published assessment results for your children</p>
    </div>

    @if(count($childrenResults) === 0)
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <i class="fas fa-chart-bar text-2xl text-slate-400"></i>
            </div>
            <h3 class="mb-2 text-lg font-semibold text-slate-800">No Children Found</h3>
            <p class="text-slate-600">No children are enrolled under your account.</p>
        </div>
    @else
        @foreach($childrenResults as $childData)
            <div class="mb-8">
                <div class="mb-4 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20">
                                <span class="text-2xl font-bold text-white">
                                    {{ strtoupper(substr($childData['child']->user->name, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $childData['child']->user->name }}</h2>
                                <p class="text-sm text-purple-100">
                                    {{ $childData['child']->program->name ?? 'N/A' }} -
                                    Semester {{ $childData['child']->current_semester }} -
                                    Section {{ $childData['child']->section }}
                                </p>
                            </div>
                        </div>

                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">{{ $childData['total_assessments'] }}</div>
                            <div class="text-xs text-purple-100">Published Assessments</div>
                        </div>
                    </div>
                </div>

                @if($childData['assessment_results']->isEmpty())
                    <div class="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                            <i class="fas fa-file-alt text-xl text-slate-400"></i>
                        </div>
                        <p class="text-slate-600">No published results found for this student</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="divide-y divide-slate-200">
                            @foreach($childData['assessment_results'] as $result)
                                <div class="p-6 transition-colors hover:bg-slate-50">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="flex-1">
                                            <div class="mb-2 flex flex-wrap items-center gap-3">
                                                <h3 class="text-lg font-semibold text-slate-900">{{ $result['exam']->name }}</h3>
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $result['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    {{ $result['passed'] ? 'Passed' : 'Failed' }}
                                                </span>
                                            </div>

                                            <div class="mb-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
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
                                                    <span>{{ $result['marks_count'] }} Subject{{ $result['marks_count'] === 1 ? '' : 's' }}</span>
                                                </div>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-6">
                                                <div>
                                                    <p class="text-xs text-slate-500">Total Marks</p>
                                                    <p class="text-lg font-bold text-slate-900">{{ number_format($result['total_obtained'], 2) }} / {{ number_format($result['total_full'], 2) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-slate-500">Percentage Rate</p>
                                                    <p class="text-lg font-bold text-purple-600">{{ number_format($result['percentage'], 1) }}%</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="lg:ml-6">
                                            <a href="{{ route('parent.results.show', [$childData['child']->id, $result['exam']->id]) }}"
                                               class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
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
