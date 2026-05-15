@props([
    'slot' => null,
    'subjects' => [],
    'teachers' => [],
    'availableGroups' => [],
    'isEditing' => false
])

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Day</label>
            <x-select name="day_of_week" x-model="editingSlot.day_of_week" class="w-full">
                <option value="monday">Monday</option>
                <option value="tuesday">Tuesday</option>
                <option value="wednesday">Wednesday</option>
                <option value="thursday">Thursday</option>
                <option value="friday">Friday</option>
                <option value="saturday">Saturday</option>
                <option value="sunday">Sunday</option>
            </x-select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
            <x-select name="type" x-model="editingSlot.type" class="w-full">
                <option value="theory">Theory</option>
                <option value="practical">Practical</option>
                <option value="lab">Lab</option>
                <option value="library">Library</option>
                <option value="break">Break</option>
            </x-select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Start Time</label>
            <x-input type="time" name="start_time" x-model="editingSlot.start_time" class="w-full" />
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">End Time</label>
            <x-input type="time" name="end_time" x-model="editingSlot.end_time" class="w-full" />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
        <x-select name="subject_id" x-model="editingSlot.subject_id" @change="onSubjectChange()" class="w-full" x-show="editingSlot.type !== 'break'" :required="false">
            <option value="">Select Subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" 
                        data-type="{{ $subject->type ?? 'theory' }}"
                        data-teachers="{{ json_encode($subject->teachers ?? []) }}">
                    {{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif
                </option>
            @endforeach
        </x-select>
        <div x-show="editingSlot.type === 'break'" class="text-sm text-slate-500 italic">
            No subject required for break time
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Teacher</label>
        <x-select name="teacher_id" x-model="editingSlot.teacher_id" @change="checkTeacherConflicts()" class="w-full" x-show="editingSlot.type !== 'break'" :required="false">
            <option value="">Select Teacher</option>
            <template x-for="teacher in availableTeachers" :key="teacher.id">
                <option :value="teacher.id" x-text="teacher.name || teacher.user?.name"></option>
            </template>
        </x-select>
        <div x-show="teacherConflicts.length > 0 && editingSlot.type !== 'break'" class="mt-1 text-xs text-red-600">
            <div class="font-medium">⚠️ Teacher Conflicts:</div>
            <template x-for="conflict in teacherConflicts" :key="conflict">
                <div x-text="conflict"></div>
            </template>
        </div>
        <div x-show="editingSlot.type === 'break'" class="text-sm text-slate-500 italic">
            No teacher required for break time
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Room/Lab</label>
        <x-input type="text" name="room_number" x-model="editingSlot.room_number" placeholder="e.g., Room 201, IT Lab" class="w-full" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Group Assignment</label>
            <x-select name="group" x-model="editingSlot.group" class="w-full">
                <option value="">All Groups (Common Class)</option>
                <option value="A">Group A Only</option>
                <option value="B">Group B Only</option>
            </x-select>
            <p class="mt-1 text-xs text-slate-500">
                <span x-show="!editingSlot.group">This class will appear in merged cell for all groups</span>
                <span x-show="editingSlot.group">This class will appear only in Group <span x-text="editingSlot.group"></span> column</span>
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Duration (Periods)</label>
            <x-select name="duration" x-model="editingSlot.duration" @change="onDurationChange()" class="w-full">
                <option value="1">1 Period</option>
                <option value="2">2 Periods (Joint)</option>
                <option value="3">3 Periods (Extended)</option>
            </x-select>
            <div x-show="durationConflicts.length > 0" class="mt-1 text-xs text-red-600">
                <div class="font-medium">⚠️ Duration Conflicts:</div>
                <template x-for="conflict in durationConflicts" :key="conflict">
                    <div x-text="conflict"></div>
                </template>
            </div>
        </div>
    </div>
</div>