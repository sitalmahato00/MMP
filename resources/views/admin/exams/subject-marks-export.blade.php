<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $collegeName }} — Subject Marks</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 18px; }
        .header { margin-bottom: 14px; }
        .college-name { font-size: 18px; font-weight: 800; margin: 0 0 5px; }
        .report-title { font-size: 12px; font-weight: 700; margin: 0 0 2px; letter-spacing: 0.02em; }
        .report-subtitle { font-size: 10px; color: #475569; margin: 0 0 10px; }
        .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
        .meta-grid td { padding: 6px 8px; border: 1px solid #d1d5db; }
        .meta-label { width: 110px; font-weight: 700; background: #f8fafc; }
        .meta-value { color: #111827; }
        .marks-table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        .marks-table th,
        .marks-table td { padding: 6px 8px; border: 1px solid #d1d5db; }
        .marks-table th { background: #f8fafc; color: #475569; font-weight: 700; text-align: left; }
        .marks-table td { color: #111827; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="college-name">{{ $collegeName }}</div>
        <div class="report-title">Subject Marks</div>
        <div class="report-subtitle">{{ $exam->name }} · {{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td class="meta-label">Exam</td>
            <td class="meta-value">{{ $exam->name }}</td>
            <td class="meta-label">Semester</td>
            <td class="meta-value">{{ $subject?->semester ? 'Semester ' . $subject->semester : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Department</td>
            <td class="meta-value">{{ $exam->department?->name ?? 'N/A' }}</td>
            <td class="meta-label">Subject</td>
            <td class="meta-value">{{ $subject?->name ?? 'Subject' }}{{ $subject?->code ? ' (' . $subject->code . ')' : '' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Full Marks</td>
            <td class="meta-value">
                @if($isAssessment)
                    {{ $exam->assessment_full_marks !== null ? number_format($exam->assessment_full_marks, 2) : 'N/A' }}
                @else
                    {{ $subject?->total_full_marks !== null ? number_format($subject->total_full_marks, 2) : 'N/A' }}
                @endif
            </td>
            <td class="meta-label">Pass Marks</td>
            <td class="meta-value">
                @if($isAssessment)
                    {{ $exam->assessment_pass_marks !== null ? number_format($exam->assessment_pass_marks, 2) : 'N/A' }}
                @else
                    {{ $subject?->total_pass_marks !== null ? number_format($subject->total_pass_marks, 2) : 'N/A' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label">Format</td>
            <td class="meta-value">{{ $isAssessment ? 'Assessment' : 'CTEVT' }}</td>
            <td class="meta-label">Generated</td>
            <td class="meta-value">{{ bsDateTime($generatedAt, 'F d, Y h:i A') }}</td>
        </tr>
    </table>

    <table class="marks-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student</th>
                <th>Program</th>
                @if($isAssessment)
                    <th class="text-right">Attendance %</th>
                    <th class="text-right">Obtained</th>
                    <th class="text-right">Percentage</th>
                @else
                    <th class="text-right">Int. Theory</th>
                    <th class="text-right">Ext. Theory</th>
                    <th class="text-right">Int. Practical</th>
                    <th class="text-right">Ext. Practical</th>
                    <th class="text-right">Total</th>
                @endif
                <th class="text-center">Result</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['roll_number'] ?? $row['student_no'] ?? '—' }}</td>
                    <td>{{ $row['student_name'] ?? '—' }}</td>
                    <td>{{ $row['program_name'] ?? '—' }}</td>
                    @if($isAssessment)
                        <td class="text-right">{{ $row['attendance_percent_label'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['obtained'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['percentage_label'] ?? '—' }}</td>
                    @else
                        <td class="text-right">{{ $row['internal_theory'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['external_theory'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['internal_practical'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['external_practical'] ?? '—' }}</td>
                        <td class="text-right">{{ $row['total_marks'] ?? '—' }}</td>
                    @endif
                    <td class="text-center">{{ $row['result_remark'] ?? '—' }}</td>
                    <td class="text-center">{{ $row['status'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isAssessment ? 8 : 10 }}" class="muted text-center">No marks found for this exam.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
