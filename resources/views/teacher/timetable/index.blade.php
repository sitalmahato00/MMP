@extends('layouts.app')

@section('title', 'My Timetable')

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        @page {
            size: landscape;
            margin: 0.5cm;
        }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                        <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                            My Timetable
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">Weekly class schedule</p>
                    </div>
                    @if($slots->isNotEmpty())
                        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition print:hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                    @endif
                </div>

                {{-- Semester Dropdown --}}
                @if($semesterOptions->count() > 0)
                    <div class="flex items-center gap-3 print:hidden">
                        <label class="text-sm font-semibold text-slate-700">Select Semester:</label>
                        <x-select 
                            name="semester_key" 
                            onchange="window.location.href='{{ route('teacher.timetable.index') }}?semester_key=' + this.value"
                            class="w-auto">
                            @foreach($semesterOptions as $index => $sem)
                                <option value="{{ $index }}" {{ $selectedKey == $index ? 'selected' : '' }}>
                                    {{ $sem['program_name'] }} - Semester {{ $sem['semester'] }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- My Classes Today (Teacher's Own from Selected Semester - All Sections) --}}
    @if($myTodaySlots->isNotEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm print:hidden">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold text-slate-900">My Classes Today</h2>
                <p class="text-xs text-slate-500">{{ bsDate(now(), 'l, F d, Y') }}</p>
            </div>
            <div class="p-4 sm:p-6 space-y-3">
                @foreach($myTodaySlots as $slot)
                    <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
                        <div class="flex flex-col items-center justify-center rounded-lg bg-blue-50 px-3 py-2 min-w-fit">
                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</span>
                            <span class="text-[10px] text-blue-500">to</span>
                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900">{{ $slot->subject->name }}</h3>
                            <p class="text-xs text-slate-500">
                                @if($slot->timetable->section)
                                    Section {{ $slot->timetable->section }}
                                @endif
                                @if($slot->group)
                                    {{ $slot->timetable->section ? ' • ' : '' }}Group {{ $slot->group }}
                                @endif
                                @if($slot->room_number)
                                    {{ ($slot->timetable->section || $slot->group) ? ' • ' : '' }}Room {{ $slot->room_number }}
                                @endif
                            </p>
                            @if($slot->type && $slot->type !== 'theory')
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ ucfirst($slot->type) }}
                                </span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Today
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Weekly Timetable Grid --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 print-area">
        <div class="px-4 py-3 border-b border-slate-200 print:px-2 print:py-2">
            <h2 class="text-sm font-semibold text-slate-900 print:text-xs">Complete Class Schedule</h2>
            @if($selectedSemester)
                <p class="text-xs text-slate-500 print:text-[10px]">
                    {{ $selectedSemester['program_name'] }} - Semester {{ $selectedSemester['semester'] }}
                </p>
            @endif
        </div>
        
        <div class="p-4 print:p-2">
            @if($slots->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No timetable available for this semester</p>
                    @if($semesterOptions->count() > 1)
                        <p class="mt-1 text-xs text-slate-400">Try selecting a different semester from the dropdown above</p>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto border border-slate-300 rounded">
                    <x-timetable-grid 
                        :slots="$slots"
                        :subjects="$subjects"
                        :teachers="$teachers"
                        :editable="false" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
