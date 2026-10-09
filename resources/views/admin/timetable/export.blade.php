<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable - {{ $timetable->program?->name }} (Sem {{ $timetable->semester }})</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; margin: 20px; color: #1e293b; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #0f172a; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 22px; color: #8B0000; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 16px; color: #334155; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; font-size: 12px; vertical-align: top; }
        th { background: #f8fafc; font-weight: bold; text-align: left; }
        .day-header { width: 12%; background: #f1f5f9; font-weight: bold; font-size: 13px; }
        .slot-card { margin-bottom: 8px; padding: 6px; background: #f8fafc; border-left: 3px solid #8B0000; border-radius: 4px; }
        .slot-time { font-weight: bold; color: #8B0000; font-size: 11px; }
        .slot-subject { font-weight: bold; font-size: 12px; margin: 2px 0; }
        .slot-meta { font-size: 11px; color: #64748b; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #8B0000; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Print / Save PDF
        </button>
    </div>

    <div class="header">
        <h1>Manmohan Memorial Polytechnic</h1>
        <h2>Class Timetable Schedule</h2>
        <p>
            <strong>Program:</strong> {{ $timetable->program?->name }} &bull;
            <strong>Semester:</strong> {{ $timetable->semester }} &bull;
            <strong>Section:</strong> {{ $timetable->section ?? 'All' }} &bull;
            <strong>Session:</strong> {{ $timetable->academicSession?->name }}
        </p>
        <p>Effective From: {{ $timetable->effective_from ? bsDate($timetable->effective_from, 'F d, Y') . ' (' . bsDate($timetable->effective_from) . ' BS)' : '—' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="day-header">Day</th>
                <th>Scheduled Periods</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $dayNum => $dayName)
                @php
                    $slots = $timetable->slots->where('day_of_week', $dayNum)->sortBy('start_time');
                @endphp
                <tr>
                    <td class="day-header">{{ $dayName }}</td>
                    <td>
                        @if($slots->isNotEmpty())
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @foreach($slots as $slot)
                                    <div class="slot-card" style="min-width: 180px;">
                                        <div class="slot-time">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</div>
                                        <div class="slot-subject">{{ $slot->subject?->name }} ({{ $slot->subject?->code }})</div>
                                        <div class="slot-meta">
                                            {{ $slot->teacher?->user?->name ?? 'TBA' }}
                                            @if($slot->room_no) &bull; Room: {{ $slot->room_no }} @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <em style="color: #94a3b8; font-size: 11px;">No classes scheduled</em>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
