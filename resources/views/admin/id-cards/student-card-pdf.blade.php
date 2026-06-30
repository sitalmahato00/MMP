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
    .pos-rel { position: relative; }
    .pos-abs { position: absolute; }
</style>
</head>
<body>
@php
    $headerColor    = $cardConfig['header_color'] ?? '#a0161d';
    $validUpto      = $cardConfig['valid_upto']   ?? '';
    $barcodeType    = $cardConfig['barcode_type'] ?? 'both';
    $collegeName    = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $address        = $settings['contact_address']      ?? '';
    $phone          = $settings['contact_phone']        ?? '';
    $email          = $settings['contact_email']        ?? '';
    $principal      = $settings['principal_name']       ?? 'Principal';

    $name           = $student->user?->name ?? '—';
    $program        = $student->program?->name ?? '—';
    $dob            = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo      = $student->student_no ?? '—';
    $studentAddress = $student->user?->address ?? null;

    // Barcode: inline-block spans for DomPDF
    $barcodeHtml = '';
    $bStr = str_pad($studentNo, 16, '0');
    for ($bi = 0; $bi < 52; $bi++) {
        $cv = ord($bStr[$bi % strlen($bStr)]) + $bi * 7;
        $bg = ($cv % 3 !== 2) ? '#000000' : '#ffffff';
        $w  = ($cv % 5 === 0) ? '3pt' : (($cv % 4 === 0) ? '1pt' : '2pt');
        $barcodeHtml .= "<span style=\"display:inline-block;background:{$bg};width:{$w};height:15pt;vertical-align:top;\"></span>";
    }
@endphp

<div class="card-center">

    {{-- ── position:relative wrapper for absolute photo ── --}}
    <div class="pos-rel">

        {{-- ══════════ HEADER BAND (71pt = 134px scaled) ══════════ --}}
        <div style="background:{{ $headerColor }}; height:71pt; position:relative;">
            {{-- College title --}}
            <div style="position:absolute; top:5pt; left:0; right:0; text-align:center; color:#fff; font-weight:800; font-size:10pt; line-height:1.2; text-transform:uppercase; letter-spacing:0.3pt; padding:0 8pt;">
                {{ mb_strtoupper($collegeName) }}
            </div>
            {{-- Logo circle --}}
            <div style="position:absolute; top:22pt; left:6pt; width:22pt; height:22pt; border-radius:50%; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center; padding:1pt; box-sizing:border-box;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width:100%; height:100%; object-fit:contain; display:block;">
                @endif
            </div>
        </div>

        {{-- ══════════ PHOTO CIRCLE (60pt = 112px scaled) ══════════ --}}
        {{-- top = 71 - 30 - 8 = 33pt (matches 62px in React) --}}
        {{-- left = (153 - 60) / 2 = 46.5 ≈ 47pt (matches 88px in React) --}}
        <div style="position:absolute; top:33pt; left:47pt; width:60pt; height:60pt; border-radius:50%; background:#e5e7eb; overflow:hidden; z-index:10; border:0.5pt solid rgba(122,15,21,0.25);">
            @if($student->photo_b64)
                <img src="{{ $student->photo_b64 }}" style="width:100%; height:100%; object-fit:cover; display:block;">
            @else
                <div style="width:100%; height:100%; border-radius:50%; background:#e2e8f0; text-align:center; line-height:60pt; font-size:18pt; color:#94a3b8;">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</div>
            @endif
        </div>

    </div>{{-- /pos-rel --}}

    {{-- ══════════ WHITE BODY (padding-top: 33pt = 62px scaled) ══════════ --}}
    <div style="background:#fff; padding-top:33pt;">

        {{-- Name --}}
        <div style="text-align:center; padding:2pt 8pt 1pt;">
            <div style="font-size:7.5pt; font-weight:bold; color:#24378d; text-transform:uppercase; letter-spacing:0.15pt; line-height:1.24;">{{ mb_strtoupper($name) }}</div>
            <div style="font-size:6pt; font-weight:bold; color:#111; text-transform:uppercase; letter-spacing:0.1pt; line-height:1.24; margin-top:1pt;">{{ mb_strtoupper($program) }}</div>
        </div>

        {{-- Details --}}
        <div style="padding:4pt 8pt 0; font-size:7pt; color:#1b1b1b; font-weight:600; line-height:1.5; text-align:center; word-break:break-word;">
            <div>Student ID No.: <strong>{{ $studentNo }}</strong></div>
            @if($dob)<div style="margin-top:1pt;">Date of Birth:- <strong>{{ $dob }}</strong></div>@endif
            @if($studentAddress)<div style="margin-top:1pt;">Address:- <strong>{{ $studentAddress }}</strong></div>@endif
            @if($validUpto)<div style="margin-top:1pt;">Valid up to: <strong>{{ $validUpto }}</strong></div>@endif
        </div>

        {{-- Barcode + Signature row --}}
        @if($barcodeType !== 'none')
        <div style="padding:2pt 6pt 3pt;">
            <table style="width:100%; border-collapse:collapse;"><tr>
                @if($barcodeType === 'barcode' || $barcodeType === 'both')
                <td style="vertical-align:bottom; padding-right:2pt;">
                    <div style="line-height:0; font-size:0; white-space:nowrap;">{!! $barcodeHtml !!}</div>
                    <div style="font-size:3.5pt; text-align:center; font-family:monospace; letter-spacing:0.5pt; margin-top:1pt;">{{ $studentNo }}</div>
                </td>
                @endif
                @if($barcodeType === 'qr' || $barcodeType === 'both')
                <td style="vertical-align:bottom; text-align:center; padding:0 2pt; width:32pt;">
                    @if($qrBase64)
                        <img src="{{ $qrBase64 }}" style="width:28pt; height:28pt; display:block; margin:0 auto;">
                    @endif
                </td>
                @endif
                <td style="vertical-align:bottom; text-align:center; width:35pt; font-size:6pt; font-weight:bold; color:#3f3f46;">
                    <div>{{ $principal }}</div>
                </td>
            </tr></table>
        </div>
        @endif

        {{-- RED ADDRESS FOOTER --}}
        <div style="background:{{ $headerColor }}; padding:5pt 2pt 4pt; text-align:center; color:#fff; font-size:6pt; line-height:1.5;">
            <div>Budhiganga-4, Morang, Koshi Province, Nepal</div>
            <div>Ph: {{ $phone ?: '021-622058' }}@if($email) | Email: {{ $email }}@endif</div>
        </div>

        {{-- BLACK IDENTITY STRIP --}}
        <div style="background:#1a1a1a; color:#fff; text-align:center; font-family:'Georgia','Times New Roman',serif; font-size:8pt; font-weight:bold; letter-spacing:0.3pt; height:18pt; display:flex; align-items:center; justify-content:center; text-transform:uppercase; line-height:1;">
            STUDENT IDENTITY CARD
        </div>

    </div>{{-- /white body --}}

</div>{{-- /card-center --}}
</body>
</html>
