@extends('layouts.app')

@section('title', 'Marksheet - ' . $student->user->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Back Button -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ route('parent.results.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Results
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition">
            <i class="fas fa-print mr-2"></i>
            Print Marksheet
        </button>
    </div>

    <!-- Marksheet Card -->
    <div class="bg-white rounded-xl shadow-lg border-2 border-slate-200 overflow-hidden print:shadow-none print:border print:rounded-none print:mt-0">
        <!-- Header with College Info -->
        <div class="border-b-4 border-slate-900 bg-gradient-to-r from-slate-50 to-white px-8 py-6 print:bg-white">
            <div class="flex items-start gap-6">
                <!-- College Logo -->
                <div class="flex-shrink-0">
                    <img src="{{ route('public.brand-logo') }}?v={{ logoVersion() }}" 
                         alt="College Logo" 
                         class="w-20 h-20 object-contain rounded-lg border-2 border-slate-200"
                         onerror="this.style.display='none'">
                </div>
                
                <!-- College Details -->
                <div class="flex-1 text-center">
                    <h1 class="text-2xl font-black text-slate-900 uppercase tracking-wide">
                        {{ \App\Models\SiteSetting::where('key', 'site_name')->value('value') ?? 'Manmohan Memorial Polytechnic' }}
                    </h1>
                    <p class="text-sm text-slate-600 mt-1">{{ $student->department->name ?? 'N/A' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $student->program->name ?? 'N/A' }}</p>
                    <div class="mt-3 pt-3 border-t border-slate-200">
                        <h2 class="text-lg font-bold text-purple-700 uppercase">{{ $exam->name }}</h2>
                        <p class="text-xs text-slate-600 mt-1">{{ $exam->category_label }} - {{ bsDate($exam->published_at ?? $exam->created_at) }}</p>
                    </div>
                </div>
                
                <!-- Result Status Badge -->
                <div class="flex-shrink-0">
                    <div class="text-right">
                        <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold {{ $allPassed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            <i class="fas {{ $allPassed ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                            {{ $allPassed ? 'PASSED' : 'FAILED' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="bg-slate-50 border-b border-slate-200 px-8 py-4 print:bg-gray-50">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Student Name</p>
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $student->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Roll No.</p>
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $student->roll_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Semester</p>
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $student->current_semester }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Section</p>
                    <p class="text-sm font-bold text-slate-900 mt-1">{{ $student->section }}</p>
                </div>
            </div>
        </div>

        <!-- Marks Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="px-4 py-3 text-left font-bold uppercase text-xs">Subject</th>
                        @if($exam->category === 'monthly_assessment')
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Full</th>
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Pass</th>
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Obtained</th>
                        @else
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Full</th>
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Pass</th>
                            <th class="px-4 py-3 text-center font-bold uppercase text-xs">Obtained</th>
                        @endif
                        <th class="px-4 py-3 text-center font-bold uppercase text-xs">Attendance</th>
                        <th class="px-4 py-3 text-center font-bold uppercase text-xs">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($marksData as $data)
                        @php
                            $mark = $data['mark'];
                            $scheme = $data['scheme'];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors print:hover:bg-transparent">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $mark->subject->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $mark->subject->code }}</p>
                                </div>
                            </td>
                            
                            @if($exam->category === 'monthly_assessment')
                                <td class="px-4 py-3 text-center font-semibold text-slate-900">
                                    {{ number_format($mark->assessment_full_marks ?? 0, 0) }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-600">
                                    {{ number_format($mark->assessment_pass_marks ?? 0, 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-lg {{ $mark->is_passed ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $mark->is_absent ? 'Ab' : number_format($mark->assessment_obtained_marks ?? 0, 2) }}
                                    </span>
                                </td>
                            @else
                                <td class="px-4 py-3 text-center font-semibold text-slate-900">
                                    {{ number_format($data['full_marks'], 0) }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-slate-600">
                                    {{ number_format(
                                        ($scheme['pass_marks_internal_theory'] ?? 0) +
                                        ($scheme['pass_marks_external_theory'] ?? 0) +
                                        ($scheme['pass_marks_internal_practical'] ?? 0) +
                                        ($scheme['pass_marks_external_practical'] ?? 0),
                                        0
                                    ) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-lg {{ $mark->is_passed ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $mark->is_absent ? 'Ab' : number_format($mark->total_marks, 2) }}
                                    </span>
                                </td>
                            @endif
                            
                            <td class="px-4 py-3 text-center">
                                @if($mark->assessment_attendance_percent !== null)
                                    <span class="text-sm font-semibold {{ $mark->assessment_attendance_percent >= 80 ? 'text-green-600' : 'text-amber-600' }}">
                                        {{ number_format($mark->assessment_attendance_percent, 0) }}%
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">N/A</span>
                                @endif
                            </td>
                            
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $mark->is_absent ? 'bg-gray-100 text-gray-700' : 
                                       ($mark->is_passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $mark->result_remark }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    
                    <!-- Total Row -->
                    <tr class="bg-slate-100 font-bold print:bg-gray-100">
                        <td class="px-4 py-4 text-right uppercase text-slate-900">Total</td>
                        <td class="px-4 py-4 text-center text-slate-900">{{ number_format($totalFull, 0) }}</td>
                        <td class="px-4 py-4 text-center text-slate-600">-</td>
                        <td class="px-4 py-4 text-center">
                            <span class="text-lg {{ $allPassed ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($totalObtained, 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center text-slate-600">-</td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                {{ $allPassed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $allPassed ? 'PASS' : 'FAIL' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Section -->
        <div class="bg-slate-50 border-t-2 border-slate-200 px-8 py-6 print:bg-gray-50">
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Marks</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalObtained, 2) }} / {{ number_format($totalFull, 0) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Percentage</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ $percentage }}%</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Result</p>
                    <p class="text-2xl font-bold {{ $allPassed ? 'text-green-600' : 'text-red-600' }} mt-1">{{ $allPassed ? 'PASSED' : 'FAILED' }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-slate-200 px-8 py-6">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs text-slate-500">Prepared By:</p>
                    <div class="mt-8 border-t border-slate-300 pt-1">
                        <p class="text-xs font-semibold text-slate-700">Examination Department</p>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-xs text-slate-500">Head of Department</p>
                    <div class="mt-8 border-t border-slate-300 pt-1">
                        <p class="text-xs font-semibold text-slate-700">{{ $student->department->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                <p class="text-xs text-slate-400">Generated on {{ bsDate(now()) }} at {{ now()->format('h:i A') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide everything except the marksheet */
    body * {
        visibility: hidden;
    }
    
    /* Show only the marksheet container and its children */
    .max-w-6xl, .max-w-6xl * {
        visibility: visible;
    }
    
    /* Position marksheet at top of page */
    .max-w-6xl {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 20px;
    }
    
    /* Remove backgrounds and shadows */
    body {
        background: white !important;
    }
    
    /* Hide navigation elements */
    aside, nav, header, .print\:hidden {
        display: none !important;
        visibility: hidden !important;
    }
    
    /* Clean up marksheet styling for print */
    .rounded-xl {
        border-radius: 0 !important;
    }
    
    .shadow-lg {
        box-shadow: none !important;
    }
    
    /* Ensure proper page breaks */
    .bg-white {
        page-break-inside: avoid;
    }
    
    /* Remove hover effects */
    .hover\:bg-slate-50:hover {
        background-color: transparent !important;
    }
}
</style>
@endsection
