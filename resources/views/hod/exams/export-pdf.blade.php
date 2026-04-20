<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $exam->name }} - Marks Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .college-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .college-address {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .exam-info {
            margin: 20px 0;
        }
        .exam-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .exam-info td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .exam-info .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 150px;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .marks-table th,
        .marks-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            font-size: 10px;
        }
        .marks-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .marks-table .student-name {
            text-align: left;
            font-weight: bold;
        }
        .pass { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .absent { color: orange; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="college-name">{{ $collegeName }}</div>
        <div class="college-address">{{ $collegeAddress }}</div>
        <h2>{{ $exam->name }} - Marks Report</h2>
    </div>

    <div class="exam-info">
        <table>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $exam->department->name }}</td>
                <td class="label">Academic Session:</td>
                <td>{{ $exam->academicSession->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Exam Type:</td>
                <td>{{ $exam->category_label }}</td>
                <td class="label">Exam Date:</td>
                <td>{{ bsDate($exam->start_date, 'F d, Y') }}</td>
            </tr>
            @if($exam->category === 'monthly_assessment')
            <tr>
                <td class="label">Full Marks:</td>
                <td>{{ $exam->assessment_full_marks ?? 100 }}</td>
                <td class="label">Pass Marks:</td>
                <td>{{ $exam->assessment_pass_marks ?? 40 }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Total Students:</td>
                <td>{{ $marks->count() }}</td>
                <td class="label">Export Date:</td>
                <td>{{ date('Y-m-d H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <table class="marks-table">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Subject</th>
                @if($exam->category === 'monthly_assessment')
                    <th>Attendance %</th>
                    <th>Obtained</th>
                    <th>Full Marks</th>
                    <th>Exam Attendance</th>
                @else
                    <th>Int. Theory</th>
                    <th>Ext. Theory</th>
                    <th>Int. Practical</th>
                    <th>Ext. Practical</th>
                    <th>Total</th>
                @endif
                <th>Result</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($marks as $index => $mark)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name">{{ $mark->student->user->name ?? 'N/A' }}</td>
                    <td>{{ $mark->student->program->name ?? 'N/A' }}</td>
                    <td>{{ $mark->subject->name ?? 'N/A' }}</td>
                    @if($exam->category === 'monthly_assessment')
                        <td>{{ number_format($mark->assessment_attendance_percent ?? 0, 1) }}%</td>
                        <td>{{ number_format($mark->assessment_obtained_marks ?? 0, 1) }}</td>
                        <td>{{ $exam->assessment_full_marks ?? 100 }}</td>
                        <td>{{ $mark->was_present_on_exam_date ? 'Present' : 'Absent' }}</td>
                    @else
                        <td>{{ number_format($mark->internal_theory_marks ?? 0, 1) }}</td>
                        <td>{{ number_format($mark->external_theory_marks ?? 0, 1) }}</td>
                        <td>{{ number_format($mark->internal_practical_marks ?? 0, 1) }}</td>
                        <td>{{ number_format($mark->external_practical_marks ?? 0, 1) }}</td>
                        <td>{{ number_format($mark->total_marks, 1) }}</td>
                    @endif
                    <td class="{{ strtolower($mark->result_remark) }}">{{ $mark->result_remark }}</td>
                    <td>{{ ucfirst($mark->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i:s') }} | {{ $collegeName }} - {{ $exam->department->name }}</p>
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>