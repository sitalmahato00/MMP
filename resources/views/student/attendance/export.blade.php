<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ $student->user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .student-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .student-info table {
            width: 100%;
        }
        .student-info td {
            padding: 3px 0;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-card {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .status-present { color: #059669; font-weight: bold; }
        .status-absent { color: #dc2626; font-weight: bold; }
        .status-late { color: #d97706; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <p>{{ $student->program->name ?? 'N/A' }} - Semester {{ $student->current_semester }}</p>
        <p>Generated on: {{ $exportDate }}</p>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td><strong>Student Name:</strong></td>
                <td>{{ $student->user->name }}</td>
                <td><strong>Student No:</strong></td>
                <td>{{ $student->student_no }}</td>
            </tr>
            <tr>
                <td><strong>Program:</strong></td>
                <td>{{ $student->program->name ?? 'N/A' }}</td>
                <td><strong>Semester:</strong></td>
                <td>{{ $student->current_semester }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td>{{ $student->department->name ?? 'N/A' }}</td>
                <td><strong>Overall Rate:</strong></td>
                <td><strong>{{ $attendanceRate }}%</strong></td>
            </tr>
        </table>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $attendances->count() }}</div>
            <div class="stat-label">Total Classes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $attendances->where('status', 'present')->count() }}</div>
            <div class="stat-label">Present</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $attendances->where('status', 'absent')->count() }}</div>
            <div class="stat-label">Absent</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $attendances->where('status', 'late')->count() }}</div>
            <div class="stat-label">Late</div>
        </div>
    </div>

    @if($subjectWise->count() > 0)
    <div class="section-title">Subject-wise Attendance</div>
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th class="text-center">Total Classes</th>
                <th class="text-center">Present</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Late</th>
                <th class="text-center">Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjectWise as $data)
            <tr>
                <td>
                    <strong>{{ $data['subject']->name }}</strong><br>
                    <small>{{ $data['subject']->code }}</small>
                </td>
                <td class="text-center">{{ $data['total'] }}</td>
                <td class="text-center status-present">{{ $data['present'] }}</td>
                <td class="text-center status-absent">{{ $data['absent'] }}</td>
                <td class="text-center status-late">{{ $data['late'] }}</td>
                <td class="text-center"><strong>{{ $data['rate'] }}%</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Detailed Attendance Records</div>
    <table>
        <thead>
            <tr>
                <th>Date (BS)</th>
                <th>Subject</th>
                <th>Teacher</th>
                <th>Period</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances->sortByDesc('attendanceSession.date') as $attendance)
            <tr>
                <td>{{ bsDate($attendance->attendanceSession->date, 'F d, Y') }}</td>
                <td>{{ $attendance->attendanceSession->subject->name }}</td>
                <td>{{ $attendance->attendanceSession->teacher->user->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $attendance->attendanceSession->period ?? 'N/A' }}</td>
                <td class="text-center status-{{ $attendance->status }}">
                    {{ ucfirst($attendance->status) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No attendance records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically from the MMP Student Portal.</p>
        <p>Report generated on {{ $exportDate }}</p>
    </div>
</body>
</html>