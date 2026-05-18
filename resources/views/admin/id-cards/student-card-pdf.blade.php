@if($cardConfig['card_type'] !== 'premium')
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Card</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @@page { margin: 0; }
    html, body { background: #fff; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 6pt; }
    .card-center { width: 153pt; margin-top: 80pt; margin-left: 221pt; }
</style>
</head>
<body>
@php
    $headerColor    = $cardConfig['header_color'] ?? '#8B0000';
    $validUpto      = $cardConfig['valid_upto']   ?? '';
    $barcodeType    = $cardConfig['barcode_type'] ?? 'both';
    $collegeName    = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $affiliation    = $settings['college_affiliation']  ?? 'CTEVT';
    $address        = $settings['contact_address']      ?? '';
    $phone          = $settings['contact_phone']        ?? '';
    $email          = $settings['contact_email']        ?? '';
    $principal      = $settings['principal_name']       ?? 'Principal';

    $name           = $student->user?->name ?? '—';
    $program        = $student->program?->name ?? '—';
    $dob            = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo      = $student->student_no ?? '—';
    $studentAddress = $student->user?->address ?? null;

    // Barcode: inline-block spans work reliably in DomPDF (table cells with varying widths do not)
    $barcodeHtml = '';
    $bStr = str_pad($studentNo, 16, '0');
    for ($bi = 0; $bi < 52; $bi++) {
        $cv = ord($bStr[$bi % strlen($bStr)]) + $bi * 7;
        $bg = ($cv % 3 !== 2) ? '#000000' : '#ffffff';
        $w  = ($cv % 5 === 0) ? '3pt' : (($cv % 4 === 0) ? '1pt' : '2pt');
        $barcodeHtml .= "<span style=\"display:inline-block;background:{$bg};width:{$w};height:15pt;vertical-align:top;\"></span>";
    }
@endphp

{{-- ── Center card on A4 page ── --}}
<div class="card-center">
{{-- ── Outer wrapper: position:relative so photo can be absolutely positioned ── --}}
<div style="position:relative; overflow:visible;">

    {{-- ── RED HEADER: extends 24pt below logo row to hold the photo's top half ── --}}
    <div style="background:{{ $headerColor }}; padding:5pt 6pt 0;">
        <table style="width:100%; border-collapse:collapse;"><tr>
            <td style="width:26pt; vertical-align:middle; padding-right:4pt;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width:22pt; height:22pt; border-radius:50%; border:1pt solid rgba(255,255,255,0.5); display:block;">
                @else
                    <div style="width:22pt; height:22pt; border-radius:50%; background:rgba(255,255,255,0.2); border:1pt solid rgba(255,255,255,0.5);"></div>
                @endif
            </td>
            <td style="vertical-align:middle;">
                <div style="color:#fff; font-size:7pt; font-weight:bold; letter-spacing:0.2pt; line-height:1.3;">{{ mb_strtoupper($collegeName) }}</div>
                <div style="color:rgba(255,255,255,0.85); font-size:5pt; margin-top:1pt;">{{ $affiliation }}</div>
            </td>
        </tr></table>
        {{-- Spacer: red bg extends 24pt so the photo circle's top half sits in the header --}}
        <div style="height:24pt;"></div>
    </div>

    {{-- ── PHOTO: position:absolute, centered, overlapping header bottom edge ── --}}
    {{-- Header height = 5pt padding + ~22pt logo row + 24pt spacer = ~51pt          --}}
    {{-- Photo top = 51 - 24 = 27pt; photo is 48pt tall so bottom = 75pt             --}}
    <div style="position:absolute; top:27pt; left:52pt; width:48pt; height:48pt; border-radius:50%; background:white; overflow:hidden;">
        @if($student->photo_b64)
            <img src="{{ $student->photo_b64 }}" style="width:44pt; height:44pt; border-radius:50%; margin:2pt; display:block;">
        @else
            <div style="width:44pt; height:44pt; border-radius:50%; background:#e2e8f0; margin:2pt; text-align:center; line-height:44pt; font-size:13pt; color:#94a3b8;">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
        @endif
    </div>

</div>{{-- end position:relative wrapper --}}

{{-- ── WHITE BODY: padding-top:28pt clears the photo bottom (photo ends at 75pt, body starts at 51+28=79pt) ── --}}
<div style="background:white; padding-top:28pt;">

    {{-- Name & program --}}
    <div style="text-align:center; padding:2pt 6pt 3pt;">
        <div style="font-size:9pt; font-weight:bold; color:#1e3a5f; letter-spacing:0.5pt;">{{ mb_strtoupper($name) }}</div>
        <div style="font-size:6pt; font-weight:bold; color:#1a1a1a; margin-top:1pt; letter-spacing:0.2pt;">{{ mb_strtoupper($program) }}</div>
    </div>

    {{-- Details --}}
    <div style="padding:2pt 8pt; text-align:center;">
        <div style="font-size:6pt; color:#1e293b; line-height:1.7;"><span style="color:#475569;">Student ID No:&nbsp;</span><strong style="color:#0f172a;">{{ $studentNo }}</strong></div>
        @if($dob)
        <div style="font-size:6pt; color:#1e293b; line-height:1.7;"><span style="color:#475569;">Date of Birth:&nbsp;</span><strong style="color:#0f172a;">{{ $dob }}</strong></div>
        @endif
        @if($studentAddress)
        <div style="font-size:6pt; color:#1e293b; line-height:1.7;"><span style="color:#475569;">Address:&nbsp;</span><strong style="color:#0f172a;">{{ $studentAddress }}</strong></div>
        @endif
        @if($address)
        <div style="font-size:6pt; color:#1e293b; line-height:1.7;"><span style="color:#475569;">Campus:&nbsp;</span><strong style="color:#0f172a;">{{ $address }}</strong></div>
        @endif
        @if($validUpto)
        <div style="font-size:6pt; color:#1e293b; line-height:1.7;"><span style="color:#475569;">Valid up to:&nbsp;</span><strong style="color:#0f172a;">{{ $validUpto }}</strong></div>
        @endif
    </div>

    {{-- Barcode / QR / Signature --}}
    @if($barcodeType !== 'none')
    <div style="padding:3pt 6pt 4pt;">
        <table style="width:100%; border-collapse:collapse;"><tr>
            @if($barcodeType === 'barcode' || $barcodeType === 'both')
            <td style="vertical-align:bottom; padding-right:2pt;">
                <div style="line-height:0; font-size:0; white-space:nowrap;">{!! $barcodeHtml !!}</div>
                <div style="font-size:4pt; text-align:center; font-family:monospace; letter-spacing:0.5pt; margin-top:1pt;">{{ $studentNo }}</div>
            </td>
            @endif
            @if($barcodeType === 'qr' || $barcodeType === 'both')
            <td style="vertical-align:bottom; text-align:center; padding:0 2pt; width:32pt;">
                @if($qrBase64)
                    <img src="{{ $qrBase64 }}" style="width:30pt; height:30pt; display:block; margin:0 auto;">
                @else
                    <div style="width:30pt; height:30pt; border:1pt solid #ddd; text-align:center; font-size:4pt; color:#999; padding-top:10pt;">QR</div>
                @endif
            </td>
            @endif
            <td style="vertical-align:bottom; text-align:center; width:30pt;">
                <div style="border-top:0.75pt solid #333; width:28pt; margin:0 auto 1pt;"></div>
                <div style="font-size:4.5pt; color:#475569;">{{ $principal }}</div>
            </td>
        </tr></table>
    </div>
    @endif

    {{-- Footer strips --}}
    <div style="background:{{ $headerColor }}; padding:3pt 5pt; text-align:center; color:#fff; font-size:4.5pt; line-height:1.5;">
        @if($address){{ $address }}<br>@endif
        @if($phone)Ph: {{ $phone }}@endif
        @if($phone && $email) | @endif
        @if($email){{ $email }}@endif
    </div>
    <div style="background:#1a1a1a; color:#fff; text-align:center; padding:3pt; font-size:5.5pt; font-weight:bold; letter-spacing:1.5pt;">STUDENT IDENTITY CARD</div>

</div>{{-- end white body --}}
</div>{{-- end card-center --}}
</body>
</html>
@else
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Card</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @@page { margin: 0; }
    html, body { background: #fff; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8pt; }
    .id-card { width: 153pt; margin-top: 80pt; margin-left: 221pt; background: #fff; }
    .card-header { padding: 14pt 16pt 12pt; }
    .card-header table { width: 100%; border-collapse: collapse; }
    .header-logo-cell { width: 42pt; vertical-align: middle; padding-right: 6pt; }
    .header-logo-cell img { width: 38pt; height: 38pt; border-radius: 50%; border: 1.5pt solid rgba(255,255,255,0.5); display: block; }
    .header-text-cell { vertical-align: middle; }
    .header-college-name { color: #ffffff; font-size: 9.5pt; font-weight: bold; letter-spacing: 0.3pt; line-height: 1.3; }
    .header-affiliation { color: rgba(255,255,255,0.85); font-size: 7.5pt; margin-top: 2pt; }
    .photo-section { background: #ffffff; padding: 10pt 0 6pt; text-align: center; }
    .photo-outer { width: 76pt; height: 76pt; border-radius: 50%; display: inline-block; }
    .photo-circle { width: 62pt; height: 62pt; border-radius: 50%; overflow: hidden; display: block; border: 3pt solid white; margin: 7pt; }
    .photo-circle img { width: 62pt; height: 62pt; display: block; }
    .photo-placeholder { width: 62pt; height: 62pt; border-radius: 50%; background: #e2e8f0; display: block; border: 3pt solid white; margin: 7pt; text-align: center; line-height: 62pt; font-size: 20pt; color: #94a3b8; }
    .name-section { background: #ffffff; text-align: center; padding: 0 10pt 8pt; }
    .student-name  { font-size: 13pt; font-weight: bold; letter-spacing: 0.5pt; }
    .student-program { font-size: 8pt; font-weight: bold; color: #1e3a5f; margin-top: 2pt; letter-spacing: 0.3pt; }
    .divider { height: 1.5pt; margin: 0 10pt; }
    .details-section { background: #ffffff; padding: 8pt 14pt; }
    .detail-row { font-size: 8pt; color: #1e293b; line-height: 1.9; }
    .detail-label { color: #475569; }
    .detail-value { font-weight: bold; color: #0f172a; }
    .id-card-footer-row { background: #ffffff; padding: 6pt 10pt 8pt; }
    .footer-inner { width: 100%; border-collapse: collapse; }
    .barcode-cell { vertical-align: bottom; padding-right: 4pt; }
    .barcode-table { border-collapse: collapse; border-spacing: 0; height: 24pt; table-layout: fixed; }
    .barcode-num { font-size: 6pt; text-align: center; font-family: 'Courier New', monospace; letter-spacing: 1pt; margin-top: 2pt; }
    .qr-cell { vertical-align: bottom; text-align: center; padding: 0 4pt; }
    .qr-cell img { width: 46pt; height: 46pt; }
    .qr-placeholder { width: 46pt; height: 46pt; border: 1pt solid #ddd; display: inline-block; text-align: center; font-size: 6pt; color: #999; padding-top: 16pt; }
    .signature-cell { vertical-align: bottom; text-align: center; width: 52pt; }
    .signature-line { border-top: 1pt solid #333; width: 48pt; margin: 0 auto 2pt; }
    .signature-label { font-size: 7pt; color: #475569; }
    .college-info-strip { padding: 5pt 8pt; text-align: center; color: #ffffff; font-size: 7pt; line-height: 1.55; }
    .identity-strip { background: #1a1a1a; color: #ffffff; text-align: center; padding: 5pt; font-size: 8.5pt; font-weight: bold; letter-spacing: 2.5pt; }
</style>
</head>
<body>
@php
    $headerColor    = $cardConfig['header_color'] ?? '#8B0000';
    $validUpto      = $cardConfig['valid_upto']   ?? '';
    $issueDate      = $cardConfig['issue_date']   ?? '';
    $barcodeType    = $cardConfig['barcode_type'] ?? 'both';
    $collegeName    = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $affiliation    = $settings['college_affiliation']  ?? 'CTEVT';
    $address        = $settings['contact_address']      ?? '';
    $phone          = $settings['contact_phone']        ?? '';
    $email          = $settings['contact_email']        ?? '';
    $principal      = $settings['principal_name']       ?? 'Principal';

    $name           = $student->user?->name ?? '—';
    $program        = $student->program?->name ?? '—';
    $dob            = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo      = $student->student_no ?? '—';
    $studentAddress = $student->user?->address ?? null;
    $validUptoBS    = $validUpto;
    $issueDateBS    = $issueDate;

    $barcodeHtml = '';
    $bStr = str_pad($studentNo, 16, '0');
    for ($bi = 0; $bi < 52; $bi++) {
        $cv = ord($bStr[$bi % strlen($bStr)]) + $bi * 7;
        $bg = ($cv % 3 !== 2) ? '#000000' : '#ffffff';
        $w  = ($cv % 5 === 0) ? '3pt' : (($cv % 4 === 0) ? '1pt' : '2pt');
        $barcodeHtml .= "<span style=\"display:inline-block;background:{$bg};width:{$w};height:24pt;vertical-align:top;\"></span>";
    }
@endphp
<div class="id-card">
    <div class="card-header" style="background:{{ $headerColor }}; padding:14pt 16pt 12pt;">
        <table><tr>
            <td class="header-logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="width:38pt;height:38pt;border-radius:50%;background:rgba(255,255,255,0.2);border:1.5pt solid rgba(255,255,255,0.5);"></div>
                @endif
            </td>
            <td class="header-text-cell">
                <div class="header-college-name">{{ mb_strtoupper($collegeName) }}</div>
                <div class="header-affiliation">{{ $affiliation }}</div>
            </td>
        </tr></table>
    </div>

    <div class="photo-section">
        <div class="photo-outer" style="background:{{ $headerColor }};">
            @if($student->photo_b64)
                <div class="photo-circle">
                    <img src="{{ $student->photo_b64 }}" alt="Photo">
                </div>
            @else
                <div class="photo-placeholder">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
            @endif
        </div>
    </div>

    <div class="name-section">
        <div class="student-name" style="color:{{ $headerColor }};">{{ mb_strtoupper($name) }}</div>
        <div class="student-program">{{ mb_strtoupper($program) }}</div>
    </div>

    <div class="divider" style="background:{{ $headerColor }};"></div>

    <div class="details-section">        <div class="detail-row"><span class="detail-label">Student ID No:&nbsp;</span><span class="detail-value">{{ $studentNo }}</span></div>
        @if($dob)
        <div class="detail-row"><span class="detail-label">Date of Birth:&nbsp;</span><span class="detail-value">{{ $dob }}</span></div>
        @endif
        @if($studentAddress)
        <div class="detail-row"><span class="detail-label">Address:&nbsp;</span><span class="detail-value">{{ $studentAddress }}</span></div>
        @endif
        @if($address)
        <div class="detail-row"><span class="detail-label">Campus:&nbsp;</span><span class="detail-value">{{ $address }}</span></div>
        @endif
        @if($validUpto)
        <div class="detail-row"><span class="detail-label">Issue Date:&nbsp;</span><span class="detail-value">{{ $issueDate }}</span></div>
        <div class="detail-row"><span class="detail-label">Valid up to:&nbsp;</span><span class="detail-value">{{ $validUpto }}</span></div>
        @endif
    </div>

    @if($barcodeType !== 'none')
    <div class="id-card-footer-row">
        <table class="footer-inner"><tr>
            @if($barcodeType === 'barcode' || $barcodeType === 'both')
            <td class="barcode-cell">
                <div style="line-height:0; font-size:0; white-space:nowrap;">{!! $barcodeHtml !!}</div>
                <div class="barcode-num">{{ $studentNo }}</div>
            </td>
            @endif
            @if($barcodeType === 'qr' || $barcodeType === 'both')
            <td class="qr-cell">
                @if($qrBase64)<img src="{{ $qrBase64 }}" alt="QR">
                @else<div class="qr-placeholder">QR</div>@endif
            </td>
            @endif
            <td class="signature-cell">
                <div class="signature-line"></div>
                <div class="signature-label">{{ $principal }}</div>
            </td>
        </tr></table>
    </div>
    @endif

    <div class="college-info-strip" style="background:{{ $headerColor }};">
        @if($address){{ $address }}<br>@endif
        @if($phone)Ph: {{ $phone }}@endif
        @if($phone && $email) | @endif
        @if($email){{ $email }}@endif
    </div>
    <div class="identity-strip">STUDENT IDENTITY CARD</div>
</div>
</body>
</html>

@endif
