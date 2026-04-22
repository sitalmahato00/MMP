@extends('layouts.app')

@section('title', $subject->name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-violet-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Class Details</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $subject->name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $subject->program->name }} - Semester {{ $subject->semester }}</p>
                </div>
                <a href="{{ route('teacher.classes.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Tabs --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100">
            <div class="flex gap-4 px-4 sm:px-6 overflow-x-auto">
                <button class="border-b-2 border-blue-600 px-4 py-3 text-sm font-semibold text-blue-600 transition" onclick="showTab('students')">
                    Students
                </button>
                <button class="border-b-2 border-transparent px-4 py-3 text-sm font-semibold text-slate-600 transition hover:text-slate-900" onclick="showTab('timetable')">
                    Timetable
                </button>
            </div>
        </div>

        {{-- Students Tab --}}
        <div id="students-tab" class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Student No.</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-700">Attendance</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($student->user->name) }}" 
                                            alt="{{ $student->user->name }}" class="h-8 w-8 rounded-full">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $student->user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $student->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $student->student_no }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $attendanceCount = $student->attendances->count();
                                        $presentCount = $student->attendances->where('status', 'present')->count();
                                        $percentage = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100) : 0;
                                    @endphp
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                        {{ $percentage }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('teacher.students.show', $student) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <p class="text-sm text-slate-500">No students enrolled</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

        {{-- Timetable Tab --}}
        <div id="timetable-tab" class="hidden p-4 sm:p-6">
            @if($slots->isEmpty())
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No timetable slots scheduled</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($slots->groupBy('day_of_week') as $day => $daySlots)
                        <div>
                            <h3 class="mb-2 font-semibold text-slate-900 capitalize">{{ $day }}</h3>
                            <div class="space-y-2">
                                @foreach($daySlots as $slot)
                                    <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
                                        <div class="flex flex-col items-center justify-center rounded-lg bg-blue-50 px-3 py-2">
                                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</span>
                                            <span class="text-[10px] text-blue-500">to</span>
                                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900">{{ $slot->subject->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $slot->room ?? 'Room TBA' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.getElementById('students-tab').classList.add('hidden');
    document.getElementById('timetable-tab').classList.add('hidden');
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Update button styles
    document.querySelectorAll('button').forEach(btn => {
        btn.classList.remove('border-blue-600', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-slate-600');
    });
    event.target.classList.remove('border-transparent', 'text-slate-600');
    event.target.classList.add('border-blue-600', 'text-blue-600');
}
</script>
@endsection
