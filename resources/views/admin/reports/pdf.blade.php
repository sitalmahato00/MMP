<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin: 0 0 8px 0; font-size: 18px; }
        p { margin: 0 0 16px 0; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; text-transform: uppercase; letter-spacing: 0.06em; font-size: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Generated at {{ bsDate($generatedAt, 'Y-m-d H:i') }} BS</p>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Attendance %</th>
                <th>Marks / Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['student_name'] ?? '-' }}</td>
                    <td>{{ $row['attendance'] ?? '-' }}</td>
                    <td>{{ $row['marks_grade'] ?? '-' }}</td>
                    <td>{{ $row['status'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
