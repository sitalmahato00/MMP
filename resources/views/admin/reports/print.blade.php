<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Report — Manmohan Memorial Polytechnic</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            font-size: 11px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Screen toolbar */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toolbar-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: #8B0000;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #6b0000;
        }
        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Document Sheet */
        .sheet-wrapper {
            padding: 24px;
            display: flex;
            justify-content: center;
        }
        .sheet {
            background: #ffffff;
            width: 100%;
            max-width: 1020px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border-radius: 8px;
        }

        /* Header Styling */
        .college-header {
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 2px solid #8B0000;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .college-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .college-meta {
            flex: 1;
            text-align: center;
        }
        .college-sub {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .college-name {
            font-size: 20px;
            font-weight: 900;
            color: #8B0000;
            letter-spacing: 0.5px;
            margin: 2px 0;
        }
        .college-address {
            font-size: 10px;
            color: #475569;
            font-weight: 500;
        }
        .report-badge-container {
            text-align: center;
            margin: 14px 0 16px;
        }
        .report-badge {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 18px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Metadata Grid */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 10.5px;
        }
        .meta-item strong {
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            display: block;
        }
        .meta-item span {
            color: #0f172a;
            font-weight: 700;
        }

        /* Clean Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9.5px;
            text-align: left;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }

        /* Signatures Block */
        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding-top: 20px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
        }
        .sig-line {
            width: 80%;
            margin: 36px auto 6px;
            border-top: 1px dashed #64748b;
        }
        .sig-title {
            font-size: 10px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sig-subtitle {
            font-size: 8.5px;
            color: #64748b;
        }

        .doc-footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }
            .toolbar {
                display: none !important;
            }
            .sheet-wrapper {
                padding: 0;
            }
            .sheet {
                box-shadow: none;
                border-radius: 0;
                padding: 10mm 12mm;
                max-width: 100%;
            }
            table.data-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    {{-- Top Action Toolbar --}}
    <header class="toolbar">
        <div class="toolbar-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Official Report Preview</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save as PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close Window
            </button>
        </div>
    </header>

    <div class="sheet-wrapper">
        <article class="sheet">
            {{-- Institutional College Header --}}
            <header class="college-header">
                <img src="{{ route('public.brand-logo') }}" alt="MMP Logo" class="college-logo" onerror="this.style.display='none'">
                <div class="college-meta">
                    <p class="college-sub">Manmohan Technical University Constituent</p>
                    <h1 class="college-name">MANMOHAN MEMORIAL POLYTECHNIC</h1>
                    <p class="college-address">Budhiganga-4, Hattimuda, Morang, Koshi Province, Nepal · Tel: 021-580000 · www.mmp.edu.np</p>
                </div>
            </header>

            {{-- Title Badge --}}
            <div class="report-badge-container">
                <span class="report-badge">
                    @if($type === 'attendance') Official Attendance & Participation Report
                    @elseif($type === 'students') Institutional Student Register
                    @elseif($type === 'exams') Examination Schedule & Evaluation Report
                    @elseif($type === 'marks') Examination Tabulation & Marks Sheet
                    @elseif($type === 'subjects') Academic Curriculum & Course Allocation
                    @else Institutional Academic Report
                    @endif
                </span>
            </div>

            {{-- Metadata Strip --}}
            <section class="meta-strip">
                <div class="meta-item">
                    <strong>Department</strong>
                    <span>{{ $department?->name ?? 'All Departments' }}</span>
                </div>
                <div class="meta-item">
                    <strong>Program</strong>
                    <span>{{ $program?->name ?? 'All Programs' }}</span>
                </div>
                <div class="meta-item">
                    <strong>Semester / Session</strong>
                    <span>{{ $semester ? "Semester {$semester}" : 'All Semesters' }} / {{ $session?->name ?? 'Current' }}</span>
                </div>
                <div class="meta-item">
                    <strong>Generated Date (BS)</strong>
                    <span>{{ bsDateTime(now()) }}</span>
                </div>
            </section>

            {{-- Data Table --}}
            <main>
                @if($type === 'attendance')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 12%;">Roll / Student No</th>
                                <th style="width: 24%;">Student Full Name</th>
                                <th style="width: 20%;">Program</th>
                                <th class="text-center" style="width: 6%;">Sem</th>
                                <th class="text-center" style="width: 8%;">Total</th>
                                <th class="text-center" style="width: 8%;">Present</th>
                                <th class="text-center" style="width: 8%;">Absent</th>
                                <th class="text-center" style="width: 10%;">Rate %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $st)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="font-mono font-bold">{{ $st->roll_number ?: $st->student_no }}</td>
                                    <td class="font-bold">{{ $st->user?->name ?? '—' }}</td>
                                    <td>{{ $st->program?->name ?? '—' }}</td>
                                    <td class="text-center">{{ $st->current_semester }}</td>
                                    <td class="text-center font-bold">{{ $st->total_sessions }}</td>
                                    <td class="text-center font-bold">{{ $st->present_sessions }}</td>
                                    <td class="text-center font-bold">{{ $st->absent_sessions }}</td>
                                    <td class="text-center font-black">{{ $st->attendance_rate }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center">No attendance entries recorded for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'students')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 14%;">Student No</th>
                                <th style="width: 12%;">Roll No</th>
                                <th style="width: 24%;">Student Full Name</th>
                                <th style="width: 24%;">Program & Dept</th>
                                <th class="text-center" style="width: 8%;">Semester</th>
                                <th class="text-center" style="width: 14%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $st)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="font-mono">{{ $st->student_no ?? '—' }}</td>
                                    <td class="font-mono font-bold">{{ $st->roll_number ?? '—' }}</td>
                                    <td class="font-bold">{{ $st->user?->name ?? '—' }}</td>
                                    <td>{{ $st->program?->name ?? '—' }}</td>
                                    <td class="text-center">{{ $st->current_semester }}</td>
                                    <td class="text-center uppercase font-bold">{{ $st->status ?? 'Active' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No students registered under this query.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'exams')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">Exam Title</th>
                                <th style="width: 25%;">Department</th>
                                <th style="width: 15%;">Academic Session</th>
                                <th class="text-center" style="width: 10%;">Type</th>
                                <th class="text-center" style="width: 15%;">Date (BS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $ex)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="font-bold">{{ $ex->name }}</td>
                                    <td>{{ $ex->department?->name ?? 'All Departments' }}</td>
                                    <td>{{ $ex->academicSession?->name ?? '—' }}</td>
                                    <td class="text-center uppercase font-bold">{{ $ex->type }}</td>
                                    <td class="text-center">{{ $ex->exam_date ? bsDate($ex->exam_date) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No examinations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'marks')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 18%;">Exam</th>
                                <th style="width: 12%;">Roll No</th>
                                <th style="width: 22%;">Student Full Name</th>
                                <th style="width: 20%;">Subject</th>
                                <th class="text-center" style="width: 8%;">Theory</th>
                                <th class="text-center" style="width: 8%;">Practical</th>
                                <th class="text-center" style="width: 8%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $mk)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $mk->exam?->name ?? '—' }}</td>
                                    <td class="font-mono font-bold">{{ $mk->student?->roll_number ?? '—' }}</td>
                                    <td class="font-bold">{{ $mk->student?->user?->name ?? '—' }}</td>
                                    <td>{{ $mk->subject?->name ?? '—' }}</td>
                                    <td class="text-center font-mono">{{ $mk->total_theory }}</td>
                                    <td class="text-center font-mono">{{ $mk->total_practical }}</td>
                                    <td class="text-center font-mono font-bold">{{ $mk->total_marks }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No marks entries recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'subjects')
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 15%;">Course Code</th>
                                <th style="width: 35%;">Subject Title</th>
                                <th style="width: 25%;">Program</th>
                                <th class="text-center" style="width: 10%;">Semester</th>
                                <th class="text-center" style="width: 10%;">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $idx => $sb)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="font-mono font-bold">{{ $sb->code }}</td>
                                    <td class="font-bold">{{ $sb->name }}</td>
                                    <td>{{ $sb->program?->name ?? '—' }}</td>
                                    <td class="text-center font-bold">{{ $sb->semester }}</td>
                                    <td class="text-center uppercase font-bold">{{ $sb->type }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No subjects allocated.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </main>

            {{-- Signature Authority Block --}}
            <footer class="signatures">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-title">Prepared By</div>
                    <div class="sig-subtitle">Academic Records Officer</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-title">Verified By</div>
                    <div class="sig-subtitle">Head of Department (HOD)</div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-title">Approved By</div>
                    <div class="sig-subtitle">Principal / Campus Chief</div>
                </div>
            </footer>

            <div class="doc-footer">
                <span>Manmohan Memorial Polytechnic Confidential Records &copy; {{ date('Y') }}</span>
                <span>Page 1 of 1 · Verified Institutional Document</span>
            </div>
        </article>
    </div>
</body>
</html>
