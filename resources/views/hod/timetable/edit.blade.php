@extends('layouts.app')

@section('title', 'Edit Timetable')

{{-- Cache Buster: {{ now() }} --}}

@section('content')
<div x-data="timetableEditor()" x-init="init()" class="space-y-4">

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-800">Timetable could not be saved — please fix the following:</p>
                    <ul class="mt-1 list-disc list-inside space-y-0.5 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <x-page-header 
        title="Edit Timetable" 
        :subtitle="$department->name . ' — ' . $timetable->program->name . ' - Semester ' . $timetable->semester . ($timetable->section ? ' • Section ' . $timetable->section : '')"
        back="{{ route('hod.timetable.index') }}">
        <div class="flex items-center gap-2">
            <x-export-dropdown 
                :exportUrl="route('hod.timetable.export', $timetable)"
                :formats="['pdf', 'csv']"
                buttonText="Export"
                buttonClass="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition" />
            
            <form method="POST" action="{{ route('hod.timetable.destroy', $timetable) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this timetable? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
            <button type="button" @click="saveAll()"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Timetable
            </button>
        </div>
    </x-page-header>

    {{-- Timetable Info Form --}}
    <form method="POST" action="{{ route('hod.timetable.update', $timetable) }}" id="timetableForm">
        @csrf
        @method('PUT')
        
        <x-form-section 
            title="Timetable Information" 
            subtitle="Update the basic details of the timetable">
            
            <x-form-row>
                <x-form-field label="Program" name="program_id" required>
                    <x-select name="program_id" required disabled>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" @selected($timetable->program_id == $prog->id)>{{ $prog->name }}</option>
                        @endforeach
                    </x-select>
                    <input type="hidden" name="program_id" value="{{ $timetable->program_id }}">
                    <p class="mt-1 text-xs text-slate-500">Program cannot be changed after creation</p>
                </x-form-field>

                <x-form-field label="Semester" name="semester" required>
                    <x-select name="semester" required disabled>
                        @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected($timetable->semester == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                    <input type="hidden" name="semester" value="{{ $timetable->semester }}">
                    <p class="mt-1 text-xs text-slate-500">Semester cannot be changed after creation</p>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Section (Optional)" name="section">
                    <x-input 
                        type="text" 
                        name="section" 
                        :value="old('section', $timetable->section)" 
                        placeholder="e.g., A, B, Morning"/>
                </x-form-field>

                <x-form-field label="Academic Session" name="academic_session_id" required>
                    <x-select name="academic_session_id" required>
                        @foreach($academicSessions as $session)
                            <option value="{{ $session->id }}" @selected($timetable->academic_session_id == $session->id)>{{ $session->name }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Effective From (BS Date)" name="effective_from" required>
                    <x-bs-date-picker 
                        name="effective_from" 
                        :value="old('effective_from', bsDate($timetable->effective_from, 'Y-m-d'))"
                        adName="effective_from_ad"
                        required />
                </x-form-field>

                <x-form-field label="Status" name="is_active">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               @checked(old('is_active', $timetable->is_active))
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700">Active Timetable</span>
                    </label>
                </x-form-field>
            </x-form-row>
        </x-form-section>
    </form>

        {{-- Timetable Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-slate-200">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <h2 class="text-sm font-semibold text-slate-900">Timetable Slots</h2>
                <span class="text-blue-600 text-sm font-medium" x-text="slots.length + ' slots'"></span>
            </div>
            
            <div class="flex items-center gap-2">
                <button type="button" @click="addSlot()"
                        class="inline-flex items-center gap-1 rounded bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Slot
                </button>
                <button type="button" @click="saveAll()"
                        class="inline-flex items-center gap-1 rounded bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Timetable
                </button>
            </div>
        </div>

        {{-- Weekly Schedule --}}
        <div class="p-4">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Weekly Schedule</h3>
            
            <div class="overflow-x-auto">
                <div class="rounded-lg border border-slate-300 overflow-hidden shadow-sm">
                    <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr>
                            <th class="border border-slate-400 border-r border-r-slate-300 border-b border-b-slate-300 px-3 py-3 text-center font-bold text-sm w-20 bg-slate-800 text-white">Day</th>
                            <th class="border border-slate-400 px-3 py-3 text-center font-bold text-sm w-32 bg-slate-800 text-white">Period</th>
                            <th class="border border-slate-400 px-3 py-3 text-center font-bold text-sm bg-blue-700 text-white" colspan="2">Subject Details</th>
                        </tr>
                        <tr>
                            <th class="border border-slate-400 border-b border-b-slate-300 border-r border-r-slate-300 px-3 py-2 text-center font-bold text-xs bg-slate-700 text-white" colspan="2"></th>
                            <th class="border border-slate-400 px-3 py-2 text-center font-bold text-sm bg-blue-600 text-white w-1/2">Group A</th>
                            <th class="border border-slate-400 px-3 py-2 text-center font-bold text-sm bg-green-600 text-white w-1/2">Group B</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="daySchedule in scheduleRows" :key="daySchedule.day">
                            <template x-for="(row, rowIndex) in daySchedule.rows" :key="row.type + '-' + row.time">
                                <tr :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="hover:bg-slate-50 transition-colors">
                                    <!-- Day Column (only show for first period of each day) -->
                                    <template x-if="rowIndex === 0">
                                        <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 border-r-4 border-r-slate-800 px-3 py-2 bg-slate-100 font-bold text-slate-900 text-sm text-center align-top"
                                            :rowspan="daySchedule.rows.length"
                                            x-text="getDayLabel(daySchedule.day).toUpperCase()"
                                            style="writing-mode: vertical-rl; text-orientation: mixed;"></td>
                                    </template>
                                    
                                    <!-- Period Column -->
                                    <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 px-2 py-3 text-xs text-center bg-slate-50 font-medium text-slate-700" 
                                        x-text="row.type === 'add' ? 'New Slot' : formatTimeRange(row.time)"></td>
                                    
                                    <!-- Common subject across both groups -->
                                    <template x-if="row.type !== 'add' && hasCommonSlot(daySchedule.day, row.time)">
                                        <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 p-2 align-top min-w-[300px] h-20" colspan="2">
                                            <template x-for="(slot, index) in getCommonSlots(daySchedule.day, row.time)" :key="slot.id || index">
                                                <div class="h-16 p-3 relative group cursor-pointer rounded-md border-l-4 transition-all hover:shadow-md"
                                                     :class="getSlotColorClass(slot.subject_id)"
                                                     @click="editSlotByData(slot)">
                                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                                        <button type="button" @click.stop="editSlotByData(slot)"
                                                                class="rounded-full p-1.5 bg-white shadow-md hover:bg-blue-50 text-blue-600 border border-blue-200" title="Edit">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" @click.stop="removeSlotByData(slot)"
                                                                class="rounded-full p-1.5 bg-white shadow-md hover:bg-red-50 text-red-600 border border-red-200" title="Delete">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="font-bold text-sm text-slate-900 leading-tight mb-1" x-text="slot.type === 'break' ? 'BREAK' : getSubjectName(slot.subject_id)"></div>
                                                    <div class="text-xs text-slate-600 leading-tight mb-1" x-show="slot.type !== 'break'" x-text="'Teacher: ' + getTeacherName(slot.teacher_id)"></div>
                                                    <div x-show="slot.room_number" class="text-xs text-slate-500 leading-tight" x-text="'Room: ' + slot.room_number"></div>
                                                    <div x-show="slot.type && slot.type !== 'theory'" class="absolute bottom-2 left-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white/80 text-slate-700 border border-slate-300" x-text="slot.type"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </td>
                                    </template>

                                    <!-- Separate Group A and Group B cells when not common -->
                                    <template x-if="row.type !== 'add' && !hasCommonSlot(daySchedule.day, row.time)">
                                        <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 p-2 align-top w-1/2 h-20 bg-blue-50/30">
                                                <template x-if="getGroupSlots(daySchedule.day, row.time, 'A').length > 0">
                                                    <div class="space-y-1">
                                                        <template x-for="(slot, index) in getGroupSlots(daySchedule.day, row.time, 'A')" :key="slot.id || index">
                                                            <div class="h-16 p-3 relative group cursor-pointer rounded-md border-l-4 border-l-blue-500 bg-blue-50 transition-all hover:shadow-md hover:bg-blue-100"
                                                                 @click="editSlotByData(slot)">
                                                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                                                    <button type="button" @click.stop="editSlotByData(slot)"
                                                                            class="rounded-full p-1.5 bg-white shadow-sm hover:bg-blue-50 text-blue-600 border border-blue-200" title="Edit">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                        </svg>
                                                                    </button>
                                                                    <button type="button" @click.stop="removeSlotByData(slot)"
                                                                            class="rounded-full p-1.5 bg-white shadow-sm hover:bg-red-50 text-red-600 border border-red-200" title="Delete">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                                <div class="font-bold text-sm text-slate-900 leading-tight mb-1" x-text="slot.type === 'break' ? 'BREAK' : getSubjectName(slot.subject_id)"></div>
                                                                <div class="text-sm text-slate-600 leading-tight" x-show="slot.type !== 'break'" x-text="getTeacherName(slot.teacher_id)"></div>
                                                                <div x-show="slot.room_number" class="text-sm text-slate-500 leading-tight" x-text="slot.room_number"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="getGroupSlots(daySchedule.day, row.time, 'A').length === 0">
                                                    <div class="h-16 flex items-center justify-center">
                                                        <button type="button"
                                                                @click="openAddSlotModal(daySchedule.day, row.time, 'A')"
                                                                class="opacity-30 hover:opacity-100 transition-all border border-dashed border-blue-300 hover:border-blue-400 hover:bg-blue-100 w-full h-full flex items-center justify-center group rounded-md">
                                                            <div class="text-center">
                                                                <svg class="w-4 h-4 text-blue-400 group-hover:text-blue-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                                </svg>
                                                                <span class="text-sm text-blue-500 group-hover:text-blue-600">Add Group A</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </template>
                                        </td>
                                    </template>
                                    <template x-if="row.type !== 'add' && !hasCommonSlot(daySchedule.day, row.time)">
                                        <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 p-2 align-top w-1/2 h-20 bg-green-50/30">
                                                <template x-if="getGroupSlots(daySchedule.day, row.time, 'B').length > 0">
                                                    <div class="space-y-1">
                                                        <template x-for="(slot, index) in getGroupSlots(daySchedule.day, row.time, 'B')" :key="slot.id || index">
                                                            <div class="h-16 p-3 relative group cursor-pointer rounded-md border-l-4 border-l-green-500 bg-green-50 transition-all hover:shadow-md hover:bg-green-100"
                                                                 @click="editSlotByData(slot)">
                                                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                                                    <button type="button" @click.stop="editSlotByData(slot)"
                                                                            class="rounded-full p-1.5 bg-white shadow-sm hover:bg-green-50 text-green-600 border border-green-200" title="Edit">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                                        </svg>
                                                                    </button>
                                                                    <button type="button" @click.stop="removeSlotByData(slot)"
                                                                            class="rounded-full p-1.5 bg-white shadow-sm hover:bg-red-50 text-red-600 border border-red-200" title="Delete">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                                <div class="font-bold text-sm text-slate-900 leading-tight mb-1" x-text="slot.type === 'break' ? 'BREAK' : getSubjectName(slot.subject_id)"></div>
                                                                <div class="text-sm text-slate-600 leading-tight" x-show="slot.type !== 'break'" x-text="getTeacherName(slot.teacher_id)"></div>
                                                                <div x-show="slot.room_number" class="text-sm text-slate-500 leading-tight" x-text="slot.room_number"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="getGroupSlots(daySchedule.day, row.time, 'B').length === 0">
                                                    <div class="h-16 flex items-center justify-center">
                                                        <button type="button"
                                                                @click="openAddSlotModal(daySchedule.day, row.time, 'B')"
                                                                class="opacity-30 hover:opacity-100 transition-all border border-dashed border-green-300 hover:border-green-400 hover:bg-green-100 w-full h-full flex items-center justify-center group rounded-md">
                                                            <div class="text-center">
                                                                <svg class="w-4 h-4 text-green-400 group-hover:text-green-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                                </svg>
                                                                <span class="text-sm text-green-500 group-hover:text-green-600">Add Group B</span>
                                                            </div>
                                                        </button>
                                                    </div>
                                                </template>
                                        </td>
                                    </template>
                                    <template x-if="row.type === 'add'">
                                        <td :class="rowIndex === daySchedule.rows.length - 1 ? 'border-b-4 border-b-slate-700' : ''" class="border border-slate-300 p-2 align-top min-w-[300px] h-20 bg-slate-50" colspan="2">
                                            <div class="h-16 flex items-center justify-center">
                                                <button type="button"
                                                        @click="openAddSlotModal(daySchedule.day, row.time, '')"
                                                        class="opacity-70 hover:opacity-100 transition-all border border-dashed border-slate-300 hover:border-blue-400 hover:bg-blue-50 w-full h-full flex items-center justify-center group rounded-md">
                                                    <div class="text-center">
                                                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                        <span class="text-sm text-slate-500 group-hover:text-blue-700">Add New Slot</span>
                                                    </div>
                                                </button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEditModal" 
         x-cloak
         @click.self="showEditModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.stop class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <template x-if="editingSlot">
                <div>
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900">Edit Slot</h3>
                        <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <x-slot-form 
                            :subjects="$subjects"
                            :teachers="$teachers"
                            :availableGroups="[]"
                            :isEditing="true" />
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <button @click="showEditModal = false" 
                                class="px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition">
                            Cancel
                        </button>
                        <button @click="saveSlotChanges()" 
                                :disabled="saving || teacherConflicts.length > 0 || durationConflicts.length > 0"
                                :class="saving || teacherConflicts.length > 0 || durationConflicts.length > 0 ? 'bg-slate-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition">
                            <span x-text="saving ? 'Saving…' : 'Save Changes'"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>{{-- /x-data --}}

<script>
function timetableEditor() {
    const subjects = @json($subjects);
    const teachers = @json($teachers);
    const globalTimeSlots = @json($timeSlots ?? []);
    // expose globally for debug/console access
    try { window.globalTimeSlots = globalTimeSlots; } catch(e) {}
    
    return {
        slots: @json($slotsData),
        days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        allTeachers: teachers,
        availableTeachers: teachers,
        loadingTeachers: false,
        showEditModal: false,
        editingSlot: null,
        saving: false,
        saveError: null,
        teacherConflicts: [],
        durationConflicts: [],
        
        init() {
            this.loadAvailableGroups();
        },
        
        get scheduleRows() {
            const timeSlots = this.getTimeSlots();

            return this.days.map(day => {
                const rows = timeSlots.map(ts => {
                    const hasSlot = this.slots.some(s => s.day_of_week === day && `${this.hi(s.start_time)}-${this.hi(s.end_time)}` === ts);
                    return { type: hasSlot ? 'slot' : 'empty', time: ts };
                }).filter(row => row.type === 'slot');
                rows.push({ type: 'add', time: this.getNextTimeRange(day, timeSlots) });
                return { day, rows };
            });
        },

        getTimeSlots() {
            const savedTimeSlots = this.slots.map(s => `${this.hi(s.start_time)}-${this.hi(s.end_time)}`);
            return Array.from(new Set([...(globalTimeSlots || []), ...savedTimeSlots]))
                .filter(Boolean)
                .sort((a, b) => this.timeToMinutes(a.split('-')[0]) - this.timeToMinutes(b.split('-')[0]));
        },

        getNextTimeRange(day, timeSlots) {
            const dayTimeSlots = this.slots
                .filter(slot => slot.day_of_week === day)
                .map(slot => `${this.hi(slot.start_time)}-${this.hi(slot.end_time)}`);
            const sourceSlots = dayTimeSlots.length > 0 ? dayTimeSlots : timeSlots;
            const sortedSlots = sourceSlots
                .filter(Boolean)
                .sort((a, b) => this.timeToMinutes(a.split('-')[1]) - this.timeToMinutes(b.split('-')[1]));
            const lastSlot = sortedSlots[sortedSlots.length - 1];

            if (!lastSlot) {
                return '06:30-07:15';
            }

            const [, lastEnd] = lastSlot.split('-');
            const nextStart = this.minutesToTime(this.timeToMinutes(lastEnd) + 15);
            const nextEnd = this.minutesToTime(this.timeToMinutes(nextStart) + 45);

            return `${nextStart}-${nextEnd}`;
        },

        minutesToTime(minutes) {
            const hrs = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
        },

        addMinutes(time, minutes) {
            const total = this.timeToMinutes(time) + minutes;
            return this.minutesToTime(total);
        },

        // Normalize a time value to HH:MM regardless of whether it has seconds
        hi(t) { return t ? String(t).slice(0, 5) : ''; },

        getSlotForCell(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.find(slot => 
                slot.day_of_week === day && 
                this.hi(slot.start_time) === start && 
                this.hi(slot.end_time) === end
            );
        },
        
        getSlotsForCell(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.filter(slot => 
                slot.day_of_week === day && 
                this.hi(slot.start_time) === start && 
                this.hi(slot.end_time) === end
            );
        },

        // Check if there are common slots (no group specified)
        hasCommonSlot(day, timeRange) {
            const [start, end] = timeRange.split('-');
            const slotsForTime = this.slots.filter(slot => 
                slot.day_of_week === day && 
                this.hi(slot.start_time) === start && 
                this.hi(slot.end_time) === end
            );
            // Check if there are slots without specific groups (common to all)
            return slotsForTime.some(slot => {
                const g = (slot.group ?? '').toString().trim();
                return g === '';
            });
        },

        // Get common slots (for all groups)
        getCommonSlots(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.filter(slot => 
                slot.day_of_week === day && 
                this.hi(slot.start_time) === start && 
                this.hi(slot.end_time) === end &&
                ((slot.group ?? '').toString().trim() === '')
            );
        },

        // Get slots for specific group (case-insensitive, tolerant of null/empty)
        getGroupSlots(day, timeRange, group) {
            const [start, end] = timeRange.split('-');
            const gNorm = (group ?? '').toString().trim().toUpperCase();
            return this.slots.filter(slot => {
                if (slot.day_of_week !== day) return false;
                if (this.hi(slot.start_time) !== start) return false;
                if (this.hi(slot.end_time) !== end) return false;
                const sg = (slot.group ?? '').toString().trim().toUpperCase();
                if (!sg) return false;
                if (sg === gNorm) return true;
                if (sg === 'BOTH' || sg === 'ALL') return true;
                if (sg.includes(',')) {
                    const parts = sg.split(',').map(s => s.trim());
                    return parts.includes(gNorm);
                }
                return false;
            });
        },

        // Open add slot modal with pre-filled group
        openAddSlotModal(day, timeRange, group = '') {
            const [start, end] = timeRange.split('-');
            this.editingSlot = {
                day_of_week: day,
                start_time: start,
                end_time: end,
                subject_id: null,
                teacher_id: null,
                room_number: '',
                type: 'theory',
                group: group || null,
                duration: '1'
            };
            this.teacherConflicts = [];
            this.durationConflicts = [];
            this.updateAvailableTeachers();
            this.showEditModal = true;
        },
        
        addSlotForCell(day, timeRange) {
            // This method is kept for backward compatibility
            this.openAddSlotModal(day, timeRange, '');
        },
        
        async editSlotByData(slot) {
            this.editingSlot = {
                ...slot,
                start_time: this.hi(slot.start_time),
                end_time:   this.hi(slot.end_time),
                duration:   slot.duration || 1,
                type:       slot.type || 'theory',
            };
            this.checkTeacherConflicts();
            this.checkDurationConflicts();
            // If slot has a subject pre-selected, load its assigned teachers
            if (this.editingSlot.subject_id) {
                await this.onSubjectChange(true); // keepExistingTeacher = true
            } else {
                this.updateAvailableTeachers();
            }
            this.showEditModal = true;
        },

        async onSubjectChange(keepExistingTeacher = false) {
            if (!this.editingSlot.subject_id) {
                this.availableTeachers = this.allTeachers;
                return;
            }

            // When the user manually changes the subject, clear the teacher selection
            if (!keepExistingTeacher) {
                this.editingSlot.teacher_id = null;
            }
            
            // Get subject teachers from API
            this.loadingTeachers = true;
            try {
                const response = await fetch(`{{ route('hod.timetable.subject-teachers', $timetable) }}?subject_id=${this.editingSlot.subject_id}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                // Update available teachers
                this.availableTeachers = data.teachers;
                
                // Auto-set type based on subject (only when user changes subject, not on open)
                if (!keepExistingTeacher) {
                    this.editingSlot.type = data.subject_type || 'theory';
                }
                
                // Auto-select teacher if none already chosen
                if (!this.editingSlot.teacher_id && this.availableTeachers.length > 0) {
                    // Prefer a specifically assigned teacher (non-empty role means assigned)
                    // data.has_assigned tells us if these are specifically assigned vs dept fallback
                    if (data.has_assigned) {
                        // Subject has specific assigned teachers — auto-select first one
                        this.editingSlot.teacher_id = this.availableTeachers[0].id;
                    } else if (this.availableTeachers.length === 1) {
                        // Only one dept teacher available — auto-select them
                        this.editingSlot.teacher_id = this.availableTeachers[0].id;
                    }
                    if (this.editingSlot.teacher_id) {
                        await this.checkTeacherConflicts();
                    }
                }
                
            } catch (error) {
                console.error('Error fetching subject teachers:', error);
            } finally {
                this.loadingTeachers = false;
            }
        },

        updateAvailableTeachers(subjectTeachers = null) {
            if (subjectTeachers && subjectTeachers.length > 0) {
                // Filter teachers based on subject assignment
                this.availableTeachers = this.allTeachers.filter(teacher => 
                    subjectTeachers.includes(teacher.id)
                );
            } else {
                this.availableTeachers = this.allTeachers;
            }
        },

        slotGroupsConflict(groupA, groupB) {
            const a = (groupA ?? '').toString().trim().toUpperCase();
            const b = (groupB ?? '').toString().trim().toUpperCase();
            if (!a || !b) return true; // treat empty as common
            return a === b;
        },

        hasSlotConflict(slotToCheck) {
            return this.slots.some(existing => {
                if (existing.id && slotToCheck.id && existing.id === slotToCheck.id) {
                    return false;
                }
                if (existing.day_of_week !== slotToCheck.day_of_week) {
                    return false;
                }
                if (!this.timeOverlaps(existing.start_time, existing.end_time, slotToCheck.start_time, slotToCheck.end_time)) {
                    return false;
                }
                return this.slotGroupsConflict(existing.group, slotToCheck.group);
            });
        },

        async checkTeacherConflicts() {
            this.teacherConflicts = [];
            
            if (!this.editingSlot.teacher_id || !this.editingSlot.day_of_week || !this.editingSlot.start_time) {
                return;
            }

            try {
                const response = await fetch(`{{ route('hod.timetable.check-teacher-conflicts', $timetable) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        teacher_id: this.editingSlot.teacher_id,
                        day_of_week: this.editingSlot.day_of_week,
                        start_time: this.editingSlot.start_time,
                        end_time: this.editingSlot.end_time,
                        slot_id: this.editingSlot.id
                    })
                });

                const data = await response.json();
                
                if (data.has_conflicts) {
                    this.teacherConflicts = data.conflicts.map(conflict => conflict.message);
                }
            } catch (error) {
                console.error('Error checking teacher conflicts:', error);
            }
        },

        checkDurationConflicts() {
            this.durationConflicts = [];
            
            if (!this.editingSlot.duration || this.editingSlot.duration === 1 || !this.editingSlot.start_time) {
                return;
            }

            const duration = parseInt(this.editingSlot.duration);
            const startTime = this.editingSlot.start_time;
            const proposedEnd = this.addMinutes(startTime, duration * 45);

            const conflictingSlot = this.slots.find(slot => 
                slot.id !== this.editingSlot.id &&
                slot.day_of_week === this.editingSlot.day_of_week &&
                this.slotGroupsConflict(slot.group, this.editingSlot.group) &&
                this.timeOverlaps(slot.start_time, slot.end_time, startTime, proposedEnd)
            );

            if (conflictingSlot) {
                this.durationConflicts.push(`Extending duration conflicts with ${this.getSubjectName(conflictingSlot.subject_id)} at ${this.hi(conflictingSlot.start_time)}`);
            }
        },

        onDurationChange() {
            if (!this.editingSlot.start_time) {
                return;
            }

            const duration = parseInt(this.editingSlot.duration);
            if (!duration || duration < 1) {
                this.editingSlot.duration = 1;
            }

            const proposedEnd = this.addMinutes(this.editingSlot.start_time, duration * 45);
            this.editingSlot.end_time = proposedEnd;
            this.checkDurationConflicts();
        },

        loadAvailableGroups() {
            this.availableGroups = ['A', 'B'];
        },

        async saveSlotChanges() {
            // Validate type field is set
            if (!this.editingSlot.type) {
                alert('Please select a valid slot type');
                return;
            }

            // Validate required fields (skip for break type)
            if (this.editingSlot.type !== 'break') {
                if (!this.editingSlot.subject_id || !this.editingSlot.teacher_id) {
                    alert('Please select both subject and teacher');
                    return;
                }
            }

            // Check for conflicts (skip teacher conflicts for break)
            if (this.editingSlot.type !== 'break' && this.teacherConflicts.length > 0) {
                alert('Please resolve teacher conflicts before saving');
                return;
            }
            
            if (this.durationConflicts.length > 0) {
                alert('Please resolve duration conflicts before saving');
                return;
            }

            if (this.hasSlotConflict(this.editingSlot)) {
                alert('This slot overlaps with another slot for the same group or common schedule. Please choose a different time or group.');
                return;
            }

            // Handle duration extension - remove conflicting slots or adjust them
            if (this.editingSlot.duration > 1) {
                await this.handleDurationExtension();
            }

            this.saveError = null;

            const savedSlot = JSON.parse(JSON.stringify(this.editingSlot));
            
            // Ensure start_time and end_time are properly formatted (HH:MM)
            savedSlot.start_time = this.hi(savedSlot.start_time);
            savedSlot.end_time = this.hi(savedSlot.end_time);
            savedSlot.duration = parseInt(savedSlot.duration) || 1;
            
            const existingSlotIndex = this.slots.findIndex(slot => {
                if (savedSlot.id && slot.id) return slot.id === savedSlot.id;
                return slot.day_of_week === savedSlot.day_of_week &&
                    this.hi(slot.start_time) === this.hi(savedSlot.start_time) &&
                    this.hi(slot.end_time) === this.hi(savedSlot.end_time) &&
                    (slot.group ?? '') === (savedSlot.group ?? '');
            });

            if (existingSlotIndex !== -1) {
                this.slots = this.slots.map((slot, index) => index === existingSlotIndex ? savedSlot : slot);
            } else {
                this.slots = [...this.slots, savedSlot];
            }

            this.showEditModal = false;
            this.editingSlot = null;
        },

        async handleDurationExtension() {
            const startTime = this.editingSlot.start_time;
            const endTime = this.editingSlot.end_time;

            this.slots = this.slots.filter(slot => {
                if (slot.id && this.editingSlot.id && slot.id === this.editingSlot.id) {
                    return true;
                }
                if (slot.day_of_week !== this.editingSlot.day_of_week) {
                    return true;
                }
                if (!this.slotGroupsConflict(slot.group, this.editingSlot.group)) {
                    return true;
                }
                return !this.timeOverlaps(slot.start_time, slot.end_time, startTime, endTime);
            });
        },

        timeOverlaps(start1, end1, start2, end2) {
            const s1 = this.timeToMinutes(start1);
            const e1 = this.timeToMinutes(end1);
            const s2 = this.timeToMinutes(start2);
            const e2 = this.timeToMinutes(end2);
            
            return s1 < e2 && s2 < e1;
        },

        timeToMinutes(time) {
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        },
        
        removeSlotByData(slot) {
            if (!confirm('Are you sure you want to delete this slot?')) {
                return;
            }
            
            // If slot has an ID, delete it from the database
            if (slot.id) {
                fetch(`{{ route('hod.timetable.slots.destroy', [$timetable, '__SLOT_ID__']) }}`.replace('__SLOT_ID__', slot.id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove from local array
                        const index = this.slots.indexOf(slot);
                        if (index !== -1) {
                            this.slots.splice(index, 1);
                        }
                    } else {
                        alert('Failed to delete slot: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete slot: ' + error.message);
                });
            } else {
                // Remove from local array only (no DB record to delete)
                const index = this.slots.indexOf(slot);
                if (index !== -1) {
                    this.slots.splice(index, 1);
                }
            }
        },
        
        addSlot() {
            const day = this.days[0] || 'monday';
            this.openAddSlotModal(day, this.getNextTimeRange(day, this.getTimeSlots()), '');
        },
        
        formatTime(time) {
            const [hours, minutes] = time.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const displayHour = h > 12 ? h - 12 : (h === 0 ? 12 : h);
            return displayHour + ':' + minutes + ' ' + ampm;
        },
        
        formatTimeRange(timeRange) {
            const [start, end] = timeRange.split('-');
            return this.formatTime(start) + ' - ' + this.formatTime(end);
        },
        
        getSubjectName(subjectId) {
            const subject = subjects.find(s => s.id == subjectId);
            return subject ? subject.name : 'No Subject';
        },
        
        getTeacherName(teacherId) {
            const teacher = teachers.find(t => t.id == teacherId);
            if (!teacher) return 'No Teacher';
            // original PHP format has nested user.name; API format has flat name
            return teacher.user?.name || teacher.name || 'No Teacher';
        },
        
        getDayLabel(day) {
            const labels = {
                'monday': 'Mon',
                'tuesday': 'Tue', 
                'wednesday': 'Wed',
                'thursday': 'Thu',
                'friday': 'Fri',
                'saturday': 'Sat',
                'sunday': 'Sun'
            };
            return labels[day] || day;
        },
        
        getSlotColorClass(subjectId) {
            const colors = [
                'bg-blue-50 border-l-blue-500 hover:bg-blue-100',
                'bg-emerald-50 border-l-emerald-500 hover:bg-emerald-100',
                'bg-violet-50 border-l-violet-500 hover:bg-violet-100',
                'bg-amber-50 border-l-amber-500 hover:bg-amber-100',
                'bg-rose-50 border-l-rose-500 hover:bg-rose-100',
                'bg-cyan-50 border-l-cyan-500 hover:bg-cyan-100',
                'bg-pink-50 border-l-pink-500 hover:bg-pink-100',
                'bg-indigo-50 border-l-indigo-500 hover:bg-indigo-100',
            ];
            const index = subjects.findIndex(s => s.id == subjectId);
            return index >= 0 ? colors[index % colors.length] : 'bg-slate-50 border-l-slate-400 hover:bg-slate-100';
        },
        
        saveAll() {
            const form = document.getElementById('timetableForm');
            
            // Remove any existing slot inputs
            const existingInputs = form.querySelectorAll('input[name^="slots["]');
            existingInputs.forEach(input => input.remove());
            
            // Serialize via JSON to get plain objects from Alpine proxy
            const plainSlots = JSON.parse(JSON.stringify(this.slots));
            const fields = ['day_of_week', 'start_time', 'end_time', 'subject_id', 'teacher_id', 'room_number', 'type', 'group', 'duration'];
            
            plainSlots.forEach((slot, index) => {
                fields.forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `slots[${index}][${key}]`;

                    if (key === 'type') {
                        input.value = slot.type || 'theory';
                    } else if (key === 'duration') {
                        input.value = parseInt(slot.duration) || 1;
                    } else if (key === 'subject_id' || key === 'teacher_id') {
                        // Send empty string for null so Laravel's nullable rule passes
                        input.value = slot[key] ?? '';
                    } else if (key === 'group') {
                        // Send empty string for null/undefined group (All Groups)
                        input.value = slot[key] ?? '';
                    } else {
                        input.value = slot[key] ?? '';
                    }

                    form.appendChild(input);
                });
            });
            
            form.submit();
        }
    }
}
</script>
@endsection
