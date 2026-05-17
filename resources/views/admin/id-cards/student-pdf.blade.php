<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #f1f5f9;
        }

        .page-wrap {
            padding: 20px;
        }

        /* Grid: 2 cards per row */
        .card-grid {
            width: 100%;
        }
        .card-row {
            width: 100%;
            margin-bottom: 16px;
        }
        .card-row:after {
            content: '';
            display: table;
            clear: both;
        }
        .card-wrap {
            float: left;
            width: 48%;
            margin-right: 4%;
        }
        .card-wrap:last-child {
            margin-right: 0;
        }

        /* ID Card */
        .id-card {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: 1.5px solid #cbd5e1;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* Header stripe */
        .card-header {
            background: #8B0000;
            padding: 8px 10px;
            text-align: center;
            color: #fff;
        }
        .card-header .college-logo {
            height: 30px;
            width: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.6);
            vertical-align: middle;
            margin-right: 6px;
        }
        .card-header .college-name {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.03em;
            display: inline-block;
            vertical-align: middle;
            line-height: 1.3;
        }
        .card-header .affiliation {
            font-size: 8px;
            opacity: 0.85;
            display: block;
            margin-top: 2px;
        }

        /* Card type label */
        .card-type {
            background: #b91c1c;
            color: #fff;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 3px 0;
        }

        /* Body */
        .card-body {
            padding: 10px;
            display: table;
            width: 100%;
        }
        .photo-col {
            display: table-cell;
            width: 60px;
            vertical-align: top;
            padding-right: 8px;
        }
        .photo-col img {
            width: 56px;
            height: 68px;
            object-fit: cover;
            border-radius: 4px;
            border: 1.5px solid #e2e8f0;
        }
        .photo-placeholder {
            width: 56px;
            height: 68px;
            border-radius: 4px;
            background: #e2e8f0;
            border: 1.5px solid #cbd5e1;
            text-align: center;
            padding-top: 18px;
            font-size: 8px;
            color: #94a3b8;
        }
        .info-col {
            display: table-cell;
            vertical-align: top;
        }
        .student-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 5px;
        }
        .info-row {
            margin-bottom: 2px;
            font-size: 8.5px;
            color: #475569;
            line-height: 1.4;
        }
        .info-label {
            font-weight: bold;
            color: #334155;
            display: inline-block;
            width: 64px;
        }
        .info-value {
            color: #1e293b;
        }

        /* Footer */
        .card-footer {
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 5px 10px;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 8px;
            color: #64748b;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            font-size: 8px;
            color: #64748b;
        }
        .validity-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border-radius: 3px;
            padding: 1px 5px;
            font-weight: bold;
            font-size: 8px;
        }
        .student-no {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.08em;
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="card-grid">
        @php $chunks = $students->chunk(2); @endphp
        @foreach ($chunks as $row)
        <div class="card-row">
            @foreach ($row as $student)
            <div class="card-wrap">
                <div class="id-card">
                    {{-- Header --}}
                    <div class="card-header">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" class="college-logo" alt="Logo">
                        @endif
                        <span class="college-name">
                            {{ $settings['college_name'] ?? 'Manmohan Memorial Polytechnic' }}
                            @if (!empty($settings['college_affiliation']))
                                <span class="affiliation">{{ $settings['college_affiliation'] }}</span>
                            @endif
                        </span>
                    </div>

                    {{-- Type label --}}
                    <div class="card-type">Student Identity Card</div>

                    {{-- Body --}}
                    <div class="card-body">
                        <div class="photo-col">
                            @if ($student->photo_b64)
                                <img src="{{ $student->photo_b64 }}" alt="Photo">
                            @else
                                <div class="photo-placeholder">No Photo</div>
                            @endif
                        </div>
                        <div class="info-col">
                            <div class="student-name">{{ $student->user?->name ?? '—' }}</div>

                            <div class="info-row">
                                <span class="info-label">Student No:</span>
                                <span class="info-value student-no">{{ $student->student_no ?? '—' }}</span>
                            </div>
                            @if ($student->registration_number)
                            <div class="info-row">
                                <span class="info-label">Reg. No:</span>
                                <span class="info-value">{{ $student->registration_number }}</span>
                            </div>
                            @endif
                            <div class="info-row">
                                <span class="info-label">Program:</span>
                                <span class="info-value">{{ $student->program?->name ?? '—' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Department:</span>
                                <span class="info-value">{{ $student->department?->name ?? '—' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Semester:</span>
                                <span class="info-value">
                                    @if ($student->current_semester)
                                        @php
                                            $n = $student->current_semester;
                                            $sfx = match(true) {
                                                $n % 100 >= 11 && $n % 100 <= 13 => 'th',
                                                $n % 10 === 1 => 'st',
                                                $n % 10 === 2 => 'nd',
                                                $n % 10 === 3 => 'rd',
                                                default => 'th',
                                            };
                                        @endphp
                                        {{ $n . $sfx }} Semester
                                    @else —
                                    @endif
                                </span>
                            </div>
                            @if ($student->section)
                            <div class="info-row">
                                <span class="info-label">Section:</span>
                                <span class="info-value">{{ $student->section }}</span>
                            </div>
                            @endif
                            @if ($student->blood_group)
                            <div class="info-row">
                                <span class="info-label">Blood Group:</span>
                                <span class="info-value">{{ $student->blood_group }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer">
                        <div class="footer-left">
                            Session: {{ $student->academicSession?->name ?? '—' }}
                        </div>
                        <div class="footer-right">
                            <span class="validity-badge">Valid: {{ now()->format('Y') }}/{{ now()->addYear()->format('Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
</body>
</html>
