<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timetable - {{ $timetable->program->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 7px;
            line-height: 1.2;
            color: #000;
            padding: 5px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .college-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #1e40af;
        }
        
        .college-address {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .department-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .timetable-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .timetable-subtitle {
            font-size: 8px;
            color: #666;
        }
        
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 5px;
            font-size: 7px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 25%;
            padding: 1px 3px;
            font-weight: bold;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
        }
        
        .info-value {
            display: table-cell;
            width: 25%;
            padding: 1px 3px;
            border: 1px solid #d1d5db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }
        
        th {
            background: #1e293b;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 3px 2px;
            border: 1px solid #000;
            font-size: 7px;
        }
        
        th.group-header {
            background: #3b82f6;
            font-size: 7px;
        }
        
        th.group-a {
            background: #3b82f6;
        }
        
        th.group-b {
            background: #10b981;
        }
        
        td {
            border: 1px solid #666;
            padding: 2px 1px;
            vertical-align: top;
            font-size: 6px;
        }
        
        td.day-cell {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            width: 18px;
            font-size: 7px;
        }
        
        td.time-cell {
            background: #f8fafc;
            text-align: center;
            font-weight: 500;
            width: 50px;
            font-size: 6px;
            line-height: 1.1;
        }
        
        td.group-a-cell {
            background: #eff6ff;
        }
        
        td.group-b-cell {
            background: #f0fdf4;
        }
        
        td.common-cell {
            background: #faf5ff;
        }
        
        .slot {
            margin-bottom: 2px;
            padding: 2px;
            border-left: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }
        
        .slot.group-a {
            border-left-color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .slot.group-b {
            border-left-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }
        
        .slot.break {
            border-left-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        
        .slot-subject {
            font-weight: bold;
            font-size: 7px;
            margin-bottom: 1px;
            color: #000;
        }
        
        .slot-teacher {
            font-size: 6px;
            color: #374151;
            margin-bottom: 1px;
        }
        
        .slot-room {
            font-size: 5px;
            color: #6b7280;
        }
        
        .slot-type {
            display: inline-block;
            padding: 0px 2px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 2px;
            font-size: 5px;
            margin-top: 1px;
        }
        
        .free-period {
            text-align: center;
            color: #9ca3af;
            font-size: 6px;
            padding: 5px 0;
        }
        
        .footer {
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 6px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="college-name">{{ $collegeName }}</div>
        <div class="college-address">{{ $collegeAddress }}</div>
        <div class="department-name">{{ $department->name }}</div>
        <div class="timetable-title">CLASS ROUTINE</div>
        <div class="timetable-subtitle">
            {{ $timetable->program->name }} - Semester {{ $timetable->semester }}
            @if($timetable->section) • Section {{ $timetable->section }} @endif
            • Effective from {{ bsDate($timetable->effective_from, 'F d, Y') }}
        </div>
    </div>

    {{-- Info Section --}}
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Program:</div>
            <div class="info-value">{{ $timetable->program->name }} ({{ $timetable->program->code }})</div>
            <div class="info-label">Academic Session:</div>
            <div class="info-value">{{ $timetable->academicSession->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Semester:</div>
            <div class="info-value">{{ $timetable->semester }}</div>
            <div class="info-label">Total Slots:</div>
            <div class="info-value">{{ $timetable->slots->count() }}</div>
        </div>
    </div>

    {{-- Timetable --}}
    @php
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        // Group slots by day, time, and group
        $slotsByDayTime = collect($timetable->slots)->groupBy(function($slot) {
            $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
            $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
            return $slot->day_of_week . '-' . $startTime . '-' . $endTime;
        });
        
        // Get unique time slots from actual data
        $timeSlots = collect($timetable->slots)->map(function($slot) {
            $startTime = $slot->start_time instanceof \Carbon\Carbon ? $slot->start_time->format('H:i') : $slot->start_time;
            $endTime = $slot->end_time instanceof \Carbon\Carbon ? $slot->end_time->format('H:i') : $slot->end_time;
            return $startTime . '-' . $endTime;
        })->unique()->sort()->values()->toArray();

        $timeSlotsByDay = collect($days)->mapWithKeys(function($day) use ($timetable) {
            $dayTimeSlots = collect($timetable->slots)
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
        
        // Helper functions
        function formatTimePDF($time) {
            $h = (int)substr($time, 0, 2);
            $m = substr($time, 3, 2);
            $ampm = $h >= 12 ? 'PM' : 'AM';
            $displayHour = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
            return $displayHour . ':' . $m . ' ' . $ampm;
        }
        
        function getSlotsForCellPDF($slots, $day, $timeSlot) {
            $slotKey = $day . '-' . $timeSlot;
            return $slots->get($slotKey, collect());
        }
        
        function hasCommonSlotPDF($slots, $day, $timeSlot) {
            $cellSlots = getSlotsForCellPDF($slots, $day, $timeSlot);
            return $cellSlots->some(function($slot) {
                return empty($slot->group) || $slot->group === '';
            });
        }
        
        function getCommonSlotsPDF($slots, $day, $timeSlot) {
            $cellSlots = getSlotsForCellPDF($slots, $day, $timeSlot);
            return $cellSlots->filter(function($slot) {
                return empty($slot->group) || $slot->group === '';
            });
        }
        
        function getGroupSlotsPDF($slots, $day, $timeSlot, $group) {
            $cellSlots = getSlotsForCellPDF($slots, $day, $timeSlot);
            return $cellSlots->filter(function($slot) use ($group) {
                return $slot->group === $group;
            });
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">Day</th>
                <th rowspan="2" style="width: 60px;">Period</th>
                <th colspan="2" class="group-header">Subject Details</th>
            </tr>
            <tr>
                <th class="group-a" style="width: 45%;">Group A</th>
                <th class="group-b" style="width: 45%;">Group B</th>
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
                    <tr>
                        @if($timeIndex === 0)
                            <td class="day-cell" rowspan="{{ $totalTimeSlots }}">
                                {{ strtoupper(substr($day, 0, 3)) }}
                            </td>
                        @endif
                        
                        <td class="time-cell">
                            @php
                                [$start, $end] = explode('-', $timeSlot);
                            @endphp
                            {{ formatTimePDF($start) }}<br>-<br>{{ formatTimePDF($end) }}
                        </td>
                        
                        @if(hasCommonSlotPDF($slotsByDayTime, $day, $timeSlot))
                            <td class="common-cell" colspan="2">
                                @foreach(getCommonSlotsPDF($slotsByDayTime, $day, $timeSlot) as $slot)
                                    @php
                                        $subject = $subjects->firstWhere('id', $slot->subject_id);
                                        $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                    @endphp
                                    <div class="slot">
                                        <div class="slot-subject">
                                            {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                        </div>
                                        @if($slot->type !== 'break')
                                            <div class="slot-teacher">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                        @endif
                                        @if($slot->room_number)
                                            <div class="slot-room">Room: {{ $slot->room_number }}</div>
                                        @endif
                                        @if($slot->type && $slot->type !== 'theory')
                                            <span class="slot-type">{{ $slot->type }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        @else
                            <td class="group-a-cell">
                                @php $groupASlots = getGroupSlotsPDF($slotsByDayTime, $day, $timeSlot, 'A'); @endphp
                                @if($groupASlots->isNotEmpty())
                                    @foreach($groupASlots as $slot)
                                        @php
                                            $subject = $subjects->firstWhere('id', $slot->subject_id);
                                            $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                        @endphp
                                        <div class="slot group-a">
                                            <div class="slot-subject">
                                                {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                            </div>
                                            @if($slot->type !== 'break')
                                                <div class="slot-teacher">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                            @endif
                                            @if($slot->room_number)
                                                <div class="slot-room">{{ $slot->room_number }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="free-period">Free Period</div>
                                @endif
                            </td>
                            
                            <td class="group-b-cell">
                                @php $groupBSlots = getGroupSlotsPDF($slotsByDayTime, $day, $timeSlot, 'B'); @endphp
                                @if($groupBSlots->isNotEmpty())
                                    @foreach($groupBSlots as $slot)
                                        @php
                                            $subject = $subjects->firstWhere('id', $slot->subject_id);
                                            $teacher = $teachers->firstWhere('id', $slot->teacher_id);
                                        @endphp
                                        <div class="slot group-b">
                                            <div class="slot-subject">
                                                {{ $slot->type === 'break' ? 'BREAK' : ($subject?->name ?? 'Unknown Subject') }}
                                            </div>
                                            @if($slot->type !== 'break')
                                                <div class="slot-teacher">{{ $teacher?->user?->name ?? 'No Teacher' }}</div>
                                            @endif
                                            @if($slot->room_number)
                                                <div class="slot-room">{{ $slot->room_number }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="free-period">Free Period</div>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ date('F d, Y \a\t h:i A') }} | {{ $collegeName }}
    </div>
</body>
</html>
