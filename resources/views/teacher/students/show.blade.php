@extends('layouts.app')

@section('title', $student->user->name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-cyan-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ $student->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->user->name) }}" 
                        alt="{{ $student->user->name }}" class="h-16 w-16 rounded-full border-4 border-white shadow-md">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Student Profile</p>
                        <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                            {{ $student->user->name }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">{{ $student->program->name }} - {{ $student->student_no }}</p>
                    </div>
                </div>
                <a href="{{ route('teacher.students.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card 
            title="Attendance Records" 
            value="{{ $attendanceSummary->get('present')?->count ?? 0 }}" 
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            tone="emerald"
        />
        <x-stat-card 
            title="Absent" 
            value="{{ $attendanceSummary->get('absent')?->count ?? 0 }}" 
            icon="M6 18L18 6M6 6l12 12"
            tone="rose"
        />
        <x-stat-card 
            title="Marks Recorded" 
            value="{{ $marksSummary->count() }}" 
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            tone="blue"
        />
    </div>

    {{-- Student Info --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Contact Information --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Contact Information</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Email</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $student->user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Student Number</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $student->student_no }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Roll Number</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $student->roll_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Program</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $student->program->name }}</p>
                </div>
            </div>
        </div>

        {{-- Parents/Guardians --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Parents/Guardians</h2>
            @if($student->parents->isEmpty())
                <p class="text-sm text-slate-500">No parents/guardians registered</p>
            @else
                <div class="space-y-3">
                    @foreach($student->parents as $parent)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <p class="font-semibold text-slate-900">{{ $parent->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $parent->user->email }}</p>
                            <p class="text-xs text-slate-500">{{ $parent->phone ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Marks --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">Recent Marks</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Subject</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Theory</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Practical</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Total</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($marksSummary as $mark)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $mark->subject->name }}</p>
                                <p class="text-xs text-slate-500">{{ $mark->subject->code }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $mark->internal_theory_marks ?? 0 }} + {{ $mark->external_theory_marks ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $mark->internal_practical_marks ?? 0 }} + {{ $mark->external_practical_marks ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    {{ $mark->total_marks }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-slate-50 text-slate-700',
                                        'submitted' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-blue-50 text-blue-700',
                                        'published' => 'bg-emerald-50 text-emerald-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$mark->status] ?? 'bg-slate-50 text-slate-700' }} capitalize">
                                    {{ $mark->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center">
                                <p class="text-sm text-slate-500">No marks recorded yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
