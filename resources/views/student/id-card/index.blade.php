@extends('layouts.app')
@section('title', 'My ID Card')

@push('styles')
<style>
    /* Completely block printing */
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

    /* Prevent text selection & right-click on the card */
    #id-card-preview {
        user-select: none;
        -webkit-user-select: none;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-red-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Student Portal</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">My ID Card</h1>
                    <p class="mt-1 text-sm text-slate-600">View your student identity card</p>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        View only — printing &amp; downloading are disabled
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Card display --}}
    <div class="flex justify-center">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="mb-5 text-center text-sm font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Student Identity Card</h2>

            <div id="id-card-preview" oncontextmenu="return false;">
                {{-- Card: matches admin preview design --}}
                <div class="w-72 overflow-hidden rounded-2xl shadow-2xl" style="font-family: 'Segoe UI', Arial, sans-serif;">

                    {{-- Header --}}
                    <div style="background:#8B0000; padding:14px 12px 52px; display:flex; align-items:center; gap:10px;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.6);object-fit:cover;flex-shrink:0;">
                        @else
                            <div style="width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="color:white;font-weight:900;font-size:14px;">{{ strtoupper(substr($collegeName,0,1)) }}</span>
                            </div>
                        @endif
                        <div style="color:white;font-weight:700;font-size:13px;line-height:1.25;letter-spacing:0.3px;">
                            {{ strtoupper($collegeName) }}<br>
                            <span style="font-size:10px;font-weight:400;opacity:0.85;">{{ $affiliation }}</span>
                        </div>
                    </div>

                    {{-- White body --}}
                    <div style="background:white; position:relative; padding-top:58px;">

                        {{-- Photo overlapping the header --}}
                        <div style="position:absolute; top:-48px; left:0; right:0; display:flex; justify-content:center;">
                            <div style="width:96px;height:96px;border-radius:50%;background:white;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <img
                                    src="{{ $photoUrl ?: 'https://ui-avatars.com/api/?name=' . urlencode($student->user->name) . '&background=8B0000&color=fff&size=120' }}"
                                    style="width:96px;height:96px;border-radius:50%;object-fit:cover;display:block;"
                                    alt="{{ $student->user->name }}">
                            </div>
                        </div>

                        {{-- Name & Program --}}
                        <div style="text-align:center; padding:4px 14px 6px;">
                            <div style="font-size:16px;font-weight:900;color:#1e3a5f;letter-spacing:1px;text-transform:uppercase;">
                                {{ strtoupper($student->user->name) }}
                            </div>
                            <div style="font-size:10.5px;font-weight:800;color:#1a1a1a;text-transform:uppercase;margin-top:2px;letter-spacing:0.8px;">
                                {{ strtoupper($student->program->name ?? 'N/A') }}
                            </div>
                        </div>

                        {{-- Details --}}
                        <div style="padding:2px 16px 6px; font-size:10px; color:#1e293b; line-height:1.85; text-align:center;">
                            <div><span style="color:#475569;">Student ID No:</span> &nbsp;<strong>{{ $student->student_no ?? '—' }}</strong></div>
                            @if($dob)
                            <div><span style="color:#475569;">Date of Birth:</span> &nbsp;<strong>{{ $dob }}</strong></div>
                            @endif
                            @if($student->user->address)
                            <div><span style="color:#475569;">Address:</span> &nbsp;<strong>{{ $student->user->address }}</strong></div>
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
                            {{-- Barcode (decorative) --}}
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;gap:1px;height:28px;align-items:stretch;overflow:hidden;">
                                    @php
                                        $code = $student->student_no ?? 'STD001';
                                        $len  = strlen($code);
                                    @endphp
                                    @for($i = 0; $i < 40; $i++)
                                        @php
                                            $charVal = ord($code[$i % $len]);
                                            $bg      = ($i * 7 + $charVal) % 3 !== 2 ? '#000' : '#fff';
                                            $w       = ($i * 3) % 4 === 0 ? '3px' : '1.5px';
                                        @endphp
                                        <div style="background:{{ $bg }};width:{{ $w }};height:100%;flex-shrink:0;"></div>
                                    @endfor
                                </div>
                                <div style="font-size:7px;text-align:center;margin-top:2px;font-family:monospace;letter-spacing:1px;">{{ $code }}</div>
                            </div>

                            {{-- QR Code --}}
                            <div style="flex-shrink:0;">
                                <img
                                    src="https://api.qrserver.com/v1/create-qr-code/?size=55x55&data={{ urlencode($student->student_no ?? $student->id) }}"
                                    style="width:50px;height:50px;" alt="QR">
                            </div>

                            {{-- Signature --}}
                            <div style="flex-shrink:0;text-align:center;font-size:8px;color:#475569;width:56px;">
                                <div style="border-top:1px solid #475569;padding-top:3px;">{{ $principal }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div style="background:#8B0000;color:white;padding:7px 10px;font-size:8px;text-align:center;line-height:1.6;">
                        {{ $address }}
                        @if($phone)
                            <br>Ph: {{ $phone }}
                        @endif
                        @if($email)
                            | Email: {{ $email }}
                        @endif
                    </div>

                    {{-- Bottom strip --}}
                    <div style="background:#1a1a1a;color:white;text-align:center;padding:7px;font-size:11px;font-weight:700;letter-spacing:3px;">
                        STUDENT IDENTITY CARD
                    </div>
                </div>
            </div>

            <p class="mt-5 text-center text-xs text-slate-400">
                This card is for identification purposes only. Contact admin to print or download.
            </p>
        </div>
    </div>

</div>
@endsection
