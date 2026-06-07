@props(['slots', 'subjects', 'teachers', 'timetable' => null, 'editable' => false])

@php
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    
    // Group slots by day, time, and group
    $slotsByDayTime = collect($slots)->groupBy(function($slot) {
        $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
        $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
        return $slot->day_of_week . '-' . $startTime . '-' . $endTime;
    });
    
    // Get unique time slots from actual data (only show slots that have classes)
    $timeSlots = collect($slots)->map(function($slot) {
        $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
        $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
        return $startTime . '-' . $endTime;
    })->unique()->sort()->values()->toArray();

    $timeSlotsByDay = collect($days)->mapWithKeys(function($day) use ($slots) {
        $dayTimeSlots = collect($slots)
            ->filter(fn($slot) => strtolower((string) $slot->day_of_week) === $day)
            ->map(function($slot) {
                $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
                $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
                return $startTime . '-' . $endTime;
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return [$day => $dayTimeSlots];
    });
    
    // Helper function to format time
    function formatTime($time) {
        $h = (int)substr($time, 0, 2);
        $m = substr($time, 3, 2);
        $ampm = $h >= 12 ? 'PM' : 'AM';
        $displayHour = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
        return $displayHour . ':' . $m . ' ' . $ampm;
    }
    
    // Helper function to get slots for a specific cell
    function getSlotsForCell($slots, $day, $timeSlot) {
        $slotKey = $day . '-' . $timeSlot;
        return $slots->get($slotKey, collect());
    }
    
    // Helper function to check if there are common slots (no group specified)
    function hasCommonSlot($slots, $day, $timeSlot) {
        $cellSlots = getSlotsForCell($slots, $day, $timeSlot);
        return $cellSlots->some(function($slot) {
            return empty($slot->group) || $slot->group === '';
        });
    }
    
    // Helper function to get common slots
    function getCommonSlots($slots, $day, $timeSlot) {
        $cellSlots = getSlotsForCell($slots, $day, $timeSlot);
        return $cellSlots->filter(function($slot) {
            return empty($slot->group) || $slot->group === '';
        });
    }
    
    // Helper function to get group-specific slots
    function getGroupSlots($slots, $day, $timeSlot, $group) {
        $cellSlots = getSlotsForCell($slots, $day, $timeSlot);
        return $cellSlots->filter(function($slot) use ($group) {
            return $slot->group === $group;
        });
    }
@endphp

<div class="rounded-lg border border-slate-800 overflow-hidden">
    <table class="w-full border-collapse bg-white text-xs print:text-[10px]">
    <thead>
        <tr>
            <th class="border border-slate-400 px-2 py-2 text-center font-bold text-xs w-16 bg-slate-800 text-white print:px-1 print:py-1">Day</th>
            <th class="border border-slate-400 px-2 py-2 text-center font-bold text-xs w-24 bg-slate-800 text-white print:px-1 print:py-1">Period</th>
            <th class="border border-slate-400 px-2 py-2 text-center font-bold text-xs bg-blue-700 text-white print:px-1 print:py-1" colspan="2">Subject Details</th>
        </tr>
        <tr>
            <th class="border border-slate-400 px-2 py-1 text-center font-bold text-xs bg-slate-700 text-white print:px-1 print:py-0.5" colspan="2"></th>
            <th class="border border-slate-400 px-2 py-1 text-center font-bold text-xs bg-blue-600 text-white w-1/2 print:px-1 print:py-0.5">Group A</th>
            <th class="border border-slate-400 px-2 py-1 text-center font-bold text-xs bg-green-600 text-white w-1/2 print:px-1 print:py-0.5">Group B</th>
        </tr>
    </thead>
    <tbody>
        @foreach($days as $dayIndex => $day)
            @php
                $dayTimeSlots = $timeSlotsByDay[$day] ?? [];
                $totalTimeSlots = count($dayTimeSlots);
            @endphp

            @continue($totalTimeSlots === 0)

            @foreach($dayTimeSlots as $timeIndex => $timeSlot)
                <tr class="hover:bg-slate-50 transition-colors print:hover:bg-white @if($timeIndex === $totalTimeSlots - 1) border-b-2 border-b-slate-700 @endif">
                    {{-- Day Column (only show for first period of each day) --}}
                    @if($timeIndex === 0)
                        <td class="border border-slate-300 border-r-2 border-r-slate-800 px-2 py-1 bg-slate-100 font-bold text-slate-900 text-xs text-center align-top print:px-1 print:py-0.5"
                            rowspan="{{ $totalTimeSlots }}"
                            style="writing-mode: vertical-rl; text-orientation: mixed;">
                            {{ strtoupper(substr($day, 0, 3)) }}
                        </td>
                    @endif
                    
                    {{-- Period Column --}}
                    <td class="border border-slate-300 px-1 py-2 text-xs text-center bg-slate-50 font-medium text-slate-700 print:px-0.5 print:py-1">
                        @php
                            [$start, $end] = explode('-', $timeSlot);
                        @endphp
                        {{ formatTime($start) }}<br class="print:hidden"/>-<br class="print:hidden"/>{{ formatTime($end) }}
                    </td>
                    
                    {{-- Check if there are common slots --}}
                    @if(hasCommonSlot($slotsByDayTime, $day, $timeSlot))
                        {{-- Merged cell for common subjects --}}
                        <td class="border border-slate-300 p-1.5 align-top min-w-[200px] print:p-1" colspan="2">
                            @foreach(getCommonSlots($slotsByDayTime, $day, $timeSlot) as $slot)
                                @php
                                    $subject = $subjects->firstWhere('id', $slot->subject_id);
                                    $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                @endphp
                                <div class="p-2 relative rounded border-l-4 border-l-purple-500 bg-purple-50 mb-1 print:p-1 print:mb-0.5">
                                    <div class="font-bold text-xs text-slate-900 leading-tight mb-0.5 print:text-[9px]">
                                        {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                    </div>
                                    @if($slot->type !== 'break')
                                        <div class="text-xs text-slate-600 leading-tight mb-0.5 print:text-[8px]">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                    @endif
                                    @if($slot->room_number)
                                        <div class="text-xs text-slate-500 leading-tight print:text-[8px]">Room: {{ $slot->room_number }}</div>
                                    @endif
                                    
                                    {{-- Type Badge --}}
                                    @if($slot->type && $slot->type !== 'theory')
                                        <div class="absolute bottom-1 left-1 print:bottom-0.5 print:left-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-white/80 text-slate-700 border border-slate-300 print:text-[7px] print:px-1 print:py-0">{{ $slot->type }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                    @else
                        {{-- Separate cells for Group A and Group B --}}
                        {{-- Group A Cell --}}
                        <td class="border border-slate-300 p-1.5 align-top w-1/2 bg-blue-50/30 print:p-1">
                            @foreach(getGroupSlots($slotsByDayTime, $day, $timeSlot, 'A') as $slot)
                                @php
                                    $subject = $subjects->firstWhere('id', $slot->subject_id);
                                    $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                @endphp
                                <div class="p-2 relative rounded border-l-4 border-l-blue-500 bg-blue-50 mb-1 print:p-1 print:mb-0.5">
                                    <div class="font-bold text-xs text-slate-900 leading-tight mb-0.5 print:text-[9px]">
                                        {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                    </div>
                                    @if($slot->type !== 'break')
                                        <div class="text-xs text-slate-600 leading-tight print:text-[8px]">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                    @endif
                                    @if($slot->room_number)
                                        <div class="text-xs text-slate-500 leading-tight print:text-[8px]">{{ $slot->room_number }}</div>
                                    @endif
                                </div>
                            @endforeach
                            
                            @if(getGroupSlots($slotsByDayTime, $day, $timeSlot, 'A')->isEmpty())
                                @if($editable)
                                    <div class="h-12 flex items-center justify-center print:h-8">
                                        <button type="button" 
                                                onclick="window.location.href='{{ $timetable ? route('hod.timetable.edit', $timetable) : '#' }}'"
                                                class="opacity-30 hover:opacity-100 transition-all border-2 border-dashed border-blue-300 hover:border-blue-400 hover:bg-blue-100 w-full h-full flex items-center justify-center group rounded-md print:hidden">
                                            <div class="text-center">
                                                <svg class="w-4 h-4 text-blue-400 group-hover:text-blue-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                <span class="text-xs text-blue-500 group-hover:text-blue-600">Add Group A</span>
                                            </div>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-12 flex items-center justify-center print:h-8">
                                        <span class="text-xs text-slate-400 print:text-[8px]">Free Period</span>
                                    </div>
                                @endif
                            @else
                                @if($editable)
                                    <div class="mt-1 print:hidden">
                                        <button type="button" 
                                                onclick="window.location.href='{{ $timetable ? route('hod.timetable.edit', $timetable) : '#' }}'"
                                                class="w-full py-1 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded border border-dashed border-blue-300 transition">
                                            + Add Another
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </td>
                        
                        {{-- Group B Cell --}}
                        <td class="border border-slate-300 p-1.5 align-top w-1/2 bg-green-50/30 print:p-1">
                            @foreach(getGroupSlots($slotsByDayTime, $day, $timeSlot, 'B') as $slot)
                                @php
                                    $subject = $subjects->firstWhere('id', $slot->subject_id);
                                    $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                @endphp
                                <div class="p-2 relative rounded border-l-4 border-l-green-500 bg-green-50 mb-1 print:p-1 print:mb-0.5">
                                    <div class="font-bold text-xs text-slate-900 leading-tight mb-0.5 print:text-[9px]">
                                        {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                    </div>
                                    @if($slot->type !== 'break')
                                        <div class="text-xs text-slate-600 leading-tight print:text-[8px]">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                    @endif
                                    @if($slot->room_number)
                                        <div class="text-xs text-slate-500 leading-tight print:text-[8px]">{{ $slot->room_number }}</div>
                                    @endif
                                </div>
                            @endforeach
                            
                            @if(getGroupSlots($slotsByDayTime, $day, $timeSlot, 'B')->isEmpty())
                                @if($editable)
                                    <div class="h-12 flex items-center justify-center print:h-8">
                                        <button type="button" 
                                                onclick="window.location.href='{{ $timetable ? route('hod.timetable.edit', $timetable) : '#' }}'"
                                                class="opacity-30 hover:opacity-100 transition-all border-2 border-dashed border-green-300 hover:border-green-400 hover:bg-green-100 w-full h-full flex items-center justify-center group rounded-md print:hidden">
                                            <div class="text-center">
                                                <svg class="w-4 h-4 text-green-400 group-hover:text-green-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                <span class="text-xs text-green-500 group-hover:text-green-600">Add Group B</span>
                                            </div>
                                        </button>
                                    </div>
                                @else
                                    <div class="h-12 flex items-center justify-center print:h-8">
                                        <span class="text-xs text-slate-400 print:text-[8px]">Free Period</span>
                                    </div>
                                @endif
                            @else
                                @if($editable)
                                    <div class="mt-1 print:hidden">
                                        <button type="button" 
                                                onclick="window.location.href='{{ $timetable ? route('hod.timetable.edit', $timetable) : '#' }}'"
                                                class="w-full py-1 text-xs text-green-600 hover:text-green-800 hover:bg-green-100 rounded border border-dashed border-green-300 transition">
                                            + Add Another
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
</div>
