@extends('layouts.app')
@section('title', 'Student ID Card Generation')

@section('content')
@php
    $collegeName  = $settings['college_name']        ?? 'Manmohan Memorial Polytechnic';
    $affiliation  = $settings['college_affiliation']  ?? 'CTEVT';
    $address      = $settings['contact_address']      ?? '';
    $phone        = $settings['contact_phone']        ?? '';
    $email        = $settings['contact_email']        ?? '';
    $principal    = $settings['principal_name']       ?? 'Principal';
    $logoUrl      = ($settings['site_logo'] ?? null)
                    ? publicStorageUrl($settings['site_logo'])
                    : null;
    $signatureUrl  = ($settings['principal_signature'] ?? null)
                    ? publicStorageUrl($settings['principal_signature'])
                    : null;
    $defaultValidUptoBS = bsDate($defaultYear . '-06-30') ?? '';
    $defaultIssueDateBS = bsDate(now()->format('Y-m-d')) ?? '';
@endphp

<div
    x-data="idCardGen({
        searchUrl: '{{ route('admin.id-cards.students.search') }}',
        bulkPdfUrl: '{{ route('admin.id-cards.students.bulk-pdf') }}',
        bulkListUrl: '{{ route('admin.id-cards.students.bulk-list') }}',
        defaultYear: '{{ $defaultYear }}',
        collegeName: @js($collegeName),
        affiliation: @js($affiliation),
        address: @js($address),
        phone: @js($phone),
        email: @js($email),
        principal: @js($principal),
        logoUrl: @js($logoUrl),
        signatureUrl: @js($signatureUrl),
        programs: @js($programs ?? []),
        defaultValidUpto: @js($defaultValidUptoBS),
        defaultIssueDate: @js($defaultIssueDateBS),
    })"
    class="space-y-6"
>
    {{-- ── Page header ── --}}
    <div class="text-center">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            STUDENT ID CARD GENERATION SYSTEM
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Works on all sections: Account, Hostel, Library, Transport, Attendance, Exams and More
        </p>
    </div>

    {{-- ── Top-right action bar ── --}}
    <div class="flex justify-end gap-3">
        <button
            @click="printCard()"
            :disabled="!student"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        <button
            @click="generateCard()"
            :disabled="!student"
            class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
        </button>
    </div>

    {{-- ── Two-panel main area ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- LEFT: Generation form ─────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-800 dark:text-slate-100">ID Card Generation</h2>

                {{-- Select Student --}}
                <div class="mb-5">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Select Student</label>

                    {{-- Search input --}}
                    <div class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.300ms="searchStudents()"
                            @focus="searchStudents()"
                            @keydown.escape="showDropdown = false"
                            placeholder="Search by Name, ID…"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-sm shadow-sm placeholder-slate-400 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <span class="absolute inset-y-0 right-3 flex items-center text-slate-400">
                            <svg x-show="!searching" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <svg x-show="searching" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>

                        {{-- Search dropdown --}}
                        <div
                            x-show="showDropdown && searchResults.length > 0"
                            x-cloak
                            @click.away="showDropdown = false"
                            class="absolute left-0 right-0 top-full z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
                            <template x-for="s in searchResults" :key="s.id">
                                <button
                                    type="button"
                                    @click="selectStudent(s)"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-red-50 dark:hover:bg-slate-700 transition">
                                    <img :src="s.photo_url || 'https://ui-avatars.com/api/?name=S&background=8B0000&color=fff'" class="h-8 w-8 rounded-lg object-cover flex-shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-slate-800 dark:text-slate-100" x-text="s.name"></p>
                                        <p class="text-xs text-slate-400" x-text="s.student_no + ' · ' + s.program"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Selected student card --}}
                    <div x-show="student" x-cloak class="mt-3 flex gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                        <img :src="student?.photo_url || 'https://ui-avatars.com/api/?name=S&background=8B0000&color=fff'" class="h-14 w-14 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="student?.name"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Student ID: ' + (student?.student_no || '—')"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="getProgramName(student?.program_id)"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="student?.batch ? 'Batch: ' + student.batch : ''"></p>
                        </div>
                        <button @click="clearStudent()" class="flex-shrink-0 text-slate-400 hover:text-red-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Card Information ─ shown only after student selected --}}
                <div x-show="student" x-cloak>
                    <div class="mb-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Card Information</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Student Information</p>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Full Name *</label>
                                    <input type="text" x-model="student.name" placeholder="Full name" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Student ID *</label>
                                    <input type="text" x-model="student.student_no" @input="barcodeNumber = $event.target.value; generateBarcode(barcodeNumber, student.id)" placeholder="Student ID" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Program / Faculty *</label>
                                    <select x-model.number="student.program_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none">
                                        <option value="">Select Program</option>
                                        @foreach($programs as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Date of Birth *</label>
                                    <input type="text" x-model="student.dob" placeholder="YYYY-MM-DD" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Address</label>
                                    <textarea x-model="student.address" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" placeholder="Address"></textarea>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Phone</label>
                                    <input type="text" x-model="student.phone" placeholder="Phone number" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Barcode Number</label>
                                    <input type="text" x-model="barcodeNumber" @input="generateBarcode($event.target.value, student.id)" placeholder="Barcode / ID for printing" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:outline-none" />
                                </div>

                                {{-- Principal signature is managed in Settings → Principal's Corner --}}
                            </div>
                        </div>

                        {{-- Valid Up To --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Valid Up To</label>
                            <div @change="validUpto = $event.target.value">
                                <x-bs-date-picker name="valid_upto" :value="$defaultValidUptoBS" placeholder="YYYY-MM-DD" />
                            </div>
                        </div>

                        {{-- Issue Date --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Issue Date</label>
                            <div @change="issueDate = $event.target.value">
                                <x-bs-date-picker name="issue_date" :value="$defaultIssueDateBS" placeholder="YYYY-MM-DD" />
                            </div>
                        </div>

                        
                    </div>

                    {{-- Generate button --}}
                    <button
                        @click="printCard()"
                        :disabled="!student"
                        class="mt-5 w-full rounded-xl py-3 text-sm font-bold text-white shadow transition disabled:opacity-60 disabled:cursor-not-allowed"
                        :style="`background: ${cardColor};`">
                        Print ID Card
                    </button>
                </div>

                {{-- Placeholder when no student --}}
                <div x-show="!student" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12 text-slate-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306-2.566 2-4 2"/>
                    </svg>
                    <p class="text-sm">Search and select a student above<br>to configure their ID card.</p>
                </div>
            </div>
        </div>

        {{-- RIGHT: ID Card Preview ───────────────────────────────── --}}
        <div class="lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-800 dark:text-slate-100">ID Card Preview</h2>

                {{-- Placeholder --}}
                <div x-show="!student" class="flex h-80 flex-col items-center justify-center text-center text-slate-300 dark:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306-2.566 2-4 2"/>
                    </svg>
                    <p class="text-sm">Select a student to preview the ID card</p>
                </div>

                                {{-- ══════════════════════════════════════════════════════════
                                         Live card preview — exact React IDCardPreview replica
                                         Constants (matching React):
                                             CARD_W=288  HDR_H=120  LOGO_D=42
                                             PHOTO_D=112  PHOTO_R=56
                                             photo top  = HDR_H - PHOTO_R = 64  (so header bottom intersects photo center)
                                             photo left = (CARD_W - PHOTO_D) / 2 = 88
                                             body paddingTop = PHOTO_R + 6 = 62
                                ══════════════════════════════════════════════════════════ --}}
                <div x-show="student" x-cloak class="flex justify-center" id="id-card-preview">
                    <div id="card-print-area" style="
                        position: relative;
                        width: 288px;
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
                        {{-- ══ RED HEADER — 120px ══ --}}
                        <div :style="`position:relative;background:${cardColor};height:120px;box-sizing:border-box;flex-shrink:0;print-color-adjust:exact;-webkit-print-color-adjust:exact;`">

                            {{-- College name — centred, top 10px --}}
                            <div style="position:absolute;top:10px;left:0;right:0;text-align:center;color:#fff;font-weight:800;font-size:18.8px;line-height:1.2;text-transform:uppercase;letter-spacing:0.45px;padding:0 16px;">
                                MANMOHAN MEMORIAL<br>POLYTECHNIC
                            </div>

                            {{-- Logo — top:40px, left:12px, 42×42 --}}
                            <div style="position:absolute;top:40px;left:12px;width:42px;height:42px;border-radius:50%;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:1;padding:2px;box-sizing:border-box;">
                                <img :src="logoUrl || '/favicon.ico'"
                                     style="width:100%;height:100%;object-fit:contain;display:block;"
                                     x-on:error="$event.target.style.display='none'">
                            </div>
                        </div>

                        {{-- ══ PHOTO — straddles header edge
                             top  = HDR_H - PHOTO_R - 16 = 120 - 44 - 16 = 60px
                             left = (288 - 88) / 2 = 100px
                             size = 88×88 ══ --}}
                        <div style="position:absolute;top:64px;left:88px;width:112px;height:112px;border-radius:50%;background:#e5e7eb;overflow:hidden;z-index:10;border:1px solid rgba(122,15,21,0.25);box-shadow:0 1px 6px rgba(0,0,0,0.12);">
                            <img :src="student?.photo_url || ''"
                                 x-show="student?.photo_url"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                            <svg x-show="!student?.photo_url" viewBox="0 0 88 88" width="88" height="88">
                                <rect width="88" height="88" fill="#e5e7eb"/>
                                <circle cx="44" cy="32" r="17" fill="#9ca3af"/>
                                <ellipse cx="44" cy="72" rx="27" ry="19" fill="#9ca3af"/>
                            </svg>
                        </div>

                        {{-- ══ WHITE BODY — padding-top = PHOTO_R + 6 = 50px ══ --}}
                        <div style="background:#fff;padding-top:62px;flex:1;display:flex;flex-direction:column;">
                            <div style="flex:1;display:flex;flex-direction:column;">

                                {{-- Name — 14px bold navy --}}
                                <div style="text-align:center;padding:2px 14px 2px;font-size:14px;font-weight:700;color:#24378d;text-transform:uppercase;letter-spacing:0.2px;line-height:1.24;"
                                     x-text="(student?.name || 'STUDENT NAME').toUpperCase()"></div>

                                {{-- Program — 11.7px bold black --}}
                                <div style="text-align:center;padding:2px 12px 0;font-size:11.7px;font-weight:700;color:#111;text-transform:uppercase;letter-spacing:0.15px;line-height:1.24;"
                                     x-text="(getProgramName(student?.program_id) || 'PROGRAM / FACULTY').toUpperCase()"></div>

                                {{-- Detail fields — 13px, font-weight:600, centred --}}
                                <div style="padding:8px 14px 0;font-size:13px;color:#1b1b1b;font-weight:600;line-height:1.4;text-align:center;word-break:break-word;">
                                    <div>Student ID No.: <strong style="font-weight:600;" x-text="student?.student_no || '—'"></strong></div>
                                    <div style="margin-top:2px;">Date of Birth:- <strong style="font-weight:600;" x-text="student?.dob || '—'"></strong></div>
                                    <div style="margin-top:2px;">Address:- <strong style="font-weight:600;" x-text="student?.address || '—'"></strong></div>
                                    <div style="margin-top:2px;">Valid up to: <strong style="font-weight:600;" x-text="validUpto || '—'"></strong></div>
                                </div>

                                {{-- Barcode + Signature row --}}
                                <div style="display:flex;justify-content:space-between;align-items:flex-end;padding:4px 12px 0;gap:10px;margin-top:auto;">

                                    {{-- Scannable barcode using SVG format --}}
                                    <div style="flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;">
                                        <svg :id="'barcode-' + (student?.id || 'temp')" style="height:32px;margin-bottom:2px;"></svg>
                                        <div style="font-size:6.4px;text-align:center;font-family:'Montserrat',Arial,sans-serif;font-weight:700;letter-spacing:0.9px;color:#333;"
                                             x-text="barcodeNumber || student?.student_no || '—'"></div>
                                    </div>

                                    {{-- Signature + Principal label --}}
                                    <div style="flex-shrink:0;text-align:center;width:79px;font-size:11px;font-weight:700;color:#3f3f46;">
                                        <div style="height:42px;display:flex;align-items:center;justify-content:center;">
                                            <img x-show="signatureDataUrl" :src="signatureDataUrl" style="max-height:36px;object-fit:contain;display:block;"/>
                                        </div>
                                        <div>Principal</div>
                                    </div>
                                </div>

                            </div>

                            {{-- Red address footer --}}
                            <div :style="`background:${cardColor};color:#fff;text-align:center;font-size:11px;font-weight:500;line-height:1.58;letter-spacing:0.05px;padding:9px 4px 8px;flex-shrink:0;print-color-adjust:exact;-webkit-print-color-adjust:exact;`">
                                <div style="transform:scaleX(1.02);transform-origin:center center;">Budhiganga-4, Morang, Koshi Province, Nepal</div>
                                <div style="transform:scaleX(1.02);transform-origin:center center;">
                                    Ph: <span x-text="phone || '021-622058'"></span>
                                    <template x-if="email">&nbsp;| Email: <span x-text="email"></span></template>
                                </div>
                            </div>

                            {{-- Black identity strip --}}
                            <div style="background:#1a1a1a;color:#fff;text-align:center;font-family:'Georgia','Times New Roman',serif;font-size:15px;font-weight:700;letter-spacing:0.45px;display:flex;align-items:center;justify-content:center;height:34px;padding:0 4px;text-transform:uppercase;line-height:1;flex-shrink:0;print-color-adjust:exact;-webkit-print-color-adjust:exact;">
                                <span style="display:block;width:100%;white-space:nowrap;transform:scaleX(1.04);transform-origin:center center;">STUDENT IDENTITY CARD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
<script>
// Register idCardGen before Alpine initializes (alpine:init fires before Alpine processes x-data)
document.addEventListener('alpine:init', () => {
    window.idCardGen = idCardGen;
});

function idCardGen(config) {
    return {
        ...config,
        searchQuery: '',
        searchResults: [],
        showDropdown: false,
        searching: false,
        student: null,
        qrDataUrl: null,
        signatureDataUrl: config.signatureUrl || null,
        barcodeNumber: '',
        template: 'red',
        validUpto: config.defaultValidUpto || '',
        issueDate: config.defaultIssueDate  || '',
        barcodeType: 'both',
        cardType: 'regular',
        generating: false,

        get cardColor() {
            return { red: '#8B0000', blue: '#1e3a5f', green: '#14532d' }[this.template] || '#8B0000';
        },

        getProgramName(id) {
            if (!id) return '';
            const p = (this.programs || []).find(x => x.id === id || x.id == id);
            return p ? p.name : '';
        },

        updateCardColor() { /* reactivity handled by cardColor getter */ },

        async searchStudents() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                this.showDropdown  = false;
                return;
            }
            this.searching = true;
            try {
                const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                this.searchResults = await res.json();
                this.showDropdown  = this.searchResults.length > 0;
            } catch (e) {
                this.searchResults = [];
            }
            this.searching = false;
        },

        async selectStudent(s) {
            this.searchQuery = s.name;
            this.showDropdown = false;
            this.qrDataUrl = null;

            // fetch full student data from server to populate all fields
            try {
                const res = await fetch(`{{ url('admin/students') }}/${s.id}/json`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    // merge into student object used by the UI
                    this.student = data;
                    this.loadQrCode(data.student_no);
                    this.barcodeNumber = data.student_no || '';
                    this.generateBarcode(this.barcodeNumber, data.id);
                    return;
                }
            } catch (e) {
                // fallback to the basic search result if JSON fetch fails
            }

            // fallback: use the provided search result object
            this.student     = s;
            // try to resolve program_id from program name in search result
            if (!this.student.program_id && s.program) {
                const match = (this.programs || []).find(p => p.name === s.program || p.name == s.program);
                if (match) this.student.program_id = match.id;
            }
            this.loadQrCode(s.student_no);
            this.barcodeNumber = s.student_no || '';
            this.generateBarcode(this.barcodeNumber, s.id);
        },

        // signature upload handled from Settings → Principal's Corner

        async loadQrCode(studentNo) {
            try {
                const url = `https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(studentNo)}`;
                const res = await fetch(url);
                const blob = await res.blob();
                this.qrDataUrl = await new Promise(resolve => {
                    const reader = new FileReader();
                    reader.onload = e => resolve(e.target.result);
                    reader.readAsDataURL(blob);
                });
            } catch (e) { /* QR fetch failed, will use direct URL */ }
        },

        generateBarcode(studentNo, studentId) {
            // accept optional args, fall back to bound values
            const no = studentNo || this.barcodeNumber || (this.student && this.student.student_no) || '';
            const id = studentId || (this.student && this.student.id) || 'temp';
            setTimeout(() => {
                const barcodeEl = document.querySelector(`#barcode-${id}`);
                if (barcodeEl && typeof JsBarcode !== 'undefined' && no) {
                    try {
                        JsBarcode(`#barcode-${id}`, no, {
                            format: 'CODE128',
                            width: 2,
                            height: 50,
                            displayValue: false,
                            margin: 0
                        });
                    } catch (e) {
                        console.error('Barcode generation error:', e);
                    }
                }
            }, 100);
        },

        clearStudent() {
            this.student      = null;
            this.searchQuery  = '';
            this.showDropdown = false;
        },

        generateCard() {
            this._openCardWindow('save-pdf');
        },

        printCard() {
            this._openCardWindow('print');
        },

        /**
         * Single popup builder — clones the live #card-print-area DOM (already
         * rendered by Alpine) so every output path uses exactly the same markup
         * as the preview card.
         */
        _openCardWindow(mode) {
            if (!this.student) return;
            const cardEl = document.querySelector('#card-print-area');
            if (!cardEl) return;

            const title  = mode === 'save-pdf'
                ? `Export ID Card — ${this.student.name}`
                : `Print ID Card — ${this.student.name}`;
            
            const studentNo = this.barcodeNumber || this.student?.student_no || '';

            // Clone the card element with all its styles preserved
            const cardClone = cardEl.cloneNode(true);
            
            const win = window.open('', '_blank', 'width=860,height=1100');
            win.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>${title}</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
html, body {
    background: #e5e7eb;
    font-family: 'Montserrat', Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 24px;
    min-height: 100vh;
    width: 100%;
    margin: 0;
}
.card-wrap {
    position: relative;
    width: 288px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.22);
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 700;
    flex-shrink: 0;
    background: #fff;
    display: flex;
    flex-direction: column;
}
.card-wrap * { 
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
.save-tip {
    position: fixed;
    bottom: 18px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.72);
    color: #fff;
    padding: 8px 20px;
    border-radius: 20px;
    font-family: Arial, sans-serif;
    font-size: 12px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 9999;
}
@media print {
    @page {
        size: 86mm 136mm;
        margin: 0;
        padding: 0;
    }
    html {
        margin: 0;
        padding: 0;
        width: 86mm;
        height: 136mm;
    }
    body {
        background: #fff;
        margin: 0;
        padding: 0;
        width: 86mm;
        height: 136mm;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .card-wrap {
        width: 288px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.22);
        position: relative;
        display: flex;
        flex-direction: column;
        page-break-after: avoid;
    }
    .save-tip {
        display: none;
    }
}
</style>
</head>
<body>
<div class="card-wrap">${cardEl.innerHTML}</div>
${mode === 'save-pdf' ? '<div class="save-tip">Ctrl+P &rarr; Destination: &ldquo;Save as PDF&rdquo;</div>' : ''}
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        // Regenerate barcodes in the print window
        const studentNo = '${studentNo}';
        const barcodes = document.querySelectorAll('svg[id^="barcode-"]');
        barcodes.forEach(barcode => {
            try {
                if (typeof JsBarcode !== 'undefined' && studentNo) {
                    JsBarcode('#' + barcode.id, studentNo, {
                        format: 'CODE128',
                        width: 2,
                        height: 50,
                        displayValue: false,
                        margin: 0
                    });
                }
            } catch (e) { }
        });
        window.print();
    }, 500);
});
<\/script>
</body>
</html>`);
            win.document.close();
            win.focus();
        },
    };
}
</script>
@endpush
@endsection
