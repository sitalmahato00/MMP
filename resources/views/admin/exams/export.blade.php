<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Exam Export</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 0;
            padding: 24px;
            background: #ffffff;
        }
        .header {
            border-bottom: 2px solid #8B0000;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }
        .subtitle {
            margin: 0;
            color: #475569;
            font-size: 12px;
        }
        .meta {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 16px;
            font-size: 11px;
            color: #334155;
        }
        .meta strong {
            color: #0f172a;
        }
        .summary {
            margin-top: 18px;
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }
        .summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            background: #f8fafc;
        }
        .summary-card .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #64748b;
            font-weight: 700;
        }
        .summary-card .value {
            margin-top: 8px;
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 10px;
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody td {
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .muted { color: #64748b; }
        .right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .green { background: #dcfce7; color: #166534; }
        .amber { background: #fef3c7; color: #92400e; }
        .red { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Exam Export</h1>
        <p class="subtitle">Managed examination overview with status, program assignments, and result workflow metrics.</p>
        <div class="meta">
            <div><strong>Generated at:</strong> {{ bsDate($generatedAt, 'Y, F d h:i A') }}</div>
            <div><strong>Total rows:</strong> {{ count($rows) }}</div>
            <div><strong>Session filter:</strong> {{ $filters['year'] ?? 'All' }}</div>
            <div><strong>Department filter:</strong> {{ $filters['department_id'] ?? 'All' }}</div>
            <div><strong>Program filter:</strong> {{ $filters['program_id'] ?? 'All' }}</div>
            <div><strong>Status filter:</strong> {{ $filters['status'] ?? 'All' }}</div>
            <div><strong>Type filter:</strong> {{ $filters['type'] ?? 'All' }}</div>
            <div><strong>Search:</strong> {{ $filters['search'] ?? '—' }}</div>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <div class="label">Rows</div>
            <div class="value">{{ count($rows) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Published</div>
            <div class="value">{{ collect($rows)->where('status_key', 'published')->count() }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Marks Completion</div>
            <div class="value">{{ number_format(collect($rows)->avg('marks_completion') ?? 0, 1) }}%</div>
        </div>
        <div class="summary-card">
            <div class="label">Marks Submitted</div>
            <div class="value">{{ number_format((int) collect($rows)->sum('submitted_marks_count')) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Exam</th>
                <th>Session / Department</th>
                <th>Programs</th>
                <th>Schedule</th>
                <th>Status</th>
                <th class="right">Marks</th>
                <th class="right">Submitted</th>
                <th class="right">Published</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $statusClass = match ($row['status_tone'] ?? 'slate') {
                        'green' => 'green',
                        'amber', 'yellow', 'orange' => 'amber',
                        'red', 'rose' => 'red',
                        default => 'amber',
                    };
                @endphp
                <tr>
                    <td>
                        <strong>{{ $row['name'] }}</strong><br>
                        <span class="muted">{{ $row['type_label'] }}</span>
                    </td>
                    <td>
                        {{ $row['exam']->academicSession?->name_bs ?: $row['exam']->academicSession?->name ?: '—' }}<br>
                        <span class="muted">{{ $row['department_label'] }}</span>
                    </td>
                    <td>
                        {{ $row['programs_label'] }}<br>
                        <span class="muted">{{ $row['semester_label'] }}</span>
                    </td>
                    <td>
                        {{ $row['start_date_label'] }} to {{ $row['end_date_label'] }}<br>
                        <span class="muted">Completion {{ number_format($row['marks_completion'], 1) }}%</span>
                    </td>
                    <td><span class="badge {{ $statusClass }}">{{ $row['status_label'] }}</span></td>
                    <td class="right">{{ number_format((int) ($row['marks_count'] ?? 0)) }}</td>
                    <td class="right">{{ number_format((int) ($row['submitted_marks_count'] ?? 0)) }}</td>
                    <td class="right">{{ number_format((int) ($row['published_marks_count'] ?? 0)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No exam records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Result exports are generated from the current exam workflow view. Values are presented in BS date format where applicable.
    </div>
</body>
</html>