@extends('layouts.app')

@section('title', 'Exam Marks - ' . $exam->name)

@section('content')
<x-page-header 
    :title="$exam->name" 
    :subtitle="$exam->category_label . ' • ' . bsDate($exam->start_date, 'F d, Y')"
    back="{{ route('hod.exams.index') }}"/>

{{-- KPI Cards --}}
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Marks</p>
        </div>
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
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($pendingMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Pending</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50">
                <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($submittedMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Submitted</p>
        </div>
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
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($approvedMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Approved</p>
        </div>
    </div>
</section>

{{-- Filters --}}
<x-form-section title="Filters & Actions" subtitle="Filter marks and perform bulk verification." class="mb-6">
    <form method="GET" action="{{ route('hod.exams.marks') }}" x-data="{ selectedMarks: [] }">
        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
        
        <x-form-row>
            <x-form-field label="Search Student" name="search">
                <x-input name="search" :value="request('search')" placeholder="Search by name..."/>
            </x-form-field>

            <x-form-field label="Program" name="program_id">
                <x-select name="program_id">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>
                            {{ $prog->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Semester" name="semester">
                <x-select name="semester">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" @selected(request('semester') == $sem)>
                            Semester {{ $sem }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Subject" name="subject_id">
                <x-select name="subject_id">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>
                            {{ $subj->name }} (Sem {{ $subj->semester }})
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Status" name="status">
                <x-select name="status">
                    <option value="">All Status</option>
                    <option value="draft" @selected(request('status') == 'draft')>Draft</option>
                    <option value="submitted" @selected(request('status') == 'submitted')>Submitted</option>
                    <option value="approved" @selected(request('status') == 'approved')>Approved</option>
                    <option value="published" @selected(request('status') == 'published')>Published</option>
                </x-select>
            </x-form-field>
        </x-form-row>

        <div class="flex items-center justify-between mt-4">
            <x-btn type="submit">Apply Filters</x-btn>

            @if(request()->hasAny(['search', 'program_id', 'subject_id', 'status']))
                <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}"
                   class="text-sm text-slate-600 hover:text-slate-900">
                    Clear Filters
                </a>
            @endif
        </div>

        {{-- Bulk Verify Action --}}
        <div x-show="selectedMarks.length > 0" x-cloak class="border-t border-slate-200 pt-4 mt-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-slate-600">
                    <span x-text="selectedMarks.length"></span> mark(s) selected
                </p>
                <button type="button" @click="verifySelected()"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verify Selected
                </button>
            </div>
        </div>
    </form>
</x-form-section>

    {{-- Grouped Marks: Semester → Subject → Students --}}
    @if(request()->hasAny(['search', 'program_id', 'semester', 'subject_id', 'status']))
        <div class="mb-3 flex items-center gap-2 text-sm text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filters active —
            <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" class="text-blue-600 hover:underline">Clear all</a>
        </div>
    @endif

    <div x-data="{ selectedMarks: [] }" class="space-y-6">

    {{-- Bulk verify bar --}}
    <div x-show="selectedMarks.length > 0" x-cloak
         class="sticky top-0 z-30 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 shadow">
        <p class="text-sm font-medium text-emerald-800"><span x-text="selectedMarks.length"></span> mark(s) selected</p>
        <button type="button" @click="verifySelected(selectedMarks)"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verify Selected
        </button>
    </div>

    @forelse($groupedMarks as $semester => $subjectGroups)
    {{-- Semester Accordion --}}
    @php
        $semAllMarks  = $subjectGroups->flatten(1);
        $semStudents  = $semAllMarks->count();
        $semApproved  = $semAllMarks->where('status', 'approved')->count();
        $semSubmitted = $semAllMarks->where('status', 'submitted')->count();
        $semDraft     = $semAllMarks->where('status', 'draft')->count();
        $semSubjects  = $subjectGroups->count();
    @endphp
    <div x-data="{ open: false }">
        <div @click="open = !open" role="button"
             class="w-full flex items-center gap-3 mb-2 cursor-pointer select-none rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm hover:bg-slate-50 transition-colors group">

            {{-- Semester badge --}}
            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-[#8B0000] text-white text-xs font-bold flex-shrink-0">{{ $semester }}</div>

            {{-- Title --}}
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-800 leading-tight">Semester {{ $semester }}</h2>
                <p class="text-[11px] text-slate-400 leading-tight">{{ $semSubjects }} {{ Str::plural('subject', $semSubjects) }}</p>
            </div>

            <div class="flex-1"></div>

            {{-- Aggregate stats --}}
            <div class="flex items-center gap-3 text-xs flex-shrink-0">
                <span class="text-slate-500 font-medium">{{ $semStudents }} {{ Str::plural('student', $semStudents) }}</span>
                <span class="inline-flex items-center gap-1 font-medium text-emerald-600">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $semApproved }} approved
                </span>
                <span class="inline-flex items-center gap-1 font-medium text-blue-600">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    {{ $semSubmitted }} submitted
                </span>
                <span class="inline-flex items-center gap-1 text-slate-400">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    {{ $semDraft }} draft
                </span>
            </div>

            {{-- Chevron --}}
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0 ml-2" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-4 pl-4 border-l-2 border-slate-200">
        @foreach($subjectGroups as $subjectId => $subjectMarks)
        @php $subject = $subjectMarks->first()->subject; @endphp
        <div x-data="{ subOpen: false }" class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            {{-- Subject Header --}}
            <div class="flex items-center bg-slate-50 border-b border-slate-100">
                {{-- Clickable toggle area --}}
                <div @click="subOpen = !subOpen" role="button"
                     class="flex flex-1 items-center justify-between px-5 py-3 hover:bg-slate-100 transition-colors cursor-pointer min-w-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm font-bold text-slate-900">{{ $subject?->name ?? 'N/A' }}</span>
                            <span class="ml-2 text-xs font-mono text-slate-400">{{ $subject?->code }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-500 ml-4 flex-shrink-0">
                        <span>{{ $subjectMarks->count() }} students</span>
                        <span class="text-emerald-600 font-medium">{{ $subjectMarks->where('status', 'approved')->count() }} approved</span>
                        <span class="text-blue-600 font-medium">{{ $subjectMarks->where('status', 'submitted')->count() }} submitted</span>
                        <span class="text-slate-400">{{ $subjectMarks->where('status', 'draft')->count() }} draft</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 ml-1 flex-shrink-0" :class="subOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                {{-- Export Buttons (do NOT toggle accordion) --}}
                <div class="flex items-center gap-1 px-3 border-l border-slate-200 flex-shrink-0">
                    <a href="{{ route('hod.exams.export-marks', array_filter(['exam_id' => $exam->id, 'subject_id' => $subjectId, 'semester' => $semester, 'format' => 'excel'])) }}"
                       title="Export Excel — {{ $subject?->name }}"
                       class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                        {{-- Excel icon --}}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Excel
                    </a>
                    <a href="{{ route('hod.exams.export-marks', array_filter(['exam_id' => $exam->id, 'subject_id' => $subjectId, 'semester' => $semester, 'format' => 'pdf'])) }}"
                       title="Export PDF — {{ $subject?->name }}"
                       class="inline-flex items-center gap-1.5 rounded-md bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800 transition-colors">
                        {{-- PDF icon --}}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF
                    </a>
                </div>
            </div>

            {{-- Students Table --}}
            <div x-show="subOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-2.5 text-left w-8">
                                <input type="checkbox"
                                       @change="$event.target.checked
                                           ? selectedMarks.push(...{{ json_encode($subjectMarks->where('status','submitted')->pluck('id')->values()) }}.filter(id => !selectedMarks.includes(id)))
                                           : selectedMarks = selectedMarks.filter(id => !{{ json_encode($subjectMarks->where('status','submitted')->pluck('id')->values()) }}.includes(id))"
                                       class="rounded border-slate-300 text-[#8B0000] focus:ring-red-500" title="Select all submitted in this subject">
                            </th>
                            <th class="px-4 py-2.5 text-left">Student</th>
                            @if($exam->category === 'monthly_assessment')
                                <th class="px-4 py-2.5 text-center">Marks Obtained<div class="text-[10px] font-normal text-slate-400 normal-case">Full: {{ $exam->assessment_full_marks ?? 100 }} / Pass: {{ $exam->assessment_pass_marks ?? 40 }}</div></th>
                            @else
                                <th class="px-4 py-2.5 text-center">Int. Theory</th>
                                <th class="px-4 py-2.5 text-center">Ext. Theory</th>
                                <th class="px-4 py-2.5 text-center">Int. Practical</th>
                                <th class="px-4 py-2.5 text-center">Ext. Practical</th>
                                <th class="px-4 py-2.5 text-center">Total</th>
                            @endif
                            <th class="px-4 py-2.5 text-center">Result</th>
                            <th class="px-4 py-2.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($subjectMarks as $mark)
                        @php
                            $resultColors = ['Pass'=>'bg-emerald-50 text-emerald-700 border-emerald-200','Fail'=>'bg-red-50 text-red-700 border-red-200','Absent'=>'bg-slate-50 text-slate-600 border-slate-200','Withheld'=>'bg-amber-50 text-amber-700 border-amber-200','Delayed'=>'bg-orange-50 text-orange-700 border-orange-200'];
                            $statusColors = ['draft'=>'bg-slate-100 text-slate-600','submitted'=>'bg-blue-50 text-blue-700','approved'=>'bg-emerald-50 text-emerald-700','published'=>'bg-green-50 text-green-700'];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3">
                                @if($mark->status === 'submitted')
                                    <input type="checkbox" :value="{{ $mark->id }}" x-model="selectedMarks"
                                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-500">
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $mark->student?->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400">{{ $mark->student?->program?->name ?? '' }}</div>
                            </td>
                            @if($exam->category === 'monthly_assessment')
                                <td class="px-4 py-3 text-center">
                                    @if($mark->is_absent) <span class="text-slate-400 text-xs">Absent</span>
                                    @else <span class="font-semibold text-slate-900">{{ number_format($mark->assessment_obtained_marks ?? 0, 1) }}</span>
                                    @endif
                                </td>
                            @else
                                <td class="px-4 py-3 text-center text-slate-700">{{ $mark->is_absent ? '—' : number_format($mark->internal_theory_marks ?? 0, 1) }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $mark->is_absent ? '—' : number_format($mark->external_theory_marks ?? 0, 1) }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $mark->is_absent ? '—' : number_format($mark->internal_practical_marks ?? 0, 1) }}</td>
                                <td class="px-4 py-3 text-center text-slate-700">{{ $mark->is_absent ? '—' : number_format($mark->external_practical_marks ?? 0, 1) }}</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-900">{{ $mark->is_absent ? '—' : number_format($mark->total_marks, 1) }}</td>
                            @endif
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $resultColors[$mark->result_remark] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                    {{ $mark->result_remark }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$mark->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($mark->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        </div>
    </div>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-600">No marks found</p>
            <p class="mt-1 text-xs text-slate-400">Try adjusting your filters</p>
        </div>
    @endforelse

    </div>{{-- /x-data --}}

{{-- Verify Form --}}
<form id="verifyForm" method="POST" action="{{ route('hod.exams.verify-marks') }}" style="display: none;">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
    <div id="verifyMarkIds"></div>
</form>

@push('scripts')
<script>
function verifySelected(selectedMarks) {
    if (!selectedMarks || selectedMarks.length === 0) {
        alert('Please select marks to verify');
        return;
    }

    if (!confirm(`Verify ${selectedMarks.length} mark(s)? This will approve them for admin to publish.`)) {
        return;
    }

    const form = document.getElementById('verifyForm');
    const container = document.getElementById('verifyMarkIds');
    container.innerHTML = '';
    
    selectedMarks.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'mark_ids[]';
        input.value = id;
        container.appendChild(input);
    });

    form.submit();
}
</script>
@endpush
@endsection
