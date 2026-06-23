@extends('layouts.app')
@section('title', 'My ID Card')

@push('styles')
<style>
    @media print {
        body * { visibility: hidden !important; }
        body::after {
            visibility: visible !important;
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #64748b;
            content: 'Printing is not allowed for this page.';
        }
    }
    #id-card-preview { user-select: none; -webkit-user-select: none; }
</style>
@endpush

@section('content')
@php
    /*
     * React constants (px):
     *   CARD_W  = 288
     *   CARD_H  = (86/54) * 288  ≈ 457.78  → we use 458
     *   HDR_H   = 134
     *   LOGO_D  = 42
     *   PHOTO_D = 112
     *   PHOTO_R = 56   (PHOTO_D / 2)
     *   PHOTO_L = 88   ((CARD_W - PHOTO_D) / 2)
     *
     * photo top = HDR_H - PHOTO_R - 16 = 134 - 56 - 16 = 62
     * body paddingTop = PHOTO_R + 6     = 56 + 6       = 62
     */
    $barcodeValue = $student->student_no ?? $student->id ?? '0000000';
    $barcodeStr   = (string) $barcodeValue;
    $bLen         = strlen($barcodeStr);

    /* build the same decorative barcode bars the React version would show */
    $bars = [];
    for ($i = 0; $i < 68; $i++) {
        $charVal  = ord($barcodeStr[$i % $bLen]);
        $barBg    = (($i * 7 + $charVal) % 3 !== 2) ? '#000' : '#fff';
        $barWidth = (($i * 3) % 4 === 0) ? '3px' : '1.5px';
        $bars[]   = ['bg' => $barBg, 'w' => $barWidth];
    }
@endphp

<div class="space-y-6">
    <div class="flex min-h-[70vh] items-center justify-center">
        <div class="rounded-2xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-6 shadow-sm">
            <h2 class="mb-5 text-center text-sm font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                Student Identity Card
            </h2>

            <div id="id-card-preview" oncontextmenu="return false;" class="flex justify-center">

                {{--
                    ═══════════════════════════════════════════════════
                    ID CARD  — exact pixel replica of React IDCardPreview
                    ═══════════════════════════════════════════════════
                --}}
                <div id="mmp-id-card" style="
                    position: relative;
                    width: 288px;
                    height: 458px;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
                    font-family: 'Montserrat', Arial, sans-serif;
                    font-weight: 700;
                    user-select: none;
                    flex-shrink: 0;
                    background: #fff;
                    display: flex;
                    flex-direction: column;
                ">

                    {{-- ── HEADER BAND (HDR_H = 134px) ─────────────── --}}
                    <div style="
                        position: relative;
                        background: #a0161d;
                        height: 134px;
                        box-sizing: border-box;
                        print-color-adjust: exact;
                        -webkit-print-color-adjust: exact;
                        flex-shrink: 0;
                    ">
                        {{-- College title --}}
                        <div data-card-title style="
                            position: absolute;
                            top: 10px;
                            left: 0;
                            right: 0;
                            text-align: center;
                            color: #fff;
                            font-weight: 800;
                            font-size: 18.8px;
                            line-height: 1.2;
                            text-transform: uppercase;
                            letter-spacing: 0.45px;
                            padding: 0 16px;
                        ">
                            MANMOHAN MEMORIAL<br>POLYTECHNIC
                        </div>

                        {{-- Logo circle (LOGO_D = 42, left = 12, top = 40) --}}
                        <div data-card-logo style="
                            position: absolute;
                            top: 40px;
                            left: 12px;
                            width: 42px;
                            height: 42px;
                            border-radius: 50%;
                            background: #fff;
                            overflow: hidden;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            z-index: 1;
                            padding: 2px;
                            box-sizing: border-box;
                        ">
                            <img src="{{ $logoUrl ?: asset('favicon.ico') }}"
                                 alt="MMP"
                                 crossorigin="anonymous"
                                 style="width:100%;height:100%;object-fit:contain;display:block;"
                                 onerror="this.style.display='none'">
                        </div>
                    </div>

                    {{-- ── FLOATING PHOTO (top = 62, left = 88, 112×112, circle) ── --}}
                    <div data-card-photo style="
                        position: absolute;
                        top: 62px;
                        left: 88px;
                        width: 112px;
                        height: 112px;
                        border-radius: 50%;
                        background: #e5e7eb;
                        overflow: hidden;
                        z-index: 10;
                        border: 1px solid rgba(122,15,21,0.25);
                        box-shadow: 0 1px 6px rgba(0,0,0,0.12);
                    ">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}"
                                 alt="{{ $student->user->name }}"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                        @else
                            <svg viewBox="0 0 88 88" width="112" height="112">
                                <rect width="88" height="88" fill="#e5e7eb"/>
                                <circle cx="44" cy="32" r="17" fill="#9ca3af"/>
                                <ellipse cx="44" cy="72" rx="27" ry="19" fill="#9ca3af"/>
                            </svg>
                        @endif
                    </div>

                    {{-- ── WHITE BODY (paddingTop = PHOTO_R + 6 = 62) ── --}}
                    <div style="
                        background: #fff;
                        padding-top: 62px;
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        min-height: 0;
                    ">
                        <div style="flex:1;display:flex;flex-direction:column;min-height:0;">

                            {{-- Student full name --}}
                            <div style="
                                text-align: center;
                                padding: 2px 14px 2px;
                                font-size: 14px;
                                font-weight: 700;
                                color: #24378d;
                                text-transform: uppercase;
                                letter-spacing: 0.2px;
                                line-height: 1.24;
                            ">{{ strtoupper($student->user->name) }}</div>

                            {{-- Program --}}
                            <div style="
                                text-align: center;
                                padding: 2px 12px 0;
                                font-size: 11.7px;
                                font-weight: 700;
                                color: #111;
                                text-transform: uppercase;
                                letter-spacing: 0.15px;
                                line-height: 1.24;
                            ">{{ strtoupper($student->program->name ?? 'PROGRAM / FACULTY') }}</div>

                            {{-- Detail fields --}}
                            <div style="
                                padding: 8px 14px 0;
                                font-size: 13px;
                                color: #1b1b1b;
                                font-weight: 600;
                                line-height: 1.4;
                                text-align: center;
                                word-break: break-word;
                            ">
                                <div>Student ID No.: <strong style="font-weight:600;">{{ $barcodeStr }}</strong></div>
                                <div style="margin-top:2px;">Date of Birth:- <strong style="font-weight:600;">{{ $dob ?: '—' }}</strong></div>
                                <div style="margin-top:2px;">Address:- <strong style="font-weight:600;">{{ $student->user->address ?: '—' }}</strong></div>
                                <div style="margin-top:2px;">Valid up to: <strong style="font-weight:600;">{{ $validUpto ?: '—' }}</strong></div>
                            </div>

                            {{-- Barcode + Signature row --}}
                            <div style="
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-end;
                                padding: 2px 12px 1px;
                                gap: 10px;
                                margin-top: auto;
                            ">
                                {{-- Barcode --}}
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;gap:1px;height:32px;align-items:stretch;overflow:hidden;">
                                        @foreach($bars as $bar)
                                            <div style="background:{{ $bar['bg'] }};width:{{ $bar['w'] }};height:100%;flex-shrink:0;"></div>
                                        @endforeach
                                    </div>
                                    <div style="
                                        font-size: 6.4px;
                                        text-align: center;
                                        margin-top: 2px;
                                        font-family: 'Montserrat', Arial, sans-serif;
                                        font-weight: 700;
                                        letter-spacing: 0.9px;
                                        color: #333;
                                    ">{{ $barcodeStr }}</div>
                                </div>

                                {{-- Signature / Principal --}}
                                <div style="
                                    flex-shrink: 0;
                                    text-align: center;
                                    width: 79px;
                                    font-size: 11px;
                                    font-weight: 700;
                                    color: #3f3f46;
                                ">
                                    @if(isset($signatureUrl) && $signatureUrl)
                                        <img src="{{ $signatureUrl }}"
                                             alt="Signature"
                                             style="height:32px;max-width:88px;object-fit:contain;display:block;margin:0 auto;padding:0;">
                                    @else
                                        <div style="height:20px;"></div>
                                    @endif
                                    <div style="padding-top:0;margin-top:0;">Principal</div>
                                </div>
                            </div>

                        </div>{{-- /inner --}}

                        {{-- ── ADDRESS FOOTER (red, matches React) ─── --}}
                        <div style="
                            background: #a0161d;
                            color: #fff;
                            text-align: center;
                            font-size: 11px;
                            font-weight: 500;
                            line-height: 1.58;
                            letter-spacing: 0.05px;
                            padding: 9px 4px 8px;
                            print-color-adjust: exact;
                            -webkit-print-color-adjust: exact;
                            flex-shrink: 0;
                        ">
                            <div style="transform:scaleX(1.02);transform-origin:center center;">
                                Budhiganga-4, Morang, Koshi Province, Nepal
                            </div>
                            <div style="transform:scaleX(1.02);transform-origin:center center;">
                                Ph: {{ $phone ?: '021-622058' }}
                                @if($email) | Email: {{ $email }} @endif
                            </div>
                        </div>

                        {{-- ── IDENTITY STRIP (black bar) ─────────── --}}
                        <div data-card-identity-bar style="
                            background: #1a1a1a;
                            color: #fff;
                            text-align: center;
                            font-family: 'Georgia', 'Times New Roman', serif;
                            font-size: 15px;
                            font-weight: 700;
                            letter-spacing: 0.45px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            height: 34px;
                            padding: 0 4px;
                            text-transform: uppercase;
                            line-height: 1;
                            print-color-adjust: exact;
                            -webkit-print-color-adjust: exact;
                            flex-shrink: 0;
                        ">
                            <span style="
                                display: block;
                                width: 100%;
                                white-space: nowrap;
                                transform: scaleX(1.04);
                                transform-origin: center center;
                            ">STUDENT IDENTITY CARD</span>
                        </div>

                    </div>{{-- /white body --}}

                </div>{{-- /#mmp-id-card --}}

            </div>{{-- /#id-card-preview --}}

            <p class="mt-5 text-center text-xs text-slate-400">
                This card is for identification purposes only. Contact admin to print or download.
            </p>
        </div>
    </div>
</div>
@endsection
