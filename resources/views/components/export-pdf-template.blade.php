<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $config['title'] }}</title>
    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
            size: A4 landscape;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        /* ── LETTERHEAD ──────────────────────────── */
        .letterhead {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 4px;
        }
        .letterhead-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: left;
        }
        .letterhead-logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .letterhead-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .letterhead-right {
            display: table-cell;
            width: 120px;
            vertical-align: middle;
            text-align: right;
        }
        .college-name-main {
            font-size: 17px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .college-address-line {
            font-size: 8.5px;
            color: #333;
            margin-top: 2px;
        }
        .college-contact-line {
            font-size: 7.5px;
            color: #555;
            margin-top: 1px;
        }
        .affiliation-badge {
            font-size: 7px;
            color: #000;
            border: 1px solid #000;
            border-radius: 2px;
            padding: 1px 5px;
            display: inline-block;
            margin-top: 3px;
        }
        .dept-label {
            font-size: 8px;
            color: #222;
            margin-top: 4px;
            font-weight: bold;
        }

        /* ── TITLE BAND ──────────────────────────── */
        .title-band {
            border: 1px solid #000;
            border-left: 4px solid #000;
            padding: 4px 10px;
            margin: 6px 0 4px 0;
        }
        .title-band .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #000;
        }
        .title-band .report-subtitle {
            font-size: 8px;
            margin-top: 2px;
            color: #444;
        }

        /* ── METADATA GRID ───────────────────────── */
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            font-size: 8px;
        }
        .meta-grid td {
            padding: 3px 7px;
            border: 1px solid #999;
        }
        .meta-grid .meta-label {
            font-weight: bold;
            background: #f0f0f0;
            color: #000;
            white-space: nowrap;
            width: 1%;
        }
        .meta-grid .meta-value {
            color: #000;
        }

        /* ── DATA TABLE ──────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 7.5px;
        }
        .data-table thead tr {
            background: #e0e0e0;
        }
        .data-table th {
            border: 1px solid #333;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
            font-weight: bold;
            color: #000;
        }
        .data-table td {
            border: 1px solid #aaa;
            padding: 3.5px 3px;
            text-align: center;
            vertical-align: middle;
            color: #000;
        }
        .data-table tbody tr:nth-child(even) td {
            background: #f7f7f7;
        }
        .text-left  { text-align: left !important; }
        .text-right { text-align: right !important; }
        .student-name { font-weight: bold; font-size: 7.5px; }
        .num { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; text-align: right; }
        .result-pass   { font-weight: bold; }
        .result-fail   { font-weight: bold; }
        .result-absent { font-weight: bold; }
        .col-sn   { width: 22px; }
        .col-name { width: 90px; }
        .col-roll { width: 45px; }
        .col-prog { width: 75px; }
        .col-sem  { width: 28px; }
        .col-subj { width: 75px; }
        .col-code { width: 45px; }
        .col-mark { width: 36px; }
        .col-pct  { width: 36px; }
        .col-res  { width: 34px; }
        .col-stat { width: 40px; }
        .col-rem  { width: 65px; }

        /* ── SUMMARY BAR ─────────────────────────── */
        .summary-bar td {
            padding: 3px 8px;
            border: 1px solid #999;
            background: #f0f0f0;
            font-weight: bold;
            font-size: 7.5px;
        }

        /* ── SIGNATURES ──────────────────────────── */
        .signature-area {
            display: table;
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #555;
            padding-top: 10px;
        }
        .sig-cell { display: table-cell; width: 33.33%; text-align: center; }
        .sig-line { border-top: 1px solid #000; margin: 0 20px 3px 20px; }
        .sig-title { font-size: 7.5px; font-weight: bold; color: #000; }
        .sig-name  { font-size: 7px; color: #555; margin-top: 1px; }

        /* ── FOOTER ──────────────────────────────── */
        .doc-footer {
            margin-top: 8px;
            text-align: center;
            font-size: 7px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    {{-- ═══════════════ LETTERHEAD ═══════════════ --}}
    <div class="letterhead">
        {{-- Logo --}}
        <div class="letterhead-logo">
            @if($collegeLogo && file_exists($collegeLogo))
                <img src="{{ $collegeLogo }}" alt="Logo">
            @else
                <table style="width:56px;height:56px;border:2px solid #000;border-radius:2px;">
                    <tr><td style="text-align:center;vertical-align:middle;font-size:7px;color:#000;font-weight:bold;letter-spacing:1px;">LOGO</td></tr>
                </table>
            @endif
        </div>

        {{-- Centre: College name + address --}}
        <div class="letterhead-center">
            <div class="college-name-main">{{ strtoupper($collegeName) }}</div>
            @if($collegeAddress)
                <div class="college-address-line">{{ $collegeAddress }}</div>
            @endif
            @if($collegePhone || $collegeEmail)
                <div class="college-contact-line">
                    @if($collegePhone) Tel: {{ $collegePhone }} @endif
                    @if($collegePhone && $collegeEmail) &nbsp;|&nbsp; @endif
                    @if($collegeEmail) Email: {{ $collegeEmail }} @endif
                    @if($collegeWebsite) &nbsp;|&nbsp; {{ $collegeWebsite }} @endif
                </div>
            @endif
            @if($collegeAffiliation)
                <div style="margin-top:4px;">
                    <span class="affiliation-badge">Affiliated to: {{ $collegeAffiliation }}</span>
                </div>
            @endif
        </div>

        {{-- Right: Estd + Department --}}
        <div class="letterhead-right">
            @if($collegeEstd)
                <div class="estd-line">Est. {{ $collegeEstd }}</div>
            @endif
            @if(isset($config['department']))
                <div style="font-size:8px; color:#000; margin-top:4px; font-weight:bold;">{{ $config['department'] }}</div>
                <div style="font-size:7px; color:#555;">Department</div>
            @endif
        </div>
    </div>

    {{-- ═══════════════ TITLE BAND ═══════════════ --}}
    <div class="title-band">
        <div class="report-title">{{ $config['title'] }}</div>
        @if(isset($config['subtitle']))
            <div class="report-subtitle">{{ $config['subtitle'] }}</div>
        @endif
    </div>

    {{-- ═══════════════ METADATA ═══════════════ --}}
    @if(isset($config['metadata']) && count($config['metadata']) > 0)
    @php
        $metaItems = collect($config['metadata'])->map(fn($v, $k) => [$k, $v])->values()->chunk(2)->all();
    @endphp
    <table class="meta-grid">
        @foreach($metaItems as $pair)
        <tr>
            @foreach($pair as $item)
                <td class="meta-label">{{ $item[0] }}</td>
                <td class="meta-value">{{ $item[1] }}</td>
            @endforeach
            @if(count($pair) === 1)
                <td class="meta-label">Export Date</td>
                <td class="meta-value">{{ now()->format('Y-m-d H:i:s') }}</td>
            @endif
        </tr>
        @endforeach
    </table>
    @endif

    {{-- ═══════════════ DATA TABLE ═══════════════ --}}
    <table class="data-table">
        <thead>
            <tr>
                <th class="col-sn">S.N.</th>
                @foreach($config['columns'] as $key => $label)
                    @php
                        $thClass = match(true) {
                            in_array($key, ['student.user.name','name']) => 'col-name text-left',
                            in_array($key, ['student.roll_number','roll_number']) => 'col-roll',
                            in_array($key, ['student.program.name','program.name']) => 'col-prog',
                            $key === 'semester' => 'col-sem',
                            $key === 'subject.name' => 'col-subj',
                            $key === 'subject.code' => 'col-code',
                            $key === 'assessment_attendance_percent' => 'col-pct',
                            in_array($key, ['assessment_obtained_marks','internal_theory_marks','external_theory_marks',
                                'internal_practical_marks','external_practical_marks','total_marks',
                                'assessment_full_marks','assessment_pass_marks',
                                'ctevt_full_marks_internal_theory','ctevt_pass_marks_internal_theory',
                                'ctevt_full_marks_external_theory','ctevt_pass_marks_external_theory',
                                'ctevt_full_marks_internal_practical','ctevt_pass_marks_internal_practical',
                                'ctevt_full_marks_external_practical','ctevt_pass_marks_external_practical']) => 'col-mark',
                            $key === 'result_remark' => 'col-res',
                            $key === 'status' => 'col-stat',
                            $key === 'remarks' => 'col-rem text-left',
                            default => 'col-mark'
                        };
                    @endphp
                    <th class="{{ $thClass }}">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($config['data'] as $index => $row)
                <tr>
                    <td class="col-sn">{{ $index + 1 }}</td>
                    @foreach($config['columns'] as $key => $label)
                        @php
                            $value = is_array($row) ? ($row[$key] ?? '') : data_get($row, $key, '');

                            if ($key === 'assessment_attendance_percent')
                                $value = number_format((float)($value ?? 0), 1) . '%';
                            elseif (in_array($key, ['assessment_obtained_marks','internal_theory_marks','external_theory_marks',
                                'internal_practical_marks','external_practical_marks','total_marks',
                                'assessment_full_marks','assessment_pass_marks',
                                'ctevt_full_marks_internal_theory','ctevt_pass_marks_internal_theory',
                                'ctevt_full_marks_external_theory','ctevt_pass_marks_external_theory',
                                'ctevt_full_marks_internal_practical','ctevt_pass_marks_internal_practical',
                                'ctevt_full_marks_external_practical','ctevt_pass_marks_external_practical']))
                                $value = $value !== '' && $value !== null ? number_format((float)$value, 1) : '—';
                            elseif (in_array($key, ['was_present_on_exam_date','is_present']))
                                $value = $value ? 'Present' : 'Absent';
                            elseif ($key === 'status')
                                $value = ucfirst($value ?? '');

                            $resultClass = '';
                            if ($key === 'result_remark') {
                                $resultClass = match(strtolower((string)$value)) {
                                    'pass'    => 'result-pass',
                                    'fail'    => 'result-fail',
                                    'absent'  => 'result-absent',
                                    default   => ''
                                };
                            }

                            $tdClass = match(true) {
                                in_array($key, ['student.user.name','name'])      => 'col-name student-name text-left',
                                in_array($key, ['student.roll_number','roll_number']) => 'col-roll',
                                in_array($key, ['student.program.name','program.name']) => 'col-prog',
                                $key === 'semester'                               => 'col-sem',
                                $key === 'subject.name'                           => 'col-subj text-left',
                                $key === 'subject.code'                           => 'col-code',
                                $key === 'assessment_attendance_percent'          => 'col-pct num',
                                in_array($key, ['assessment_obtained_marks','internal_theory_marks','external_theory_marks',
                                    'internal_practical_marks','external_practical_marks','total_marks',
                                    'assessment_full_marks','assessment_pass_marks',
                                    'ctevt_full_marks_internal_theory','ctevt_pass_marks_internal_theory',
                                    'ctevt_full_marks_external_theory','ctevt_pass_marks_external_theory',
                                    'ctevt_full_marks_internal_practical','ctevt_pass_marks_internal_practical',
                                    'ctevt_full_marks_external_practical','ctevt_pass_marks_external_practical']) => 'col-mark num',
                                $key === 'result_remark'                          => "col-res $resultClass",
                                $key === 'status'                                 => 'col-stat',
                                $key === 'remarks'                                => 'col-rem text-left',
                                default => 'col-mark'
                            };
                        @endphp
                        <td class="{{ $tdClass }}">{{ $value ?: '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
            @if(count($config['data']) === 0)
                <tr><td colspan="{{ count($config['columns']) + 1 }}" style="text-align:center;color:#9ca3af;padding:12px;">No records found.</td></tr>
            @endif
        </tbody>
    </table>

    {{-- ═══════════════ SUMMARY ROW ═══════════════ --}}
    @if(count($config['data']) > 0)
    @php
        $total   = count($config['data']);
        $passed  = collect($config['data'])->where('result_remark', 'Pass')->count();
        $failed  = collect($config['data'])->where('result_remark', 'Fail')->count();
        $absent  = collect($config['data'])->where('result_remark', 'Absent')->count();
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
    @endphp
    <table style="width:100%;margin-top:6px;font-size:7.5px;border-collapse:collapse;">
        <tr>
            <td style="padding:3px 8px;background:#f0f0f0;border:1px solid #999;color:#000;font-weight:bold;">
                Total Students: {{ $total }}
            </td>
            <td style="padding:3px 8px;background:#f0f0f0;border:1px solid #999;color:#000;font-weight:bold;">
                Passed: {{ $passed }}
            </td>
            <td style="padding:3px 8px;background:#f0f0f0;border:1px solid #999;color:#000;font-weight:bold;">
                Failed: {{ $failed }}
            </td>
            <td style="padding:3px 8px;background:#f0f0f0;border:1px solid #999;color:#000;font-weight:bold;">
                Absent: {{ $absent }}
            </td>
            <td style="padding:3px 8px;background:#f0f0f0;border:1px solid #999;color:#000;font-weight:bold;">
                Pass Rate: {{ $passRate }}%
            </td>
        </tr>
    </table>
    @endif

    {{-- ═══════════════ SIGNATURES ═══════════════ --}}
    <div class="signature-area">
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-title">Exam Coordinator</div>
            <div class="sig-name">Signature &amp; Date</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-title">Head of Department</div>
            <div class="sig-name">Signature &amp; Date</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line"></div>
            <div class="sig-title">Principal</div>
            <div class="sig-name">Signature &amp; Date</div>
        </div>
    </div>

    {{-- ═══════════════ FOOTER ═══════════════ --}}
    <div class="doc-footer">
        <strong>{{ $collegeName }}</strong>
        &nbsp;|&nbsp; Generated: {{ now()->format('F d, Y \a\t H:i') }}
        @if(isset($config['department'])) &nbsp;|&nbsp; {{ $config['department'] }} Department @endif
        <br>
        This is a computer-generated official document. Any alteration renders it invalid.
    </div>

</body>
</html>