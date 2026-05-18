<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk Student ID Cards</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #e5e7eb;
}

.card-page {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 24px;
    min-height: 100vh;
    background: #e5e7eb;
}

.card-wrap {
    width: 288px;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.18);
    background: #fff;
    font-family: 'Segoe UI', Arial, sans-serif;
}

.card-wrap * { box-sizing: border-box; }

@media print {
    @page { size: A4 portrait; margin: 0; }

    body { background: #fff; }

    .card-page {
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 80px;
        width: 210mm;
        min-height: 297mm;
        page-break-after: always;
        break-after: page;
    }

    .card-page:last-child {
        page-break-after: avoid;
        break-after: avoid;
    }

    .card-wrap {
        width: 86mm;
        border-radius: 0;
        box-shadow: none;
    }
}
</style>
</head>
<body>

@php
    $collegeName = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $affiliation = $settings['college_affiliation'] ?? 'CTEVT';
    $address     = $settings['contact_address']     ?? '';
    $phone       = $settings['contact_phone']       ?? '';
    $email       = $settings['contact_email']       ?? '';
    $principal   = $settings['principal_name']      ?? 'Principal';
    $headerColor = $cardConfig['header_color']      ?? '#8B0000';
    $barcodeType = $cardConfig['barcode_type']      ?? 'both';
    $validUpto   = $cardConfig['valid_upto']        ?? '';
@endphp

@foreach($students as $student)
@php
    $name       = $student->user?->name ?? '—';
    $program    = $student->program?->name ?? '—';
    $dob        = $student->user?->dob ? bsDate($student->user->dob) : null;
    $studentNo  = $student->student_no ?? '—';
    $stdAddress = $student->user?->address ?? null;
    $qrBase64   = $qrMap[$student->id] ?? null;

    // Barcode — flex div bars matching the preview card logic
    $barHtml = '';
    if (in_array($barcodeType, ['both', 'barcode'])) {
        $sno    = $student->student_no ?: str_pad((string) $student->id, 8, '0', STR_PAD_LEFT);
        $snoLen = max(strlen($sno), 1);
        for ($i = 1; $i <= 40; $i++) {
            $charCode = ord($sno[($i - 1) % $snoLen]);
            $cv       = $i * 7 + $charCode;
            $bg       = ($cv % 3 !== 2) ? '#000' : '#fff';
            $w        = ($i * 3) % 4 === 0 ? '3px' : '1.5px';
            $barHtml .= "<div style=\"display:inline-block;background:{$bg};width:{$w};height:100%;flex-shrink:0;\"></div>";
        }
    }
@endphp

<div class="card-page">
    <div class="card-wrap">

        {{-- ── Header ── --}}
        <div style="background:{{ $headerColor }}; padding:14px 12px 52px; display:flex; align-items:center; gap:10px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}"
                     style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.6);object-fit:cover;flex-shrink:0;">
            @else
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.6);flex-shrink:0;"></div>
            @endif
            <div style="color:white;font-weight:700;font-size:13px;line-height:1.25;letter-spacing:0.3px;">
                {{ mb_strtoupper($collegeName) }}<br>
                <span style="font-size:10px;font-weight:400;opacity:0.85;">{{ $affiliation }}</span>
            </div>
        </div>

        {{-- ── White body ── --}}
        <div style="background:white; position:relative; padding-top:58px;">

            {{-- Photo circle overlapping the header bottom --}}
            <div style="position:absolute; top:-48px; left:0; right:0; display:flex; justify-content:center;">
                <div style="width:96px;height:96px;border-radius:50%;background:white;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if($student->photo_b64)
                        <img src="{{ $student->photo_b64 }}"
                             style="width:96px;height:96px;border-radius:50%;object-fit:cover;display:block;">
                    @else
                        <div style="width:96px;height:96px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#94a3b8;">
                            {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Name & Program --}}
            <div style="text-align:center; padding:4px 14px 6px;">
                <div style="font-size:16px;font-weight:900;color:#1e3a5f;letter-spacing:1px;text-transform:uppercase;">
                    {{ mb_strtoupper($name) }}
                </div>
                <div style="font-size:10.5px;font-weight:800;color:#1a1a1a;text-transform:uppercase;margin-top:2px;letter-spacing:0.8px;">
                    {{ mb_strtoupper($program) }}
                </div>
            </div>

            {{-- Details --}}
            <div style="padding:2px 16px 6px; font-size:10px; color:#1e293b; line-height:1.85; text-align:center;">
                <div><span style="color:#475569;">Student ID No:</span> &nbsp;<strong>{{ $studentNo }}</strong></div>
                @if($dob)
                    <div><span style="color:#475569;">Date of Birth:</span> &nbsp;<strong>{{ $dob }}</strong></div>
                @endif
                @if($stdAddress)
                    <div><span style="color:#475569;">Address:</span> &nbsp;<strong>{{ $stdAddress }}</strong></div>
                @endif
                @if($address)
                    <div><span style="color:#475569;">Campus:</span> &nbsp;<strong>{{ $address }}</strong></div>
                @endif
                @if($validUpto)
                    <div><span style="color:#475569;">Valid up to:</span> &nbsp;<strong>{{ $validUpto }}</strong></div>
                @endif
            </div>

            {{-- Barcode + QR + Signature --}}
            <div style="padding:8px 14px; display:flex; justify-content:space-between; align-items:flex-end; gap:6px;">

                {{-- Barcode --}}
                @if(in_array($barcodeType, ['both', 'barcode']))
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;gap:1px;height:28px;align-items:stretch;overflow:hidden;">
                        {!! $barHtml !!}
                    </div>
                    <div style="font-size:7px;text-align:center;margin-top:2px;font-family:monospace;letter-spacing:1px;">{{ $studentNo }}</div>
                </div>
                @endif

                {{-- QR --}}
                @if(in_array($barcodeType, ['both', 'qr']))
                <div style="flex-shrink:0;">
                    @if($qrBase64)
                        <img src="{{ $qrBase64 }}" style="width:50px;height:50px;" alt="QR">
                    @else
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=55x55&data={{ urlencode($studentNo) }}"
                             style="width:50px;height:50px;" alt="QR">
                    @endif
                </div>
                @endif

                {{-- Signature --}}
                <div style="flex-shrink:0;text-align:center;font-size:8px;color:#475569;width:56px;">
                    <div style="border-top:1px solid #475569;padding-top:3px;">{{ $principal }}</div>
                </div>

            </div>
        </div>{{-- end white body --}}

        {{-- ── Footer ── --}}
        <div style="background:{{ $headerColor }};color:white;padding:7px 10px;font-size:8px;text-align:center;line-height:1.6;">
            {{ $address }}<br>
            @if($phone)<span>Ph: {{ $phone }}</span>@endif
            @if($email)<span> | {{ $email }}</span>@endif
        </div>

        {{-- ── Bottom strip ── --}}
        <div style="background:#1a1a1a;color:white;text-align:center;padding:7px;font-size:11px;font-weight:700;letter-spacing:3px;">
            STUDENT IDENTITY CARD
        </div>

    </div>
</div>
@endforeach

<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 600);
});
</script>
</body>
</html>
