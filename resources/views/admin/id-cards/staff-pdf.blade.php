<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff ID Cards</title>
<style>
    @@page { margin: 0; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { background: #fff; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 6pt; }
    .id-card { width: 100%; background: #fff; overflow: hidden; page-break-after: always; }
    .id-card:last-child { page-break-after: avoid; }
    .card-header { padding: 5pt 6pt 0; }
    .card-header table { width: 100%; border-collapse: collapse; }
    .header-logo-cell { width: 26pt; vertical-align: middle; padding-right: 4pt; }
    .header-logo-cell img { width: 22pt; height: 22pt; border-radius: 50%; border: 1pt solid rgba(255,255,255,0.5); display: block; }
    .header-text-cell { vertical-align: middle; }
    .header-college-name { color: #ffffff; font-size: 7pt; font-weight: bold; letter-spacing: 0.2pt; line-height: 1.3; }
    .header-affiliation { color: rgba(255,255,255,0.85); font-size: 5pt; margin-top: 1pt; }
    .photo-spacer { height: 24pt; }
    .photo-row { text-align: center; margin-top: -24pt; position: relative; }
    .photo-outer { width: 48pt; height: 48pt; border-radius: 50%; display: inline-block; overflow: hidden; background: white; }
    .photo-circle { width: 44pt; height: 44pt; border-radius: 50%; overflow: hidden; display: block; margin: 2pt; }
    .photo-circle img { width: 44pt; height: 44pt; display: block; }
    .photo-placeholder { width: 44pt; height: 44pt; border-radius: 50%; background: #e2e8f0; display: block; margin: 2pt; text-align: center; line-height: 44pt; font-size: 13pt; color: #94a3b8; }
    .white-body { background: #ffffff; padding-top: 4pt; }
    .name-section { background: #ffffff; text-align: center; padding: 2pt 6pt 3pt; }
    .staff-name { font-size: 9pt; font-weight: bold; color: #1e3a5f; letter-spacing: 0.5pt; }
    .staff-designation { font-size: 6pt; font-weight: bold; margin-top: 1pt; letter-spacing: 0.2pt; }
    .staff-department { font-size: 5.5pt; color: #94a3b8; margin-top: 1pt; }
    .divider { height: 1pt; margin: 0 8pt; }
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
    $headerColor = $cardConfig['header_color'] ?? '#1e3a5f';
    $validUpto   = $cardConfig['valid_upto']   ?? '';
    $issueDate   = $cardConfig['issue_date']   ?? '';
    $barcodeType = $cardConfig['barcode_type'] ?? 'both';
    $collegeName = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $affiliation = $settings['college_affiliation']  ?? 'CTEVT';
    $address     = $settings['contact_address']      ?? '';
    $phone       = $settings['contact_phone']        ?? '';
    $email       = $settings['contact_email']        ?? '';
    $principal   = $settings['principal_name']       ?? 'Principal';
@endphp

@foreach($staffList as $member)
@php
    $name        = $member->name ?? '—';
    $designation = $member->designation ?? '—';
    $department  = $member->department ?? '';
    $staffCode   = $member->staff_code ?? '—';
    $joinDate    = $member->join_date ? bsDate($member->join_date) : null;
    $qrBase64    = $qrMap[$member->id] ?? null;

    $barcodeHtml = '';
    $bStr = str_pad($staffCode, 16, '0');
    for ($bi = 0; $bi < 52; $bi++) {
        $cv = ord($bStr[$bi % strlen($bStr)]) + $bi * 7;
        $bg = ($cv % 3 !== 2) ? '#000000' : '#ffffff';
        $w  = ($cv % 5 === 0) ? 3 : (($cv % 4 === 0) ? 1 : 2);
        $barcodeHtml .= "<td style=\"background:{$bg};width:{$w}pt;height:15pt;padding:0;border:none;font-size:0;line-height:0;\"></td>";
    }
@endphp
<div class="id-card">
    <div class="card-header" style="background:{{ $headerColor }};">
        <table><tr>
            <td class="header-logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @else
                    <div style="width:22pt;height:22pt;border-radius:50%;background:rgba(255,255,255,0.2);"></div>
                @endif
            </td>
            <td class="header-text-cell">
                <div class="header-college-name">{{ mb_strtoupper($collegeName) }}</div>
                <div class="header-affiliation">{{ $affiliation }}</div>
            </td>
        </tr></table>
        <div class="photo-spacer"></div>
    </div>
    <div class="photo-row">
        <div class="photo-outer">
        @if($member->photo_b64)
            <div class="photo-circle"><img src="{{ $member->photo_b64 }}" alt="Photo"></div>
        @else
            <div class="photo-placeholder">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
        @endif
        </div>
    </div>
    <div class="white-body">
        <div class="name-section">
            <div class="staff-name">{{ mb_strtoupper($name) }}</div>
            <div class="staff-designation" style="color:{{ $headerColor }};">{{ mb_strtoupper($designation) }}</div>
            @if($department)<div class="staff-department">{{ mb_strtoupper($department) }}</div>@endif
        </div>
        <div class="divider" style="background:{{ $headerColor }};"></div>
        <div class="details-section">
            <div class="detail-row"><span class="detail-label">Staff Code:&nbsp;</span><span class="detail-value">{{ $staffCode }}</span></div>
            @if($joinDate)
            <div class="detail-row"><span class="detail-label">Join Date:&nbsp;</span><span class="detail-value">{{ $joinDate }}</span></div>
            @endif
            @if($issueDate)
            <div class="detail-row"><span class="detail-label">Issue Date:&nbsp;</span><span class="detail-value">{{ $issueDate }}</span></div>
            @endif
            @if($validUpto)
            <div class="detail-row"><span class="detail-label">Valid up to:&nbsp;</span><span class="detail-value">{{ $validUpto }}</span></div>
            @endif
        </div>
        @if($barcodeType !== 'none')
        <div class="id-card-footer-row">
            <table class="footer-inner"><tr>
                @if($barcodeType === 'barcode' || $barcodeType === 'both')
                <td class="barcode-cell">
                    <table class="barcode-table"><tr>{!! $barcodeHtml !!}</tr></table>
                    <div class="barcode-num">{{ $staffCode }}</div>
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
    </div>{{-- end white-body --}}
    <div class="college-info-strip" style="background:{{ $headerColor }};">
        @if($address){{ $address }}<br>@endif
        @if($phone)Ph: {{ $phone }}@endif
        @if($phone && $email) | @endif
        @if($email){{ $email }}@endif
    </div>
    <div class="identity-strip">STAFF IDENTITY CARD</div>
</div>
@endforeach
</body>
</html>
