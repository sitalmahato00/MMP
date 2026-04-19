<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Subject Marks Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #0f172a; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #8B0000; padding-bottom: 10px; margin-bottom: 12px; }
        .title { font-size: 20px; margin: 0; font-weight: 700; }
        .subtitle { margin: 4px 0 0 0; color: #475569; }
        .meta { margin-top: 8px; color: #334155; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #f8fafc; color: #64748b; text-transform: uppercase; font-size: 9px; letter-spacing: 0.08em; padding: 8px 6px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        tbody td { padding: 7px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .right { text-align: right; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Subject Marks Export</h1>
        <p class="subtitle">{{ $exam->name }} · {{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }}</p>
        <p class="meta">Generated at {{ bsDate($generatedAt, 'Y, F d h:i A') ?: '—' }} · Total rows {{ count($rows) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Student</th>
                <th>Program</th>
                <th class="right">IT</th>
                <th class="right">ET</th>
                <th class="right">IP</th>
                <th class="right">EP</th>
                <th class="right">Total</th>
                <th class="right">%</th>
                <th>Result</th>
                <th>Status</th>
                <th>Teacher</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>
                        <strong>{{ $row['subject_code'] }}</strong><br>
                        <span class="muted">{{ $row['subject_name'] }} · Sem {{ $row['semester'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $row['student_name'] }}</strong><br>
                        <span class="muted">{{ $row['student_no'] }} · Roll {{ $row['roll_number'] }}</span>
                    </td>
                    <td>{{ $row['program_name'] }}</td>
                    <td class="right">{{ $row['internal_theory'] ?? '—' }}</td>
                    <td class="right">{{ $row['external_theory'] ?? '—' }}</td>
                    <td class="right">{{ $row['internal_practical'] ?? '—' }}</td>
                    <td class="right">{{ $row['external_practical'] ?? '—' }}</td>
                    <td class="right">{{ $row['total_marks'] }}</td>
                    <td class="right">{{ $row['percentage_label'] }}</td>
                    <td>{{ $row['result_remark'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['teacher_name'] }}</td>
                    <td>{{ $row['updated_at_label'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="muted">No marks found for this exam.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
