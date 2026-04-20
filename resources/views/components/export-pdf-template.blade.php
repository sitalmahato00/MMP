<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $config['title'] }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .college-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #2563eb;
        }
        .college-address {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 8px 0 3px 0;
            color: #1f2937;
        }
        .report-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .metadata {
            margin: 15px 0;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        .metadata table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .metadata td {
            padding: 3px 8px;
            border: 1px solid #cbd5e1;
        }
        .metadata .label {
            font-weight: bold;
            background-color: #e2e8f0;
            width: 120px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #374151;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #e5e7eb;
            font-weight: bold;
            font-size: 7px;
            color: #1f2937;
        }
        .data-table .text-left {
            text-align: left;
        }
        .data-table .student-name {
            text-align: left;
            font-weight: bold;
            max-width: 80px;
            font-size: 7px;
        }
        .data-table .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .pass { 
            color: #059669; 
            font-weight: bold; 
        }
        .fail { 
            color: #dc2626; 
            font-weight: bold; 
        }
        .absent { 
            color: #d97706; 
            font-weight: bold; 
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        
        /* Column width adjustments */
        .col-sn { width: 30px; }
        .col-name { width: 100px; }
        .col-program { width: 80px; }
        .col-subject { width: 80px; }
        .col-code { width: 50px; }
        .col-marks { width: 45px; }
        .col-result { width: 40px; }
        .col-status { width: 50px; }
        .col-remarks { width: 80px; }
        
        @media print {
            body { margin: 0; }
            .header { margin-bottom: 15px; }
            .footer { 
                position: fixed; 
                bottom: 0; 
                width: 100%; 
                background: white;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        @if($collegeLogo)
            <img src="{{ $collegeLogo }}" alt="College Logo" style="height: 60px; margin-bottom: 10px;">
        @endif
        <div class="college-name">{{ $collegeName }}</div>
        <div class="college-address">{{ $collegeAddress }}</div>
        <div class="report-title">{{ $config['title'] }}</div>
        @if(isset($config['subtitle']))
            <div class="report-subtitle">{{ $config['subtitle'] }}</div>
        @endif
    </div>

    @if(isset($config['metadata']) && count($config['metadata']) > 0)
        <div class="metadata">
            <table>
                @foreach(array_chunk($config['metadata'], 2, true) as $chunk)
                    <tr>
                        @foreach($chunk as $key => $value)
                            <td class="label">{{ $key }}:</td>
                            <td>{{ $value }}</td>
                        @endforeach
                        @if(count($chunk) == 1)
                            <td class="label">Export Date:</td>
                            <td>{{ date('Y-m-d H:i:s') }}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-sn">S.N.</th>
                @foreach($config['columns'] as $key => $label)
                    @php
                        $colClass = match($key) {
                            'student.user.name', 'name' => 'col-name',
                            'student.program.name', 'program.name' => 'col-program',
                            'subject.name' => 'col-subject',
                            'subject.code' => 'col-code',
                            'assessment_obtained_marks', 'internal_theory_marks', 'external_theory_marks',
                            'internal_practical_marks', 'external_practical_marks', 'total_marks',
                            'assessment_attendance_percent' => 'col-marks',
                            'result_remark' => 'col-result',
                            'status' => 'col-status',
                            'remarks' => 'col-remarks',
                            default => 'col-marks'
                        };
                    @endphp
                    <th class="{{ $colClass }} {{ in_array($key, ['student.user.name', 'name']) ? 'text-left' : '' }}">
                        {{ $label }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($config['data'] as $index => $row)
                <tr>
                    <td class="col-sn">{{ $index + 1 }}</td>
                    @foreach($config['columns'] as $key => $label)
                        @php
                            $value = '';
                            if (is_array($row)) {
                                $value = $row[$key] ?? '';
                            } elseif (is_object($row)) {
                                $value = data_get($row, $key, '');
                            }
                            
                            // Format specific values
                            if (in_array($key, ['assessment_attendance_percent'])) {
                                $value = number_format($value ?? 0, 1) . '%';
                            } elseif (in_array($key, ['assessment_obtained_marks', 'internal_theory_marks', 'external_theory_marks', 'internal_practical_marks', 'external_practical_marks', 'total_marks'])) {
                                $value = number_format($value ?? 0, 1);
                            } elseif ($key === 'was_present_on_exam_date') {
                                $value = $value ? 'Present' : 'Absent';
                            } elseif ($key === 'is_present') {
                                $value = $value ? 'Present' : 'Absent';
                            } elseif ($key === 'status') {
                                $value = ucfirst($value ?? '');
                            } elseif ($key === 'result_remark') {
                                $value = $value ?? '';
                            }

                            $colClass = match($key) {
                                'student.user.name', 'name' => 'col-name student-name text-left',
                                'student.program.name', 'program.name' => 'col-program',
                                'subject.name' => 'col-subject',
                                'subject.code' => 'col-code',
                                'assessment_obtained_marks', 'internal_theory_marks', 'external_theory_marks',
                                'internal_practical_marks', 'external_practical_marks', 'total_marks',
                                'assessment_attendance_percent' => 'col-marks number',
                                'result_remark' => 'col-result ' . strtolower($value),
                                'status' => 'col-status',
                                'remarks' => 'col-remarks text-left',
                                default => 'col-marks'
                            };
                        @endphp
                        
                        <td class="{{ $colClass }}">
                            {{ $value ?: 'N/A' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i:s') }} | {{ $collegeName }}</p>
        @if(isset($config['department']))
            <p>{{ $config['department'] }} Department</p>
        @endif
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>