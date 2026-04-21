@extends('layouts.app')

@section('title', 'Exams & Marks')

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
                        Exams & Marks Management
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Monitor exam schedules and student performance</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('hod.exams.create') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Create Assessment Exam</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Exams</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500 opacity-40"></div>
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
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($upcomingExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Upcoming</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500 opacity-40"></div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-50">
                    <svg class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($ongoingExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Ongoing</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-orange-500 opacity-40"></div>
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
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($completedExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Completed</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 opacity-40"></div>
        </div>
    </section>

    {{-- Exams Table --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Department Exams</h2>
            <p class="text-xs text-slate-500">Exam schedules and status for your department</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Exam Name</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Schedule</th>
                        <th class="px-5 py-3 text-left">Programs</th>
                        <th class="px-5 py-3 text-center">Semester</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exams as $exam)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $exam->name }}</div>
                                <div class="text-xs text-slate-500">{{ $exam->academicSession->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ ucfirst($exam->type) }}</div>
                                <div class="text-xs text-slate-500">{{ $exam->category_label }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ bsDate($exam->start_date, 'F d, Y') }}</div>
                                @if($exam->end_date && $exam->end_date != $exam->start_date)
                                    <div class="text-xs text-slate-500">to {{ bsDate($exam->end_date, 'F d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $exam->programs->count() }} programs</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $semesters = $exam->programs->pluck('pivot.semester')->filter()->unique()->sort()->values();
                                @endphp
                                @if($semesters->count() > 0)
                                    <span class="text-sm font-medium text-slate-900">{{ $semesters->implode(', ') }}</span>
                                @else
                                    <span class="text-sm font-medium text-slate-500">All</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'upcoming' => 'bg-blue-50 text-blue-700',
                                        'ongoing' => 'bg-orange-50 text-orange-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'published' => 'bg-green-50 text-green-700',
                                    ];
                                    $statusColor = $statusColors[$exam->status] ?? 'bg-slate-50 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                    {{ $exam->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1 flex-wrap">
                                    {{-- View Marks Button --}}
                                    <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" 
                                       class="inline-flex items-center gap-1 rounded-md bg-violet-50 px-2 py-1 text-xs font-medium text-violet-700 hover:bg-violet-100 transition-colors"
                                       title="View Marks">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>

                                    {{-- Edit Button (only for assessment exams) --}}
                                    @if($exam->category === 'monthly_assessment')
                                        <a href="{{ route('hod.exams.edit', $exam) }}" 
                                           class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 transition-colors"
                                           title="Edit Assessment Exam">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @endif

                                    {{-- Fill Button --}}
                                    <a href="{{ route('hod.exams.fill-marks', ['exam_id' => $exam->id]) }}" 
                                       class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors"
                                       title="Fill Marks">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        Fill
                                    </a>

                                    {{-- Delete Button --}}
                                    <button type="button" 
                                            onclick="confirmDelete({{ $exam->id }}, '{{ $exam->name }}')"
                                            class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors"
                                            title="Delete Exam">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No exams found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exams->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $exams->links() }}
            </div>
        @endif
    </section>
</div>

{{-- Hidden Delete Forms --}}
@foreach($exams as $exam)
    <form id="deleteForm{{ $exam->id }}" method="POST" action="{{ route('hod.exams.destroy', $exam) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="forceDeleteForm{{ $exam->id }}" method="POST" action="{{ route('hod.exams.force-destroy', $exam) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@push('scripts')
<script>
function confirmDelete(examId, examName) {
    // First confirmation
    if (!confirm(`⚠️ WARNING: Are you sure you want to delete "${examName}"?\n\nThis action cannot be undone!`)) {
        return;
    }
    
    // Second confirmation with more details
    if (!confirm(`🚨 FINAL CONFIRMATION 🚨\n\nYou are about to permanently delete:\n"${examName}"\n\nThis will also delete:\n• All student marks for this exam\n• All related data\n\nType "DELETE" in the next prompt to confirm.`)) {
        return;
    }
    
    // Third confirmation requiring typing
    const confirmation = prompt('Type "DELETE" (in capital letters) to confirm deletion:');
    if (confirmation !== 'DELETE') {
        alert('Deletion cancelled. You must type "DELETE" exactly to confirm.');
        return;
    }
    
    // Submit the delete form
    document.getElementById('deleteForm' + examId).submit();
}
</script>
@endpush
@endsection