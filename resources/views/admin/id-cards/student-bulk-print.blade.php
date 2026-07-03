<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk Student ID Cards — Print</title>
<style>
/* ── Reset ───────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html, body { background: #1e2030; }
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ── Toolbar (screen only) ───────────────────────── */
.toolbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 200;
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 24px;
    background: #0f1117;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.toolbar-left { display: flex; align-items: center; gap: 14px; }
.toolbar h1  { font-size: 15px; font-weight: 700; color: #fff; white-space: nowrap; }
.t-count     { font-size: 13px; color: #94a3b8; }
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    border: none; text-decoration: none; transition: opacity .15s;
}
.btn-red   { background: #8B0000; color: #fff; }
.btn-red:hover   { opacity: .88; }
.btn-ghost { background: rgba(255,255,255,0.08); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.13); }
.btn-ghost:hover { background: rgba(255,255,255,0.14); }

/* ── Screen preview ──────────────────────────────── */
.preview {
    padding: 70px 0 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
}
.c-label {
    font-size: 10px; font-weight: 600; letter-spacing: 1px;
    color: #64748b; text-transform: uppercase; text-align: center;
}

/* ── Card shell
       Single card: 54 mm W × 86 mm H
       Screen scale: 1 mm = 3.78 px → 204 px × 325 px
       pt → px:  1 pt = 1.333 px at 96 dpi
       Card is 153 pt wide = 204 px ✓
    ─────────────────────────────────────────────── */
.card-shell {
    width: 204px;       /* 54 mm */
    height: 325px;      /* 86 mm */
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 6pt;     /* base = same as DomPDF template */
    position: relative;
    display: flex;
    flex-direction: column;
    background: #fff;
}

/* ── Print ───────────────────────────────────────── */
@media print {
    @page {
        size: 54mm 86mm;   /* portrait card */
        margin: 0;
    }
    html, body { background: #fff; }
    .toolbar   { display: none !important; }
    .preview   { padding: 0; gap: 0; display: block; }
    .c-label   { display: none; }

    .card-shell {
        width: 54mm;
        height: 86mm;
        border-radius: 0;
        box-shadow: none;
        page-break-after: always;
        break-after: page;
        overflow: hidden;
    }
    .card-shell:last-child {
        page-break-after: avoid;
        break-after: avoid;
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
    $barcodeType = $cardConfig['barcode_type']  ?? 'both';
    $validUpto   = $cardConfig['valid_upto']    ?? '';
    $total       = $students->count();
@endphp

{{-- Toolbar --}}
<div class="toolbar">
    <div class="toolbar-left">
        <h1>Bulk ID Card Print</h1>
        <span class="t-count">{{ $total }} card(s)</span>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('admin.id-cards.students.bulk-list') }}" class="btn btn-ghost">← Back</a>
        <button onclick="window.print()" class="btn btn-red">🖨 Print All Cards</button>
    </div>
</div>

{{-- Cards --}}
<div class="preview">
@foreach($students as $student)
@php
    $name      = $student->user?->name ?? '—';
    $program   = $student->program?->name ?? '—';
    $dob       = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo = $student->student_no ?? '—';
    $stdAddr   = $student->user?->address ?? null;
    $qrBase64  = $qrMap[$student->id] ?? null;

    /* Barcode bars — identical algorithm to student-card-pdf.blade.php */
    $barcodeHtml = '';
    $bStr = str_pad($studentNo, 16, '0');
    for ($bi = 0; $bi < 52; $bi++) {
        $cv  = ord($bStr[$bi % strlen($bStr)]) + $bi * 7;
        $bg  = ($cv % 3 !== 2) ? '#000000' : '#ffffff';
        $w   = ($cv % 5 === 0) ? '3pt' : (($cv % 4 === 0) ? '1pt' : '2pt');
        $barcodeHtml .= "<span style=\"display:inline-block;background:{$bg};width:{$w};height:15pt;vertical-align:top;\"></span>";
    }
@endphp

<div class="c-label">Card {{ $loop->iteration }} of {{ $total }}</div>

{{-- ══════════════════════════════════════════════════
     Card — 100% identical inline-style structure to
     student-card-pdf.blade.php, using pt units so the
     browser renders it the same way as DomPDF.
     Card = 153pt × 243pt  (54mm × 86mm at 72dpi/pt)
══════════════════════════════════════════════════ --}}
<div class="card-shell">

    {{-- ── HEADER (71pt tall) ───────────────────── --}}
    <div style="background:{{ $headerColor }};height:71pt;position:relative;flex-shrink:0;">
        {{-- College name --}}
        <div style="position:absolute;top:5pt;left:0;right:0;text-align:center;color:#fff;font-weight:800;font-size:10pt;line-height:1.2;text-transform:uppercase;letter-spacing:0.3pt;padding:0 8pt;">
            {{ mb_strtoupper($collegeName) }}
        </div>
        {{-- Logo (left, 22pt circle) --}}
        <div style="position:absolute;top:22pt;left:6pt;width:22pt;height:22pt;border-radius:50%;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;padding:1pt;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="width:100%;height:100%;object-fit:contain;display:block;">
            @endif
        </div>
    </div>

    {{-- ── PHOTO CIRCLE (60pt, overlapping header) ─ --}}
    {{-- top=33pt, left=(153-60)/2=46.5≈47pt, same as single PDF --}}
    <div style="position:absolute;top:33pt;left:47pt;width:60pt;height:60pt;border-radius:50%;background:#e5e7eb;overflow:hidden;z-index:10;border:0.5pt solid rgba(122,15,21,0.25);box-shadow:0 2px 6px rgba(0,0,0,0.18);">
        @if($student->photo_b64)
            <img src="{{ $student->photo_b64 }}" style="width:100%;height:100%;object-fit:cover;display:block;">
        @else
            <svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
                <rect width="60" height="60" fill="#e2e8f0"/>
                <circle cx="30" cy="22" r="12" fill="#94a3b8"/>
                <ellipse cx="30" cy="52" rx="19" ry="12" fill="#94a3b8"/>
            </svg>
        @endif
    </div>

    {{-- ── WHITE BODY ────────────────────────────── --}}
    <div style="background:#fff;padding-top:33pt;flex:1;display:flex;flex-direction:column;">

        {{-- Name + Program --}}
        <div style="text-align:center;padding:2pt 8pt 1pt;">
            <div style="font-size:7.5pt;font-weight:bold;color:#24378d;text-transform:uppercase;letter-spacing:0.15pt;line-height:1.24;">{{ mb_strtoupper($name) }}</div>
            <div style="font-size:6pt;font-weight:bold;color:#111;text-transform:uppercase;letter-spacing:0.1pt;line-height:1.24;margin-top:1pt;">{{ mb_strtoupper($program) }}</div>
        </div>

        {{-- Details --}}
        <div style="padding:4pt 8pt 0;font-size:7pt;color:#1b1b1b;font-weight:600;line-height:1.5;text-align:center;word-break:break-word;">
            <div>Student ID No.: <strong>{{ $studentNo }}</strong></div>
            @if($dob)<div style="margin-top:1pt;">Date of Birth:- <strong>{{ $dob }}</strong></div>@else<div style="margin-top:1pt;">Date of Birth:- <strong>—</strong></div>@endif
            @if($stdAddr)<div style="margin-top:1pt;">Address:- <strong>{{ $stdAddr }}</strong></div>@endif
            @if($validUpto)<div style="margin-top:1pt;">Valid up to: <strong>{{ $validUpto }}</strong></div>@endif
        </div>

        {{-- Barcode + Signature row — identical to student-card-pdf.blade.php --}}
        @if($barcodeType !== 'none')
        <div style="padding:2pt 6pt 3pt;margin-top:auto;">
            <table style="width:100%;border-collapse:collapse;"><tr>
                @if($barcodeType === 'barcode' || $barcodeType === 'both')
                <td style="vertical-align:bottom;padding-right:2pt;">
                    <div style="line-height:0;font-size:0;white-space:nowrap;">{!! $barcodeHtml !!}</div>
                    <div style="font-size:3.5pt;text-align:center;font-family:monospace;letter-spacing:0.5pt;margin-top:1pt;">{{ $studentNo }}</div>
                </td>
                @endif
                @if(($barcodeType === 'qr' || $barcodeType === 'both') && $qrBase64)
                <td style="vertical-align:bottom;text-align:center;padding:0 2pt;width:32pt;">
                    <img src="{{ $qrBase64 }}" style="width:28pt;height:28pt;display:block;margin:0 auto;">
                </td>
                @endif
                <td style="vertical-align:bottom;text-align:center;width:35pt;font-size:6pt;font-weight:bold;color:#3f3f46;">
                    <div>{{ $principal }}</div>
                </td>
            </tr></table>
        </div>
        @else
        <div style="margin-top:auto;"></div>
        @endif

        {{-- Red address footer --}}
        <div style="background:{{ $headerColor }};padding:5pt 2pt 4pt;text-align:center;color:#fff;font-size:6pt;line-height:1.5;flex-shrink:0;">
            <div>{{ $address ?: 'Budhiganga-4, Morang, Koshi Province, Nepal' }}</div>
            <div>Ph: {{ $phone ?: '021-622058' }}@if($email) | Email: {{ $email }}@endif</div>
        </div>

        {{-- Black identity strip --}}
        <div style="background:#1a1a1a;color:#fff;text-align:center;font-family:'Georgia','Times New Roman',serif;font-size:8pt;font-weight:bold;letter-spacing:0.3pt;height:18pt;display:flex;align-items:center;justify-content:center;text-transform:uppercase;line-height:1;flex-shrink:0;">
            STUDENT IDENTITY CARD
        </div>

    </div>{{-- /white body --}}

</div>{{-- /card-shell --}}
@endforeach
</div>{{-- /preview --}}

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 700);
    });
</script>
</body>
</html>
