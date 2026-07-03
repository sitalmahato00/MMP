<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk Student ID Cards — Print</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Reset ─────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    background: #111827;
    font-family: 'Montserrat', Arial, sans-serif;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

/* ── Screen Toolbar (fixed at top) ─────────────────── */
.toolbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 24px;
    background: #0f172a;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.toolbar-left { display: flex; align-items: center; gap: 14px; }
.toolbar h1 { font-size: 15px; font-weight: 700; color: #fff; }
.toolbar-count { font-size: 13px; color: #94a3b8; }
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; text-decoration: none; transition: opacity 0.15s;
}
.btn-red { background: #8B0000; color: #fff; }
.btn-red:hover { opacity: 0.88; }
.btn-outline { background: rgba(255,255,255,0.07); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.13); }
.btn-outline:hover { background: rgba(255,255,255,0.13); }

/* ── Page Preview (screen wrapper) ─────────────────── */
.preview-container {
    padding: 80px 24px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
}
.sheet-label {
    font-size: 11px; font-weight: 600; color: #64748b;
    text-transform: uppercase; letter-spacing: 1px;
    text-align: center;
}

/* ── Page sheet (screen dimensions) ────────────────── */
.page-sheet {
    position: relative;
    width: 288px;
    height: 458px;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}
.card-print-wrap {
    width: 288px;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
}

/* ── PRINT STYLES ──────────────────────────────────── */
@media print {
    @page {
        size: 54mm 86mm;
        margin: 0;
        padding: 0;
    }
    html, body {
        margin: 0;
        padding: 0;
        width: 54mm;
        height: 86mm;
        background: #fff;
        overflow: hidden;
    }
    body {
        display: block;
        position: relative;
    }
    .toolbar, .sheet-label {
        display: none !important;
    }
    .preview-container {
        padding: 0;
        gap: 0;
        display: block;
        background: #fff;
    }
    .page-sheet {
        position: relative !important;
        width: 54mm !important;
        height: 86mm !important;
        margin: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        page-break-after: always !important;
        break-after: page !important;
        overflow: hidden !important;
        display: block !important;
    }
    .page-sheet:last-child {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    .card-print-wrap {
        width: 288px !important;
        height: 458px !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        transform: scale(0.7083) !important;
        transform-origin: top left !important;
        display: flex !important;
        flex-direction: column !important;
    }
}
</style>
</head>
<body>

@php
    $collegeName = $settings['college_name']    ?? 'Manmohan Memorial Polytechnic';
    $address     = $settings['contact_address'] ?? 'Budhiganga-4, Morang, Koshi Province, Nepal';
    $phone       = $settings['contact_phone']   ?? '021-622058';
    $email       = $settings['contact_email']   ?? '';
    $principal   = $settings['principal_name']  ?? 'Principal';
    $headerColor = $cardConfig['header_color']  ?? '#8B0000';
    $validUpto   = $cardConfig['valid_upto']    ?? '';
    $total       = $students->count();
@endphp

{{-- Toolbar --}}
<div class="toolbar">
    <div class="toolbar-left">
        <h1>Bulk ID Card Print</h1>
        <span class="toolbar-count">{{ $total }} card(s) ready</span>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.id-cards.students.bulk-list') }}" class="btn btn-outline">
            ← Back
        </a>
        <button onclick="window.print()" class="btn btn-red">
            🖨 Print All Cards
        </button>
    </div>
</div>

{{-- Cards preview --}}
<div class="preview-container">
@foreach($students as $student)
@php
    $name      = $student->user?->name ?? '—';
    $program   = $student->program?->name ?? '—';
    $dob       = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo = $student->student_no ?? '—';
    $stdAddr   = $student->user?->address ?? null;
@endphp

<div class="sheet-label">Card {{ $loop->iteration }} of {{ $total }}</div>

<div class="page-sheet">
    <div class="card-print-wrap">
        {{-- ══ RED HEADER (120px) ══ --}}
        <div style="position:relative; background:{{ $headerColor }}; height:120px; box-sizing:border-box; flex-shrink:0; print-color-adjust:exact; -webkit-print-color-adjust:exact;">
            {{-- College name --}}
            <div style="position:absolute; top:10px; left:0; right:0; text-align:center; color:#fff; font-weight:800; font-size:18.8px; line-height:1.2; text-transform:uppercase; letter-spacing:0.45px; padding:0 16px;">
                {{ mb_strtoupper($collegeName) }}
            </div>
            {{-- Logo --}}
            <div style="position:absolute; top:40px; left:12px; width:42px; height:42px; border-radius:50%; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center; z-index:1; padding:2px; box-sizing:border-box;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width:100%; height:100%; object-fit:contain; display:block;">
                @endif
            </div>
        </div>

        {{-- ══ PHOTO overlapping header/body ══ --}}
        <div style="position:absolute; top:64px; left:88px; width:112px; height:112px; border-radius:50%; background:#e5e7eb; overflow:hidden; z-index:10; border:1px solid rgba(122,15,21,0.25); box-shadow:0 1px 6px rgba(0,0,0,0.12);">
            @if($student->photo_b64)
                <img src="{{ $student->photo_b64 }}" style="width:100%; height:100%; object-fit:cover; display:block;">
            @else
                <svg viewBox="0 0 112 112" width="112" height="112">
                    <rect width="112" height="112" fill="#e5e7eb"/>
                    <circle cx="56" cy="40" r="22" fill="#9ca3af"/>
                    <ellipse cx="56" cy="92" rx="34" ry="24" fill="#9ca3af"/>
                </svg>
            @endif
        </div>

        {{-- ══ WHITE BODY (padding-top: 62px) ══ --}}
        <div style="background:#fff; padding-top:62px; flex:1; display:flex; flex-direction:column;">
            <div style="flex:1; display:flex; flex-direction:column;">
                
                {{-- Name --}}
                <div style="text-align:center; padding:2px 14px 2px; font-size:14px; font-weight:700; color:#24378d; text-transform:uppercase; letter-spacing:0.2px; line-height:1.24;">
                    {{ mb_strtoupper($name) }}
                </div>

                {{-- Program --}}
                <div style="text-align:center; padding:2px 12px 0; font-size:11.7px; font-weight:700; color:#111; text-transform:uppercase; letter-spacing:0.15px; line-height:1.24;">
                    {{ mb_strtoupper($program) }}
                </div>

                {{-- Details --}}
                <div style="padding:8px 14px 0; font-size:13px; color:#1b1b1b; font-weight:600; line-height:1.4; text-align:center; word-break:break-word;">
                    <div>Student ID No.: <strong style="font-weight:600;">{{ $studentNo }}</strong></div>
                    <div style="margin-top:2px;">Date of Birth:- <strong style="font-weight:600;">{{ $dob ?: '—' }}</strong></div>
                    <div style="margin-top:2px;">Address:- <strong style="font-weight:600;">{{ $stdAddr ?: '—' }}</strong></div>
                    <div style="margin-top:2px;">Valid up to: <strong style="font-weight:600;">{{ $validUpto ?: '—' }}</strong></div>
                </div>

                {{-- Barcode + Signature row --}}
                <div style="display:flex; justify-content:space-between; align-items:flex-end; padding:4px 12px 0; gap:10px; margin-top:auto;">
                    
                    {{-- Scannable barcode using SVG format --}}
                    <div style="flex:1; min-width:0; display:flex; flex-direction:column; align-items:center;">
                        <svg id="barcode-{{ $student->id }}" style="height:32px; margin-bottom:2px;"></svg>
                        <div style="font-size:6.4px; text-align:center; font-family:'Montserrat',Arial,sans-serif; font-weight:700; letter-spacing:0.9px; color:#333;">
                            {{ $studentNo }}
                        </div>
                    </div>

                    {{-- Signature + Principal label --}}
                    <div style="flex-shrink:0; text-align:center; width:79px; font-size:11px; font-weight:700; color:#3f3f46;">
                        <div style="height:42px; display:flex; align-items:center; justify-content:center;">
                            @if($sigBase64)
                                <img src="{{ $sigBase64 }}" style="max-height:36px; object-fit:contain; display:block;"/>
                            @endif
                        </div>
                        <div>Principal</div>
                    </div>

                </div>
            </div>

            {{-- Red address footer --}}
            <div style="background:{{ $headerColor }}; color:#fff; text-align:center; font-size:11px; font-weight:500; line-height:1.58; letter-spacing:0.05px; padding:9px 4px 8px; flex-shrink:0; print-color-adjust:exact; -webkit-print-color-adjust:exact;">
                <div style="transform:scaleX(1.02); transform-origin:center center;">
                    {{ $address }}
                </div>
                <div style="transform:scaleX(1.02); transform-origin:center center;">
                    Ph: {{ $phone }}@if($email) &nbsp;| Email: {{ $email }}@endif
                </div>
            </div>

            {{-- Black identity strip --}}
            <div style="background:#1a1a1a; color:#fff; text-align:center; font-family:'Georgia','Times New Roman',serif; font-size:15px; font-weight:700; letter-spacing:0.45px; display:flex; align-items:center; justify-content:center; height:34px; padding:0 4px; text-transform:uppercase; line-height:1; flex-shrink:0; print-color-adjust:exact; -webkit-print-color-adjust:exact;">
                <span style="display:block; width:100%; white-space:nowrap; transform:scaleX(1.04); transform-origin:center center;">STUDENT IDENTITY CARD</span>
            </div>

        </div>
    </div>
</div>
@endforeach
</div>

{{-- ── Scripts for dynamic barcode generation ── --}}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
window.addEventListener('load', function () {
    // Generate barcodes for all students
    @foreach($students as $student)
    try {
        JsBarcode('#barcode-{{ $student->id }}', '{{ $student->student_no }}', {
            format: 'CODE128',
            width: 2,
            height: 50,
            displayValue: false,
            margin: 0
        });
    } catch(e) { console.error(e); }
    @endforeach

    // Auto-trigger print
    setTimeout(function () {
        window.print();
    }, 600);
});
</script>
</body>
</html>
