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

            <x-form-field label="Subject" name="subject_id">
                <x-select name="subject_id">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>
                            {{ $subj->name }}
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

    {{-- Marks Table --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Marks List</h2>
                    <p class="text-xs text-slate-500">View and verify student marks</p>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Export Component --}}
                    <x-export-dropdown 
                        :export-url="route('hod.exams.export-marks')"
                        :query-params="array_merge(request()->query(), ['exam_id' => $exam->id])"
                        :formats="['csv', 'excel', 'pdf']"
                        button-text="Export Marks" />
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">
                            <input type="checkbox" @change="toggleAll($event)" 
                                   class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        @if($exam->category === 'monthly_assessment')
                            <th class="px-5 py-3 text-center">
                                <div>Marks Obtained</div>
                                <div class="text-[10px] text-slate-400 normal-case">Full: {{ $exam->assessment_full_marks ?? 100 }} / Pass: {{ $exam->assessment_pass_marks ?? 40 }}</div>
                            </th>
                        @else
                            <th class="px-5 py-3 text-center">
                                <div>Internal Theory</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $firstMark = $marks->first();
                                        $fullIntTheory = $firstMark?->ctevt_full_marks_internal_theory ?? 0;
                                        $passIntTheory = $firstMark?->ctevt_pass_marks_internal_theory ?? 0;
                                    @endphp
                                    Full: {{ $fullIntTheory }} / Pass: {{ $passIntTheory }}
                                </div>
                            </th>
                            <th class="px-5 py-3 text-center">
                                <div>External Theory</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullExtTheory = $firstMark?->ctevt_full_marks_external_theory ?? 0;
                                        $passExtTheory = $firstMark?->ctevt_pass_marks_external_theory ?? 0;
                                    @endphp
                                    Full: {{ $fullExtTheory }} / Pass: {{ $passExtTheory }}
                                </div>
                            </th>
                            <th class="px-5 py-3 text-center">
                                <div>Internal Practical</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullIntPractical = $firstMark?->ctevt_full_marks_internal_practical ?? 0;
                                        $passIntPractical = $firstMark?->ctevt_pass_marks_internal_practical ?? 0;
                                    @endphp
                                    Full: {{ $fullIntPractical }} / Pass: {{ $passIntPractical }}
                                </div>
                            </th>
                            <th class="px-5 py-3 text-center">
                                <div>External Practical</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullExtPractical = $firstMark?->ctevt_full_marks_external_practical ?? 0;
                                        $passExtPractical = $firstMark?->ctevt_pass_marks_external_practical ?? 0;
                                    @endphp
                                    Full: {{ $fullExtPractical }} / Pass: {{ $passExtPractical }}
                                </div>
                            </th>
                            <th class="px-5 py-3 text-center">Total</th>
                        @endif
                        <th class="px-5 py-3 text-left">Result</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" x-data="{ selectedMarks: [] }">
                    @forelse($marks as $mark)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                @if($mark->status === 'submitted')
                                    <input type="checkbox" value="{{ $mark->id }}" 
                                           x-model="selectedMarks"
                                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $mark->student->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $mark->student->program->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $mark->subject->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $mark->subject->code ?? 'N/A' }}</div>
                            </td>
                            @if($exam->category === 'monthly_assessment')
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-sm text-slate-500">Absent</span>
                                    @else
                                        <div class="text-sm font-medium text-slate-900">{{ number_format($mark->assessment_obtained_marks ?? 0, 1) }}</div>
                                        <div class="text-xs text-slate-500">/ {{ $exam->assessment_full_marks ?? 100 }}</div>
                                    @endif
                                </td>
                            @else
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-xs text-slate-500">-</span>
                                    @else
                                        <span class="text-sm font-medium text-slate-900">{{ number_format($mark->internal_theory_marks ?? 0, 1) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-xs text-slate-500">-</span>
                                    @else
                                        <span class="text-sm font-medium text-slate-900">{{ number_format($mark->external_theory_marks ?? 0, 1) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-xs text-slate-500">-</span>
                                    @else
                                        <span class="text-sm font-medium text-slate-900">{{ number_format($mark->internal_practical_marks ?? 0, 1) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-xs text-slate-500">-</span>
                                    @else
                                        <span class="text-sm font-medium text-slate-900">{{ number_format($mark->external_practical_marks ?? 0, 1) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($mark->is_absent)
                                        <span class="text-xs text-slate-500">-</span>
                                    @else
                                        <div class="text-sm font-bold text-slate-900">{{ number_format($mark->total_marks, 1) }}</div>
                                    @endif
                                </td>
                            @endif
                            <td class="px-5 py-4">
                                @php
                                    $resultColors = [
                                        'Pass' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Fail' => 'bg-red-50 text-red-700 border-red-200',
                                        'Absent' => 'bg-slate-50 text-slate-700 border-slate-200',
                                        'Withheld' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Delayed' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    ];
                                    $resultColor = $resultColors[$mark->result_remark] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $resultColor }}">
                                    {{ $mark->result_remark }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-slate-50 text-slate-700',
                                        'submitted' => 'bg-blue-50 text-blue-700',
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'published' => 'bg-green-50 text-green-700',
                                    ];
                                    $statusColor = $statusColors[$mark->status] ?? 'bg-slate-50 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($mark->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No marks found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($marks->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $marks->links() }}
            </div>
        @endif
    </section>
</div>

{{-- Verify Form --}}
<form id="verifyForm" method="POST" action="{{ route('hod.exams.verify-marks') }}" style="display: none;">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
    <div id="verifyMarkIds"></div>
</form>

@push('scripts')
<script>
function verifySelected() {
    const selectedMarks = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
        .filter(cb => cb.value)
        .map(cb => cb.value);
    
    if (selectedMarks.length === 0) {
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

function toggleAll(event) {
    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = event.target.checked);
}
</script>
@endpush
@endsection
