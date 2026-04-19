<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        .header { margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #8B0000; }
        h1 { margin: 0; font-size: 24px; }
        .subtitle { color: #64748b; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; letter-spacing: 0.12em; color: #475569; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Administrative Staff Export</h1>
        <div class="subtitle">Generated {{ bsDate(now(), 'Y F d') }} {{ now()->format('h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Staff</th>
                <th>Employment</th>
                <th>Contact</th>
                <th>Visibility</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $member)
                <tr>
                    <td>
                        <strong>{{ $member->name }}</strong><br>
                        <span class="muted">{{ $member->staff_code }}</span><br>
                        <span class="muted">{{ $member->designation }}</span>
                    </td>
                    <td>
                        {{ $member->department ?: 'General Administration' }}<br>
                        {{ ucfirst(str_replace('_', ' ', $member->employment_type ?? 'unspecified')) }}<br>
                        {{ ucfirst($member->employment_status ?? 'active') }}
                    </td>
                    <td>
                        {{ $member->email ?: '—' }}<br>
                        {{ $member->phone ?: '—' }}
                    </td>
                    <td>
                        Public: {{ $member->public_visible ? 'Yes' : 'No' }}<br>
                        Featured: {{ $member->featured ? 'Yes' : 'No' }}<br>
                        Docs: {{ $member->documents_count ?? 0 }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No staff records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>