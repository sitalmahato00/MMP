@extends('layouts.app')

@section('title', 'Alumni Preparation')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $department->name }} Department</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Alumni Preparation
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Prepare graduating students for alumni status</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('hod.alumni.graduating') }}" 
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        View All Graduating
                    </a>
                    <a href="{{ route('hod.alumni.records') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                        Alumni Records
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalGraduating) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Graduating Students</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500 opacity-40"></div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalPrepared) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Prepared Alumni</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 opacity-40"></div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($pendingPreparation) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Pending Preparation</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500 opacity-40"></div>
        </div>
    </section>

    {{-- Graduating Students --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Graduating Students</h2>
            <p class="text-xs text-slate-500">Students ready for alumni preparation</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Program</th>
                        <th class="px-5 py-3 text-left">Semester</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($graduatingStudents as $student)
                        @php
                            $programDuration = $student->program->duration ?? 4;
                            $isGraduating = $student->semester >= $programDuration;
                            $hasAlumniRecord = \App\Models\Alumni::where('student_id', $student->id)->exists();
                        @endphp
                        @if($isGraduating)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student->user->avatar_url }}" alt="{{ $student->user->name }}" 
                                             class="h-8 w-8 rounded-full object-cover">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-slate-900">{{ $student->program->name }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-slate-900">Semester {{ $student->semester }}</div>
                                    <div class="text-xs text-slate-500">Final semester</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($hasAlumniRecord)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                            Alumni Prepared
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                            Ready for Preparation
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if(!$hasAlumniRecord)
                                        <form method="POST" action="{{ route('hod.alumni.prepare', $student) }}" 
                                              onsubmit="return confirm('Are you sure you want to prepare this student for alumni status?')">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Prepare Alumni
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-sm text-slate-400">Already prepared</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No graduating students found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Recently Prepared Alumni --}}
    @if($preparedAlumni->count() > 0)
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recently Prepared Alumni</h2>
                <p class="text-xs text-slate-500">Latest students prepared for alumni status</p>
            </div>
            
            <div class="p-5">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($preparedAlumni as $alumni)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $alumni->student->user->avatar_url }}" alt="{{ $alumni->student->user->name }}" 
                                     class="h-10 w-10 rounded-full object-cover">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-slate-900">{{ $alumni->student->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $alumni->student->program->name }}</div>
                                </div>
                            </div>
                            <div class="mt-3 text-xs text-slate-500">
                                Prepared on {{ bsDate($alumni->created_at, 'M d, Y') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
@endsection