@if($cardConfig['card_type'] !== 'premium')
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Card</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @@page { margin: 0; }
    html, body { overflow: hidden; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 6pt; background: #fff; }
    .id-card { width: 100%; background: #fff; overflow: hidden; }
    .card-header { padding: 5pt 6pt 0; }
    .card-header table { width: 100%; border-collapse: collapse; }
    .header-logo-cell { width: 26pt; vertical-align: middle; padding-right: 4pt; }
    .header-logo-cell img { width: 22pt; height: 22pt; border-radius: 50%; border: 1pt solid rgba(255,255,255,0.5); display: block; }
    .header-text-cell { vertical-align: middle; }
    .header-college-name { color: #ffffff; font-size: 7pt; font-weight: bold; letter-spacing: 0.2pt; line-height: 1.3; }
    .header-affiliation { color: rgba(255,255,255,0.85); font-size: 5pt; margin-top: 1pt; }
    .photo-spacer { height: 24pt; } /* red-bg space inside header for the photo's top half */
    .photo-row { text-align: center; margin-top: -24pt; position: relative; } /* pull photo up into header */
    .photo-outer { width: 48pt; height: 48pt; border-radius: 50%; display: inline-block; overflow: hidden; background: white; }
    .photo-circle { width: 44pt; height: 44pt; border-radius: 50%; overflow: hidden; display: block; margin: 2pt; }
    .photo-circle img { width: 44pt; height: 44pt; display: block; }
    .photo-placeholder { width: 44pt; height: 44pt; border-radius: 50%; background: #e2e8f0; display: block; margin: 2pt; text-align: center; line-height: 44pt; font-size: 13pt; color: #94a3b8; }
    .white-body { background: #ffffff; padding-top: 4pt; }
    .name-section { background: #ffffff; text-align: center; padding: 2pt 6pt 3pt; }
    .student-name  { font-size: 9pt; font-weight: bold; color: #1e3a5f; letter-spacing: 0.5pt; }
    .student-program { font-size: 6pt; font-weight: bold; color: #1a1a1a; margin-top: 1pt; letter-spacing: 0.2pt; }
    .details-section { background: #ffffff; padding: 2pt 8pt; text-align: center; }
    .detail-row { font-size: 6pt; color: #1e293b; line-height: 1.7; }
    .detail-label { color: #475569; }
    .detail-value { font-weight: bold; color: #0f172a; }
    .id-card-footer-row { background: #ffffff; padding: 3pt 6pt 4pt; }
    .footer-inner { width: 100%; border-collapse: collapse; }
    .barcode-cell { vertical-align: bottom; padding-right: 2pt; }
    .barcode-table { border-collapse: collapse; border-spacing: 0; height: 15pt; table-layout: fixed; }
    .barcode-num { font-size: 4pt; text-align: center; font-family: 'Courier New', monospace; letter-spacing: 0.5pt; margin-top: 1pt; }
    .qr-cell { vertical-align: bottom; text-align: center; padding: 0 2pt; }
    .qr-cell img { width: 29pt; height: 29pt; }
    .qr-placeholder { width: 29pt; height: 29pt; border: 1pt solid #ddd; display: inline-block; text-align: center; font-size: 4pt; color: #999; padding-top: 10pt; }
    .signature-cell { vertical-align: bottom; text-align: center; width: 33pt; }
    .signature-line { border-top: 0.75pt solid #333; width: 30pt; margin: 0 auto 1pt; }
    .signature-label { font-size: 4.5pt; color: #475569; }
    .college-info-strip { padding: 3pt 5pt; text-align: center; color: #ffffff; font-size: 4.5pt; line-height: 1.5; }
    .identity-strip { background: #1a1a1a; color: #ffffff; text-align: center; padding: 3pt; font-size: 5.5pt; font-weight: bold; letter-spacing: 1.5pt; }
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
        $w  = ($cv % 5 === 0) ? 3 : (($cv % 4 === 0) ? 1 : 2);
        $barcodeHtml .= "<td style=\"background:{$bg};width:{$w}pt;height:15pt;padding:0;border:none;font-size:0;line-height:0;\"></td>";
    }
@endphp
<div class="id-card">

    {{-- ── Header: red bg, spacer div at bottom creates space for photo top half ── --}}
    <div class="card-header" style="background:{{ $headerColor }};">
        <table><tr>
            <td class="header-logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="width:22pt;height:22pt;border-radius:50%;background:rgba(255,255,255,0.2);border:1pt solid rgba(255,255,255,0.5);"></div>
                @endif
            </td>
            <td class="header-text-cell">
                <div class="header-college-name">{{ mb_strtoupper($collegeName) }}</div>
                <div class="header-affiliation">{{ $affiliation }}</div>
            </td>
        </tr></table>
        <div class="photo-spacer"></div>
    </div>

    {{-- ── Photo row: pulled up by half its height into the red header via negative margin ── --}}
    <div class="photo-row">
        <div class="photo-outer">
            @if($student->photo_b64)
                <div class="photo-circle">
                    <img src="{{ $student->photo_b64 }}" alt="Photo">
                </div>
            @else
                <div class="photo-placeholder">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
            @endif
        </div>
    </div>

    {{-- ── White body ── --}}
    <div class="white-body">
        <div class="name-section">
            <div class="student-name">{{ mb_strtoupper($name) }}</div>
            <div class="student-program">{{ mb_strtoupper($program) }}</div>
        </div>

        <div class="details-section">
            <div class="detail-row"><span class="detail-label">Student ID No:&nbsp;</span><span class="detail-value">{{ $studentNo }}</span></div>
            @if($dob)
            <div class="detail-row"><span class="detail-label">Date of Birth:&nbsp;</span><span class="detail-value">{{ $dob }}</span></div>
            @endif
            @if($studentAddress)
            <div class="detail-row"><span class="detail-label">Address:&nbsp;</span><span class="detail-value">{{ $studentAddress }}</span></div>
            @endif
            @if($address)
            <div class="detail-row"><span class="detail-label">Campus:&nbsp;</span><span class="detail-value">{{ $address }}</span></div>
            @endif
            @if($issueDateBS)
            <div class="detail-row"><span class="detail-label">Issue Date:&nbsp;</span><span class="detail-value">{{ $issueDateBS }}</span></div>
            @endif
            @if($validUptoBS)
            <div class="detail-row"><span class="detail-label">Valid up to:&nbsp;</span><span class="detail-value">{{ $validUptoBS }}</span></div>
            @endif
        </div>

        @if($barcodeType !== 'none')
        <div class="id-card-footer-row">
            <table class="footer-inner"><tr>
                @if($barcodeType === 'barcode' || $barcodeType === 'both')
                <td class="barcode-cell">
                    <table class="barcode-table"><tr>{!! $barcodeHtml !!}</tr></table>
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
    </div>

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
@else
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student ID Card</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @@page { margin: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8pt; background: #fff; }
    .id-card { width: 100%; background: #fff; }
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
        $w  = ($cv % 5 === 0) ? 3 : (($cv % 4 === 0) ? 1 : 2);
        $barcodeHtml .= "<td style=\"background:{$bg};width:{$w}pt;height:24pt;padding:0;border:none;font-size:0;line-height:0;\"></td>";
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
                <table class="barcode-table"><tr>{!! $barcodeHtml !!}</tr></table>
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
