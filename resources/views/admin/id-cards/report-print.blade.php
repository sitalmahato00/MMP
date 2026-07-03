<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student ID Card Print Report</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', 'Trebuchet MS', Arial, Helvetica, sans-serif;
            background: #64748b;
            color: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Screen-only toolbar ──────────────────────── */
        .no-print {
            position: fixed; top: 0; left: 0; right: 0; z-index: 300;
            display: flex; align-items: center; justify-content: space-between;
            padding: 9px 20px;
            background: #0f172a;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .no-print-left { display: flex; align-items: center; gap: 10px; color: #fff; font-size: 13px; font-weight: 700; }
        .no-print-count { color: #94a3b8; font-weight: 400; font-size: 12px; }
        .no-print-btns { display: flex; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 16px; border-radius: 7px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            border: none; text-decoration: none; transition: opacity .15s;
        }
        .btn-red  { background: #8B0000; color: #fff; }
        .btn-red:hover { opacity: 0.88; }
        .btn-gray { background: rgba(255,255,255,0.1); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.14); }
        .btn-gray:hover { background: rgba(255,255,255,0.16); }

        /* ── Page wrapper (screen preview) ───────────── */
        .page-wrap {
            padding: 60px 20px 40px;
            display: flex; flex-direction: column; align-items: center; gap: 20px;
        }

        /* ── A4 white sheet ─────────────────────────── */
        .a4 {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            box-shadow: 0 4px 30px rgba(0,0,0,0.4);
            padding: 15mm 14mm 14mm;
        }

        /* ══ HEADER ══════════════════════════════════════════
           Logo centered, college name centered below it
        ══════════════════════════════════════════════════════ */
        .hdr {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        /* Logo: 72px circle, red ring */
        .hdr-logo {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            border-radius: 50%;
            border: 2.5px solid #8B0000;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }
        .hdr-logo img  { width: 100%; height: 100%; object-fit: contain; display: block; }

        /* College name – moderate size, centered */
        .hdr-text {
            text-align: center;
        }
        .hdr-college {
            font-size: 18pt;
            font-weight: 600;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.15;
        }
        .hdr-sub {
            font-size: 10pt;
            color: #6b7280;
            margin-top: 5px;
            letter-spacing: 0.2px;
        }
        .hdr-gen {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 2px;
        }

        /* Horizontal rule under header */
        .hdr-rule {
            width: 100%;
            height: 1.5px;
            background: #8B0000;
            border: none;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        /* ══════════════════════════════════════════════
           TABLE — matches reference exactly
        ══════════════════════════════════════════════ */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            table-layout: auto;
        }

        /* Header row */
        thead tr {
            background: #f1f5f9;
        }
        th {
            padding: 5px 7px;
            text-align: left;
            font-weight: 700;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            white-space: nowrap;
            font-size: 8pt;
            letter-spacing: 0.1px;
        }

        /* Data rows */
        td {
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            color: #111;
            vertical-align: top;
            line-height: 1.35;
        }

        /* S.No */
        th:nth-child(1), td:nth-child(1) {
            text-align: center;
            white-space: nowrap;
        }
        /* Student ID */
        th:nth-child(2), td:nth-child(2) {
            white-space: nowrap;
            font-family: 'Courier New', monospace;
            font-size: 7.8pt;
        }
        /* DOB */
        th:nth-child(4), td:nth-child(4) { white-space: nowrap; }
        /* Phone */
        th:nth-child(5), td:nth-child(5) { white-space: nowrap; }
        /* Print Date */
        th:nth-child(8), td:nth-child(8) { white-space: nowrap; }

        /* No row striping — reference has plain white rows */
        tbody tr { background: #fff; }

        /* Light bottom border per row only (reference style) */
        tbody td { border-top: none; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        thead th { border: 1px solid #cbd5e1; }

        /* Department in dark red bold */
        .dept { color: #8B0000; font-weight: 700; }

        /* ── Footer ─────────────────────────────────── */
        .rpt-footer {
            margin-top: 10mm;
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 3mm;
        }

        /* ══ PRINT STYLES ══════════════════════════════ */
        @media print {
            @page { size: A4 portrait; margin: 10mm 10mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .page-wrap { padding: 0; background: #fff; }
            .a4 { width: 100%; min-height: auto; box-shadow: none; padding: 0; }
            .hdr-college { font-size: 16pt; }
            table { font-size: 8pt; }
            th, td { padding: 4px 6px; }
            thead tr { background: #f1f5f9 !important; }
            .dept { color: #8B0000 !important; }
        }
    </style>
</head>
<body>

{{-- ══ Screen toolbar ══════════════════════════════════ --}}
<div class="no-print">
    <div class="no-print-left">
        Student ID Card Print Report
        <span class="no-print-count">{{ $students->count() }} student(s)</span>
    </div>
    <div class="no-print-btns">
        <a href="{{ route('admin.id-cards.students.reports') }}" class="btn btn-gray">← Back</a>
        <button onclick="window.print()" class="btn btn-red">🖨 Print / Save PDF</button>
    </div>
</div>

{{-- ══ A4 document ══════════════════════════════════════ --}}
<div class="page-wrap">
<div class="a4">

    {{-- ── Header ──────────────────────────────────── --}}
    <div class="hdr">
        <div class="hdr-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @else
                {{-- Star icon fallback matching reference --}}
                <svg viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="36" cy="36" r="34" fill="#fff8f8" stroke="#8B0000" stroke-width="2"/>
                    <polygon
                        points="36,10 41.5,27 59,27 45,37.5 50.5,54 36,44 21.5,54 27,37.5 13,27 30.5,27"
                        fill="none" stroke="#8B0000" stroke-width="2.2" stroke-linejoin="round"/>
                </svg>
            @endif
        </div>
        <div class="hdr-text">
            <div class="hdr-college">{{ $settings['college_name'] ?? 'Manmohan Memorial Polytechnic' }}</div>
            <div class="hdr-sub">Student ID Card Print Report</div>
            <div class="hdr-gen">Generated on {{ $printDate }}</div>
        </div>
    </div>
    <div class="hdr-rule"></div>

    {{-- ── Table ───────────────────────────────────── --}}
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Student&nbsp;ID</th>
                <th>Full Name</th>
                <th>DOB (BS)</th>
                <th>Phone</th>
                <th>Department</th>
                <th>Address</th>
                <th>Print Date&nbsp;(BS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $i => $student)
            @php
                $user  = $student->user;
                $dept  = $student->department;
                $dob   = ($user && $user->dob)     ? bsDate($user->dob) : '—';
                $phone = ($user && $user->phone)   ? $user->phone       : '—';
                $addr  = ($user && $user->address) ? $user->address     : '—';
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $student->student_no ?? '—' }}</td>
                <td>{{ $user->name ?? '—' }}</td>
                <td>{{ $dob }}</td>
                <td>{{ $phone }}</td>
                <td><span class="dept">{{ $dept->name ?? '—' }}</span></td>
                <td>{{ $addr }}</td>
                <td>{{ $printDate }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center;color:#9ca3af;padding:14px;">No students found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Footer ─────────────────────────────────── --}}
    <div class="rpt-footer">
        {{ $settings['college_name'] ?? 'Manmohan Memorial Polytechnic' }} &bull;
        {{ $settings['contact_address'] ?? 'Kathmandu, Nepal' }} &bull;
        Printed on {{ $printDate }}
    </div>

</div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 600);
    });
</script>
</body>
</html>
