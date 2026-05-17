<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff ID Cards</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; background: #f0f0f0; }
    .page-wrap { width: 100%; padding: 12mm 10mm; }
    .cards-row { width: 100%; margin-bottom: 8mm; }
    .cards-row table { width: 100%; border-collapse: collapse; }
    .card-cell { width: 50%; padding: 0 4mm; vertical-align: top; }
    .id-card { width: 82mm; border: 1.5pt solid #ccc; border-radius: 5pt; overflow: hidden; background: #fff; }
    .card-header { padding: 8pt 10pt; }
    .card-header table { width: 100%; border-collapse: collapse; }
    .header-logo-cell { width: 42pt; vertical-align: middle; padding-right: 6pt; }
    .header-logo-cell img { width: 38pt; height: 38pt; border-radius: 50%; border: 1.5pt solid rgba(255,255,255,0.5); display: block; }
    .header-text-cell { vertical-align: middle; }
    .header-college-name { color: #ffffff; font-size: 9.5pt; font-weight: bold; letter-spacing: 0.3pt; line-height: 1.3; }
    .header-affiliation { color: rgba(255,255,255,0.85); font-size: 7.5pt; margin-top: 2pt; }
    .photo-section { background: #ffffff; padding: 10pt 0 6pt; text-align: center; }
    .photo-outer { width: 66pt; height: 66pt; border-radius: 50%; display: inline-block; background: #1e3a5f; }
    .photo-circle { width: 54pt; height: 54pt; border-radius: 50%; overflow: hidden; display: block; border: 3pt solid white; margin: 6pt; }
    .photo-circle img { width: 54pt; height: 54pt; display: block; }
    .photo-placeholder { width: 54pt; height: 54pt; border-radius: 50%; background: #e2e8f0; display: block; border: 3pt solid white; margin: 6pt; text-align: center; line-height: 54pt; font-size: 18pt; color: #94a3b8; }
    .name-section { background: #ffffff; text-align: center; padding: 0 10pt 8pt; }
    .staff-name { font-size: 11.5pt; font-weight: bold; color: #0f172a; letter-spacing: 0.5pt; }
    .staff-designation { font-size: 7.5pt; font-weight: bold; margin-top: 2pt; letter-spacing: 0.3pt; }
    .staff-department { font-size: 7pt; color: #94a3b8; margin-top: 1pt; }
    .divider { height: 1.5pt; margin: 0 10pt; }
    .details-section { background: #ffffff; padding: 8pt 14pt; }
    .detail-row { font-size: 8pt; color: #1e293b; line-height: 1.9; }
    .detail-label { color: #475569; }
    .detail-value { font-weight: bold; color: #0f172a; }
    .id-card-footer-row { background: #ffffff; padding: 6pt 10pt 8pt; }
    .footer-inner { width: 100%; border-collapse: collapse; }
    .barcode-cell { vertical-align: bottom; padding-right: 4pt; }
    .barcode-table { border-collapse: collapse; height: 24pt; }
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
<div class="page-wrap">
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
    $chunks = $staffList->chunk(2);
@endphp

@foreach($chunks as $pair)
<div class="cards-row">
    <table>
        <tr>
            @foreach($pair as $member)
            <td class="card-cell">
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
                        $barcodeHtml .= "<td style=\"background:{$bg};width:{$w}pt;padding:0;\"></td>";
                    }
                @endphp
                <div class="id-card">
                    <div class="card-header" style="background:{{ $headerColor }};">
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
                        @if($member->photo_b64)
                            <div class="photo-circle">
                                <img src="{{ $member->photo_b64 }}" alt="Photo">
                            </div>
                        @else
                            <div class="photo-placeholder">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
                        @endif
                        </div>
                    </div>
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
                        @if($validUpto)
                        <div class="detail-row"><span class="detail-label">Valid up to:&nbsp;</span><span class="detail-value">{{ $validUpto }}</span></div>
                        @endif
                        @if($issueDate)
                        <div class="detail-row"><span class="detail-label">Issue Date:&nbsp;</span><span class="detail-value">{{ $issueDate }}</span></div>
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
                    <div class="college-info-strip" style="background:{{ $headerColor }};">
                        @if($address){{ $address }}<br>@endif
                        @if($phone)Ph: {{ $phone }}@endif
                        @if($phone && $email) | @endif
                        @if($email){{ $email }}@endif
                    </div>
                    <div class="identity-strip">STAFF IDENTITY CARD</div>
                </div>
            </td>
            @endforeach
            @if($pair->count() === 1)<td class="card-cell"></td>@endif
        </tr>
    </table>
</div>
@endforeach
</div>
</body>
</html>
