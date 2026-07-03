<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk Student ID Cards</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Segoe UI', Arial, sans-serif; background: #e5e7eb; }
.card-page {
    display: flex; justify-content: center; align-items: flex-start;
    padding: 40px 24px; min-height: 100vh; background: #e5e7eb;
}
.card-wrap {
    width: 288px; overflow: hidden; border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22); background: #fff;
    font-family: 'Montserrat', Arial, sans-serif; font-weight: 700;
    position: relative;
}
.card-wrap * { box-sizing: border-box; }
@media print {
    @page { size: A4 portrait; margin: 0; }
    body { background: #fff; }
    .card-page {
        background: #fff; display: flex; justify-content: center;
        align-items: flex-start; padding-top: 80px;
        width: 210mm; min-height: 297mm;
        page-break-after: always; break-after: page;
    }
    .card-page:last-child { page-break-after: avoid; break-after: avoid; }
    .card-wrap { width: 86mm; border-radius: 0; box-shadow: none; }
}
</style>
</head>
<body>

@php
    $collegeName = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $address     = $settings['contact_address']     ?? '';
    $phone       = $settings['contact_phone']       ?? '';
    $email       = $settings['contact_email']       ?? '';
    $principal   = $settings['principal_name']      ?? 'Principal';
    $headerColor = $cardConfig['header_color']      ?? '#a0161d';
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

    // Decorative barcode — 68 bars matching React logic
    $barHtml = '';
    if (in_array($barcodeType, ['both', 'barcode'])) {
        $sno    = $studentNo;
        $snoLen = max(strlen($sno), 1);
        for ($i = 0; $i < 68; $i++) {
            $charVal = ord($sno[$i % $snoLen]);
            $bg      = (($i * 7 + $charVal) % 3 !== 2) ? '#000' : '#fff';
            $w       = (($i * 3) % 4 === 0) ? '3px' : '1.5px';
            $barHtml .= "<div style=\"display:inline-block;background:{$bg};width:{$w};height:100%;flex-shrink:0;\"></div>";
        }
    }
@endphp

<div class="card-page">
    <div class="card-wrap" style="border-radius:12px;">

        {{-- ══════════ HEADER BAND
             HDR_H = 120px (matches React CARD_W=288 HDR_H=120) ══════════ --}}
        <div style="position:relative;background:{{ $headerColor }};height:120px;box-sizing:border-box;flex-shrink:0;print-color-adjust:exact;-webkit-print-color-adjust:exact;">
            <div style="position:absolute;top:10px;left:0;right:0;text-align:center;color:#fff;font-weight:800;font-size:18.8px;line-height:1.2;text-transform:uppercase;letter-spacing:0.45px;padding:0 16px;">
                MANMOHAN MEMORIAL<br>POLYTECHNIC
            </div>
            @if($logoBase64)
            <div style="position:absolute;top:40px;left:12px;width:42px;height:42px;border-radius:50%;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:1;padding:2px;box-sizing:border-box;">
                <img src="{{ $logoBase64 }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;display:block;">
            </div>
            @endif
        </div>

        {{-- ══════════ FLOATING PHOTO
             top  = HDR_H - PHOTO_R - 16 = 120 - 44 - 16 = 60px
             left = (CARD_W - PHOTO_D) / 2 = (288 - 88) / 2 = 100px
             size = 88×88px (PHOTO_D) ══════════ --}}
        <div style="position:absolute;top:60px;left:100px;width:88px;height:88px;border-radius:50%;background:#e5e7eb;overflow:hidden;z-index:10;border:1px solid rgba(122,15,21,0.25);box-shadow:0 1px 6px rgba(0,0,0,0.12);">
            @if($student->photo_b64)
                <img src="{{ $student->photo_b64 }}" style="width:100%;height:100%;object-fit:cover;display:block;">
            @else
                <svg viewBox="0 0 88 88" width="88" height="88">
                    <rect width="88" height="88" fill="#e5e7eb"/>
                    <circle cx="44" cy="32" r="17" fill="#9ca3af"/>
                    <ellipse cx="44" cy="72" rx="27" ry="19" fill="#9ca3af"/>
                </svg>
            @endif
        </div>

        {{-- ══════════ WHITE BODY
             padding-top = PHOTO_R + 6 = 44 + 6 = 50px ══════════ --}}
        <div style="background:#fff;padding-top:50px;flex:1;display:flex;flex-direction:column;min-height:0;">

            {{-- Name --}}
            <div style="text-align:center;padding:2px 14px 2px;font-size:14px;font-weight:700;color:#24378d;text-transform:uppercase;letter-spacing:0.2px;line-height:1.24;">
                {{ strtoupper($name) }}
            </div>

            {{-- Program --}}
            <div style="text-align:center;padding:2px 12px 0;font-size:11.7px;font-weight:700;color:#111;text-transform:uppercase;letter-spacing:0.15px;line-height:1.24;">
                {{ strtoupper($program) }}
            </div>

            {{-- Detail fields --}}
            <div style="padding:8px 14px 0;font-size:13px;color:#1b1b1b;font-weight:600;line-height:1.4;text-align:center;word-break:break-word;">
                <div>Student ID No.: <strong style="font-weight:600;">{{ $studentNo }}</strong></div>
                <div style="margin-top:2px;">Date of Birth:- <strong style="font-weight:600;">{{ $dob ?: '—' }}</strong></div>
                <div style="margin-top:2px;">Address:- <strong style="font-weight:600;">{{ $stdAddress ?: '—' }}</strong></div>
                <div style="margin-top:2px;">Valid up to: <strong style="font-weight:600;">{{ $validUpto ?: '—' }}</strong></div>
            </div>

            {{-- Barcode + Signature row --}}
            <div style="display:flex;justify-content:space-between;align-items:flex-end;padding:4px 12px 0;gap:10px;margin-top:auto;">
                @if(in_array($barcodeType, ['both', 'barcode']))
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;gap:1px;height:32px;align-items:stretch;overflow:hidden;">
                        {!! $barHtml !!}
                    </div>
                    <div style="font-size:6.4px;text-align:center;margin-top:2px;font-family:'Montserrat',Arial,sans-serif;font-weight:700;letter-spacing:0.9px;color:#333;">
                        {{ $studentNo }}
                    </div>
                </div>
                @else
                <div style="flex:1;min-width:0;"></div>
                @endif
                <div style="flex-shrink:0;text-align:center;width:79px;font-size:11px;font-weight:700;color:#3f3f46;">
                    <div style="height:30px;"></div>
                    <div>{{ $principal }}</div>
                </div>
            </div>

            {{-- RED ADDRESS FOOTER --}}
            <div style="background:{{ $headerColor }};color:#fff;text-align:center;font-size:11px;font-weight:500;line-height:1.58;letter-spacing:0.05px;padding:9px 4px 8px;flex-shrink:0;">
                <div>Budhiganga-4, Morang, Koshi Province, Nepal</div>
                <div>
                    Ph: {{ $phone ?: '021-622058' }}
                    @if($email) | Email: {{ $email }} @endif
                </div>
            </div>

            {{-- BLACK IDENTITY STRIP --}}
            <div style="background:#1a1a1a;color:#fff;text-align:center;font-family:'Georgia','Times New Roman',serif;font-size:15px;font-weight:700;letter-spacing:0.45px;display:flex;align-items:center;justify-content:center;height:34px;padding:0 4px;text-transform:uppercase;line-height:1;flex-shrink:0;">
                <span style="display:block;width:100%;white-space:nowrap;">STUDENT IDENTITY CARD</span>
            </div>

        </div>{{-- /white body --}}

    </div>{{-- /card-wrap --}}
</div>{{-- /card-page --}}
@endforeach

<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 600);
});
</script>
</body>
</html>
