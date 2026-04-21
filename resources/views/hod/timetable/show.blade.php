@extends('layouts.app')

@section('title', 'View Timetable')

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
<div class="space-y-4">
    {{-- Header --}}
    <x-page-header 
        title="View Timetable" 
        :subtitle="$department->name . ' — ' . $timetable->program->name . ' - Semester ' . $timetable->semester . ($timetable->section ? ' • Section ' . $timetable->section : '')"
        back="{{ route('hod.timetable.index') }}">
        <div class="flex items-center gap-2">
            <x-export-dropdown 
                :exportUrl="route('hod.timetable.export', $timetable)"
                :formats="['pdf', 'csv']"
                buttonText="Export"
                buttonClass="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition" />
            
            <a href="{{ route('hod.timetable.edit', $timetable) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Timetable
            </a>
        </div>
    </x-page-header>

    {{-- Timetable Info Card --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Timetable Information</h2>
        </div>
        
        <div class="p-5">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Program</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $timetable->program->name }}</p>
                    <p class="text-xs text-slate-500">{{ $timetable->program->code }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Semester</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">Semester {{ $timetable->semester }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Section</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $timetable->section ?? 'All' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Academic Session</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $timetable->academicSession->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Effective From</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ bsDate($timetable->effective_from, 'F d, Y') }}</p>
                    <p class="text-xs text-slate-500">{{ bsDate($timetable->effective_from, 'l') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Slots</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $timetable->slots->count() }} slots</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Status</p>
                    <p class="mt-1">
                        @if($timetable->is_active)
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Weekly Timetable Grid --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 print-area">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between print:px-2 print:py-2">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 print:text-xs">Weekly Schedule</h2>
                <p class="text-xs text-slate-500 print:text-[10px]">Class routine for the week</p>
            </div>
            
            @if(!$timetable->slots->isEmpty())
                <x-export-dropdown 
                    :exportUrl="route('hod.timetable.export', $timetable)"
                    :formats="['pdf', 'csv']"
                    buttonText="Export"
                    buttonClass="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition print:hidden" />
            @endif
        </div>
        
        <div class="p-4 print:p-2">
            @if($timetable->slots->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No time slots defined yet</p>
                    <a href="{{ route('hod.timetable.edit', $timetable) }}" 
                       class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Slots
                    </a>
                </div>
            @else
                <div class="overflow-x-auto border border-slate-300 rounded">
                    <x-timetable-grid 
                        :slots="$timetable->slots"
                        :subjects="$subjects"
                        :teachers="$teachers"
                        :editable="false" />
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
