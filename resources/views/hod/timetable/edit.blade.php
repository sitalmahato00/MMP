@extends('layouts.app')

@section('title', 'Edit Timetable')

{{-- Cache Buster: {{ now() }} --}}

@section('content')
<div x-data="timetableEditor()" x-init="init()" class="space-y-4">

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
                    <p class="mt-1 text-xs text-slate-500">Program cannot be changed after creation</p>
                </x-form-field>

                <x-form-field label="Semester" name="semester" required>
                    <x-select name="semester" required disabled>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected($timetable->semester == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
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
            
            <button type="button" @click="openAddSlotModal('monday', '06:30-07:15', '')"
                    class="inline-flex items-center gap-1 rounded bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Slot
            </button>
        </div>

        {{-- Weekly Schedule --}}
        <div class="p-4">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Weekly Schedule</h3>
            
            <div class="overflow-x-auto border border-slate-300 rounded-lg shadow-sm">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <tr>
                            <th class="border border-slate-400 px-3 py-3 text-center font-bold text-sm w-20 bg-slate-800 text-white">Day</th>
                            <th class="border border-slate-400 px-3 py-3 text-center font-bold text-sm w-32 bg-slate-800 text-white">Period</th>
                            <th class="border border-slate-400 px-3 py-3 text-center font-bold text-sm bg-blue-700 text-white" colspan="2">Subject Details</th>
                        </tr>
                        <tr>
                            <th class="border border-slate-400 px-3 py-2 text-center font-bold text-xs bg-slate-700 text-white" colspan="2"></th>
                            <th class="border border-slate-400 px-3 py-2 text-center font-bold text-sm bg-blue-600 text-white w-1/2">Group A</th>
                            <th class="border border-slate-400 px-3 py-2 text-center font-bold text-sm bg-green-600 text-white w-1/2">Group B</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(day, dayIndex) in days" :key="day">
                            <template x-for="(time, timeIndex) in uniqueTimeSlots" :key="time">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Day Column (only show for first period of each day) -->
                                    <template x-if="timeIndex === 0">
                                        <td class="border border-slate-300 px-3 py-2 bg-slate-100 font-bold text-slate-900 text-sm text-center align-top"
                                            :rowspan="uniqueTimeSlots.length"
                                            x-text="getDayLabel(day).toUpperCase()"
                                            style="writing-mode: vertical-rl; text-orientation: mixed;"></td>
                                    </template>
                                    
                                    <!-- Period Column -->
                                    <td class="border border-slate-300 px-2 py-3 text-xs text-center bg-slate-50 font-medium text-slate-700" 
                                        x-text="formatTimeRange(time)"></td>
                                    
                                    <!-- Check if there are common slots (same subject for both groups) -->
                                    <template x-if="hasCommonSlot(day, time)">
                                        <!-- Merged cell for common subjects -->
                                        <td class="border border-slate-300 p-2 align-top min-w-[300px] h-20" colspan="2">
                                            <template x-for="(slot, index) in getCommonSlots(day, time)" :key="slot.id || index">
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
                                                    

                                                    
                                                    <!-- Type Badge -->
                                                    <div x-show="slot.type && slot.type !== 'theory'" class="absolute bottom-2 left-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white/80 text-slate-700 border border-slate-300" x-text="slot.type"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <!-- Add button for common slot -->
                                            <template x-if="getCommonSlots(day, time).length === 0">
                                                <div class="h-16 flex items-center justify-center">
                                                    <button type="button" 
                                                            @click="openAddSlotModal(day, time, '')"
                                                            class="opacity-30 hover:opacity-100 transition-all border-2 border-dashed border-slate-300 hover:border-purple-400 hover:bg-purple-50 w-full h-full flex items-center justify-center group rounded-md">
                                                        <div class="text-center">
                                                            <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                            </svg>
                                                            <span class="text-xs text-slate-500 group-hover:text-purple-600">Add Common Subject</span>
                                                        </div>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                    </template>
                                    
                                    <!-- Separate cells for Group A and Group B -->
                                    <template x-if="!hasCommonSlot(day, time)">
                                        <!-- Group A Cell -->
                                        <td class="border border-slate-300 p-2 align-top w-1/2 h-20 bg-blue-50/30">
                                            <template x-if="getGroupSlots(day, time, 'A').length > 0">
                                                <div class="space-y-1">
                                                    <template x-for="(slot, index) in getGroupSlots(day, time, 'A')" :key="slot.id || index">
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
                                            
                                            <template x-if="getGroupSlots(day, time, 'A').length === 0">
                                                <div class="h-16 flex items-center justify-center">
                                                    <button type="button" 
                                                            @click="openAddSlotModal(day, time, 'A')"
                                                            class="opacity-30 hover:opacity-100 transition-all border-2 border-dashed border-blue-300 hover:border-blue-400 hover:bg-blue-100 w-full h-full flex items-center justify-center group rounded-md">
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
                                    
                                    <template x-if="!hasCommonSlot(day, time)">
                                        <!-- Group B Cell -->
                                        <td class="border border-slate-300 p-2 align-top w-1/2 h-20 bg-green-50/30">
                                            <template x-if="getGroupSlots(day, time, 'B').length > 0">
                                                <div class="space-y-1">
                                                    <template x-for="(slot, index) in getGroupSlots(day, time, 'B')" :key="slot.id || index">
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
                                            
                                            <template x-if="getGroupSlots(day, time, 'B').length === 0">
                                                <div class="h-16 flex items-center justify-center">
                                                    <button type="button" 
                                                            @click="openAddSlotModal(day, time, 'B')"
                                                            class="opacity-30 hover:opacity-100 transition-all border-2 border-dashed border-green-300 hover:border-green-400 hover:bg-green-100 w-full h-full flex items-center justify-center group rounded-md">
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
                                :disabled="teacherConflicts.length > 0 || durationConflicts.length > 0"
                                :class="teacherConflicts.length > 0 || durationConflicts.length > 0 ? 'bg-slate-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition">
                            Save Changes
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
    
    return {
        slots: @json($slotsData),
        days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        showEditModal: false,
        editingSlot: null,
        availableTeachers: @json($teachers),
        allTeachers: @json($teachers),
        teacherConflicts: [],
        durationConflicts: [],
        availableGroups: [],
        
        async init() {
            // Load available groups from database
            await this.loadAvailableGroups();
        },
        
        get uniqueTimeSlots() {
            // Fixed time periods matching the original structure
            return [
                '06:30-07:15',
                '07:15-08:00',
                '08:00-08:45',
                '08:45-09:30',
                '09:30-10:15',
                '10:15-11:00',
                '11:00-11:45',
                '11:45-12:30',
                '12:30-13:15'
            ];
        },
        
        getSlotForCell(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.find(slot => 
                slot.day_of_week === day && 
                slot.start_time === start && 
                slot.end_time === end
            );
        },
        
        getSlotsForCell(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.filter(slot => 
                slot.day_of_week === day && 
                slot.start_time === start && 
                slot.end_time === end
            );
        },

        // Check if there are common slots (same subject for all groups)
        hasCommonSlot(day, timeRange) {
            const [start, end] = timeRange.split('-');
            const slotsForTime = this.slots.filter(slot => 
                slot.day_of_week === day && 
                slot.start_time === start && 
                slot.end_time === end
            );
            
            // Check if there are slots without specific groups (common to all)
            return slotsForTime.some(slot => !slot.group || slot.group === '');
        },

        // Get common slots (for all groups)
        getCommonSlots(day, timeRange) {
            const [start, end] = timeRange.split('-');
            return this.slots.filter(slot => 
                slot.day_of_week === day && 
                slot.start_time === start && 
                slot.end_time === end &&
                (!slot.group || slot.group === '')
            );
        },

        // Get slots for specific group
        getGroupSlots(day, timeRange, group) {
            const [start, end] = timeRange.split('-');
            return this.slots.filter(slot => 
                slot.day_of_week === day && 
                slot.start_time === start && 
                slot.end_time === end &&
                slot.group === group
            );
        },

        // Open add slot modal with pre-filled group
        openAddSlotModal(day, timeRange, group = '') {
            const [start, end] = timeRange.split('-');
            this.editingSlot = {
                day_of_week: day,
                start_time: start,
                end_time: end,
                subject_id: '',
                teacher_id: '',
                room_number: '',
                type: 'theory',
                group: group,
                duration: 1
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
        
        editSlotByData(slot) {
            this.editingSlot = { ...slot, duration: slot.duration || 1 };
            this.checkTeacherConflicts();
            this.checkDurationConflicts();
            this.updateAvailableTeachers();
            this.showEditModal = true;
        },

        async onSubjectChange() {
            if (!this.editingSlot.subject_id) return;
            
            // Get subject teachers from API
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
                
                // Auto-set type based on subject
                this.editingSlot.type = data.subject_type || 'theory';
                
                // Auto-select teacher if only one available
                if (this.availableTeachers.length === 1) {
                    this.editingSlot.teacher_id = this.availableTeachers[0].id;
                    await this.checkTeacherConflicts();
                }
                
                // Auto-select lab teacher for lab subjects
                if (data.subject_type === 'lab' || data.subject_type === 'practical') {
                    const labTeacher = this.availableTeachers.find(t => t.role === 'lab_assistant');
                    if (labTeacher) {
                        this.editingSlot.teacher_id = labTeacher.id;
                        await this.checkTeacherConflicts();
                    }
                }
                
            } catch (error) {
                console.error('Error fetching subject teachers:', error);
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
            
            if (!this.editingSlot.duration || this.editingSlot.duration === 1) {
                return;
            }

            const duration = parseInt(this.editingSlot.duration);
            const startTime = this.editingSlot.start_time;
            const timeSlots = this.uniqueTimeSlots;
            const startIndex = timeSlots.findIndex(slot => slot.startsWith(startTime));
            
            if (startIndex === -1) return;

            // Check if extending duration conflicts with existing slots
            for (let i = 1; i < duration; i++) {
                const nextSlotIndex = startIndex + i;
                if (nextSlotIndex >= timeSlots.length) {
                    this.durationConflicts.push(`Cannot extend beyond last period`);
                    break;
                }

                const nextTimeSlot = timeSlots[nextSlotIndex];
                const [nextStart] = nextTimeSlot.split('-');
                
                const conflictingSlot = this.slots.find(slot => 
                    slot.id !== this.editingSlot.id &&
                    slot.day_of_week === this.editingSlot.day_of_week &&
                    slot.start_time === nextStart
                );

                if (conflictingSlot) {
                    this.durationConflicts.push(`Conflicts with ${this.getSubjectName(conflictingSlot.subject_id)} at ${nextStart}`);
                }
            }
        },

        onDurationChange() {
            this.checkDurationConflicts();
            
            // Auto-adjust end time based on duration
            if (this.editingSlot.duration && this.editingSlot.start_time) {
                const duration = parseInt(this.editingSlot.duration);
                const timeSlots = this.uniqueTimeSlots;
                const startIndex = timeSlots.findIndex(slot => slot.startsWith(this.editingSlot.start_time));
                
                if (startIndex !== -1 && startIndex + duration - 1 < timeSlots.length) {
                    const endSlot = timeSlots[startIndex + duration - 1];
                    const [, endTime] = endSlot.split('-');
                    this.editingSlot.end_time = endTime;
                }
            }
        },

        async loadAvailableGroups() {
            try {
                const response = await fetch(`{{ route('hod.timetable.available-groups', $timetable) }}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                this.availableGroups = data.groups;
            } catch (error) {
                console.error('Error loading groups:', error);
                this.availableGroups = ['A', 'B', 'C', 'D']; // Fallback
            }
        },

        async saveSlotChanges() {
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

            // Handle duration extension - remove conflicting slots or adjust them
            if (this.editingSlot.duration > 1) {
                await this.handleDurationExtension();
            }

            // Find existing slot to update or add new one
            const existingSlotIndex = this.slots.findIndex(slot => 
                slot.day_of_week === this.editingSlot.day_of_week &&
                slot.start_time === this.editingSlot.start_time &&
                slot.end_time === this.editingSlot.end_time &&
                slot.group === this.editingSlot.group &&
                (!slot.id || slot.id === this.editingSlot.id)
            );

            if (existingSlotIndex !== -1) {
                // Update existing slot
                this.slots[existingSlotIndex] = { ...this.editingSlot };
            } else {
                // Add new slot
                this.slots.push({ ...this.editingSlot });
            }

            this.showEditModal = false;
            this.editingSlot = null;
        },

        async handleDurationExtension() {
            const duration = parseInt(this.editingSlot.duration);
            const startTime = this.editingSlot.start_time;
            const timeSlots = this.uniqueTimeSlots;
            const startIndex = timeSlots.findIndex(slot => slot.startsWith(startTime));
            
            if (startIndex === -1) return;

            // Remove or adjust conflicting slots for joint classes
            for (let i = 1; i < duration; i++) {
                const nextSlotIndex = startIndex + i;
                if (nextSlotIndex >= timeSlots.length) break;

                const nextTimeSlot = timeSlots[nextSlotIndex];
                const [nextStart] = nextTimeSlot.split('-');
                
                // Find and remove conflicting slots
                this.slots = this.slots.filter(slot => 
                    !(slot.id !== this.editingSlot.id &&
                      slot.day_of_week === this.editingSlot.day_of_week &&
                      slot.start_time === nextStart)
                );
            }
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
                // Just remove from local array if not saved yet
                const index = this.slots.indexOf(slot);
                if (index !== -1) {
                    this.slots.splice(index, 1);
                }
            }
        },
        
        addSlot() {
            // Redirect to modal-based adding
            this.openAddSlotModal('monday', '06:30-07:15', '');
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
            return teacher ? teacher.user.name : 'No Teacher';
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
            
            // Add slots data to form
            this.slots.forEach((slot, index) => {
                Object.keys(slot).forEach(key => {
                    if (key !== 'id') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `slots[${index}][${key}]`;
                        input.value = slot[key] || '';
                        form.appendChild(input);
                    }
                });
            });
            
            form.submit();
        }
    }
}
</script>
@endsection