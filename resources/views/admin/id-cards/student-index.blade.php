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
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="student?.program"></p>
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
                        {{-- Template --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Select Template</label>
                            <select x-model="template" @change="updateCardColor()" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-red-300">
                                <option value="red">Official ID Card (Red)</option>
                                <option value="blue">Official ID Card (Blue)</option>
                                <option value="green">Official ID Card (Green)</option>
                            </select>
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

                        {{-- Barcode / QR --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Barcode / QR</label>
                            <select x-model="barcodeType" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-red-300">
                                <option value="both">Both Barcode &amp; QR Code</option>
                                <option value="barcode">Barcode Only</option>
                                <option value="qr">QR Code Only</option>
                                <option value="none">None</option>
                            </select>
                        </div>

                        {{-- Card Type --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Card Type</label>
                            <select x-model="cardType" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-red-300">
                                <option value="regular">Regular</option>
                                <option value="premium">Premium</option>
                            </select>
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

                {{-- Live card preview ── new design matching student portal ── --}}
                <div x-show="student" x-cloak class="flex justify-center" id="id-card-preview">
                    <div style="
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
                        {{-- Header band (134px) --}}
                        <div :style="`position:relative;background:${cardColor};height:134px;box-sizing:border-box;flex-shrink:0;`">
                            {{-- College title --}}
                            <div style="position:absolute;top:10px;left:0;right:0;text-align:center;color:#fff;font-weight:800;font-size:18.8px;line-height:1.2;text-transform:uppercase;letter-spacing:0.45px;padding:0 16px;">
                                MANMOHAN MEMORIAL<br>POLYTECHNIC
                            </div>
                            {{-- Logo circle --}}
                            <div style="position:absolute;top:40px;left:12px;width:42px;height:42px;border-radius:50%;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;z-index:1;padding:2px;box-sizing:border-box;">
                                <img :src="logoUrl || '/favicon.ico'"
                                     style="width:100%;height:100%;object-fit:contain;display:block;"
                                     @error="$event.target.style.display='none'">
                            </div>
                        </div>

                        {{-- Floating photo (top=62, left=88, 112×112) --}}
                        <div style="position:absolute;top:62px;left:88px;width:112px;height:112px;border-radius:50%;background:#e5e7eb;overflow:hidden;z-index:10;border:1px solid rgba(122,15,21,0.25);box-shadow:0 1px 6px rgba(0,0,0,0.12);">
                            <img :src="student?.photo_url || ''"
                                 x-show="student?.photo_url"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                            <svg x-show="!student?.photo_url" viewBox="0 0 88 88" width="112" height="112">
                                <rect width="88" height="88" fill="#e5e7eb"/>
                                <circle cx="44" cy="32" r="17" fill="#9ca3af"/>
                                <ellipse cx="44" cy="72" rx="27" ry="19" fill="#9ca3af"/>
                            </svg>
                        </div>

                        {{-- White body --}}
                        <div style="background:#fff;padding-top:62px;flex:1;display:flex;flex-direction:column;min-height:0;">
                            <div style="flex:1;display:flex;flex-direction:column;min-height:0;">

                                {{-- Name --}}
                                <div style="text-align:center;padding:2px 14px 2px;font-size:14px;font-weight:700;color:#24378d;text-transform:uppercase;letter-spacing:0.2px;line-height:1.24;"
                                     x-text="(student?.name || 'STUDENT NAME').toUpperCase()"></div>

                                {{-- Program --}}
                                <div style="text-align:center;padding:2px 12px 0;font-size:11.7px;font-weight:700;color:#111;text-transform:uppercase;letter-spacing:0.15px;line-height:1.24;"
                                     x-text="(student?.program || 'PROGRAM / FACULTY').toUpperCase()"></div>

                                {{-- Detail fields --}}
                                <div style="padding:8px 14px 0;font-size:13px;color:#1b1b1b;font-weight:600;line-height:1.4;text-align:center;word-break:break-word;">
                                    <div>Student ID No.: <strong style="font-weight:600;" x-text="student?.student_no || '—'"></strong></div>
                                    <div style="margin-top:2px;">Date of Birth:- <strong style="font-weight:600;" x-text="student?.dob || '—'"></strong></div>
                                    <div style="margin-top:2px;">Address:- <strong style="font-weight:600;" x-text="student?.address || '—'"></strong></div>
                                    <div style="margin-top:2px;">Valid up to: <strong style="font-weight:600;" x-text="validUpto || '—'"></strong></div>
                                </div>

                                {{-- Barcode + Signature row --}}
                                <div style="display:flex;justify-content:space-between;align-items:flex-end;padding:2px 12px 1px;gap:10px;margin-top:auto;">
                                    {{-- Decorative barcode --}}
                                    <div style="flex:1;min-width:0;">
                                        <div style="display:flex;gap:1px;height:32px;align-items:stretch;overflow:hidden;">
                                            <template x-for="i in 68">
                                                <div :style="`background:${(i*7+(student?.student_no?.charCodeAt(i%(student?.student_no?.length||1))||65))%3!==2?'#000':'#fff'};width:${(i*3)%4===0?3:1.5}px;height:100%;flex-shrink:0;`"></div>
                                            </template>
                                        </div>
                                        <div style="font-size:6.4px;text-align:center;margin-top:2px;font-family:'Montserrat',Arial,sans-serif;font-weight:700;letter-spacing:0.9px;color:#333;"
                                             x-text="student?.student_no || '—'"></div>
                                    </div>
                                    {{-- Signature --}}
                                    <div style="flex-shrink:0;text-align:center;width:79px;font-size:11px;font-weight:700;color:#3f3f46;">
                                        <div style="height:20px;"></div>
                                        <div style="padding-top:0;margin-top:0;" x-text="principal || 'Principal'"></div>
                                    </div>
                                </div>

                            </div>

                            {{-- Red address footer --}}
                            <div :style="`background:${cardColor};color:#fff;text-align:center;font-size:11px;font-weight:500;line-height:1.58;letter-spacing:0.05px;padding:9px 4px 8px;flex-shrink:0;`">
                                <div style="transform:scaleX(1.02);transform-origin:center center;">Budhiganga-4, Morang, Koshi Province, Nepal</div>
                                <div style="transform:scaleX(1.02);transform-origin:center center;">
                                    Ph: <span x-text="phone || '021-622058'"></span>
                                    <template x-if="email"> | Email: <span x-text="email"></span></template>
                                </div>
                            </div>

                            {{-- Identity strip --}}
                            <div style="background:#1a1a1a;color:#fff;text-align:center;font-family:'Georgia','Times New Roman',serif;font-size:15px;font-weight:700;letter-spacing:0.45px;display:flex;align-items:center;justify-content:center;height:34px;padding:0 4px;text-transform:uppercase;line-height:1;flex-shrink:0;">
                                <span style="display:block;width:100%;white-space:nowrap;transform:scaleX(1.04);transform-origin:center center;">STUDENT IDENTITY CARD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── How It Works / Features / Works on all sections ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- How It Works --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">How It Works</h3>
            <ol class="space-y-3">
                @foreach([
                    ['Select Student', 'Search and select the student.'],
                    ['Fill Card Details', 'Choose template, validity, and other preferences.'],
                    ['Generate', 'Click generate to create ID card instantly.'],
                    ['Print / Download', 'Print or download the ID card as image/PDF.'],
                ] as $idx => $step)
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-red-700 text-xs font-bold text-white">{{ $idx + 1 }}</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $step[0] }}</p>
                        <p class="text-xs text-slate-400">{{ $step[1] }}</p>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>

        {{-- Features --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Features</h3>
            <ul class="space-y-2">
                @foreach([
                    'Official ID Card Format',
                    'Barcode & QR Code Support',
                    'Custom Validity Date',
                    'Multiple Card Templates',
                    'Bulk ID Card Generation',
                    'Print & Download Option',
                    'Works on All Sections',
                ] as $feature)
                <li class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Works on all sections --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Works on All Sections</h3>
            <div class="grid grid-cols-3 gap-3">
                @foreach([
                    ['Account', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['Hostel', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['Library', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['Attendance', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['Transport', 'M8 17M12 17M4 9v4m16-4v4M4 13h16M9 5h6M3 13h18M3 7h18a2 2 0 012 2v4a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z'],
                    ['Examination', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ] as [$label, $svgPath])
                <div class="flex flex-col items-center gap-1 rounded-xl border border-slate-100 p-3 dark:border-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $svgPath }}"/>
                    </svg>
                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Bulk Generation + Export Options ── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Bulk Generation --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Bulk Generation</h3>
            <p class="mb-4 text-xs text-slate-400">Generate ID cards for multiple students at once.</p>
            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('admin.id-cards.students.bulk-list') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Select Students
                </a>
                <span class="text-xs text-slate-400">or</span>
                <a href="{{ route('admin.id-cards.students.bulk-list') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload Students (Excel)
                </a>
            </div>
        </div>

        {{-- Export Options --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Export Options</h3>
            <p class="mb-4 text-xs text-slate-400">Download or print the generated ID card.</p>
            <div class="flex flex-wrap gap-3">
                <button
                    @click="generateCard()"
                    :disabled="!student"
                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Download as PDF
                </button>
                <button
                    @click="downloadImage()"
                    :disabled="!student || generating"
                    class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 hover:bg-green-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Download as Image
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
<script>
function idCardGen(config) {
    return {
        ...config,
        searchQuery: '',
        searchResults: [],
        showDropdown: false,
        searching: false,
        student: null,
        qrDataUrl: null,
        template: 'red',
        validUpto: config.defaultValidUpto || '',
        issueDate: config.defaultIssueDate  || '',
        barcodeType: 'both',
        cardType: 'regular',
        generating: false,

        get cardColor() {
            return { red: '#8B0000', blue: '#1e3a5f', green: '#14532d' }[this.template] || '#8B0000';
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

        selectStudent(s) {
            this.student     = s;
            this.searchQuery = s.name;
            this.showDropdown = false;
            this.qrDataUrl = null;
            this.loadQrCode(s.student_no);
        },

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

        clearStudent() {
            this.student      = null;
            this.searchQuery  = '';
            this.showDropdown = false;
        },

        generateCard() {
            if (!this.student) return;
            const cardEl = document.querySelector('#id-card-preview .w-72');
            if (!cardEl) return;
            const win = window.open('', '_blank', 'width=800,height=900');
            win.document.write(`<!DOCTYPE html>
<html>
<head>
<title>Export ID Card — ${this.student.name}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    background: #e5e7eb;
    font-family: 'Segoe UI', Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 24px;
    min-height: 100vh;
}
.card-wrap {
    width: 288px;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2);
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #fff;
}
.card-wrap * { box-sizing: border-box; }
.tip {
    position: fixed;
    bottom: 18px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: #fff;
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 12px;
    white-space: nowrap;
    pointer-events: none;
}
@media print {
    @page { size: A4 portrait; margin: 0; }
    html, body {
        background: #fff;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 80px;
        width: 210mm;
        min-height: 0;
    }
    .card-wrap { width: 86mm; border-radius: 0; box-shadow: none; }
    .tip { display: none; }
}
</style>
</head>
<body>
<div class="card-wrap">${cardEl.innerHTML}</div>
<div class="tip">Press Ctrl+P &rarr; "Save as PDF" to export</div>
</body>
</html>`);
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 600);
        },

        async downloadImage() {
            if (!this.student) return;
            const el = document.querySelector('#id-card-preview .w-72');
            if (!el) return;
            this.generating = true;
            try {
                if (typeof html2canvas === 'undefined') { this.printCard(); return; }
                const canvas = await html2canvas(el, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: '#ffffff',
                    logging: false,
                });
                const link = document.createElement('a');
                link.download = 'id-card-' + (this.student.student_no || this.student.id) + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } catch (e) {
                console.error('Image download error:', e);
                this.printCard();
            }
            this.generating = false;
        },

        printCard() {
            if (!this.student) return;
            const cardEl = document.querySelector('#id-card-preview .w-72');
            if (!cardEl) return;
            const win = window.open('', '_blank', 'width=360,height=700');
            win.document.write(`<!DOCTYPE html>
<html>
<head>
<title>Print ID Card</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
    background: #e5e7eb;
    font-family: 'Segoe UI', Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 24px;
    height: auto;
}
.card-wrap {
    width: 288px;
    overflow: hidden;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2);
    font-family: 'Segoe UI', Arial, sans-serif;
    flex-shrink: 0;
    background: #fff;
}
.card-wrap * { box-sizing: border-box; }
@media print {
    @page { size: 86mm auto; margin: 0; }
    html, body {
        background: #fff;
        padding: 0;
        display: block;
        width: 86mm;
        height: auto;
        min-height: 0;
    }
    .card-wrap { width: 86mm; border-radius: 0; box-shadow: none; }
}
</style>
</head>
<body>
<div class="card-wrap">${cardEl.innerHTML}</div>
</body>
</html>`);
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 500);
        },
    };
}
</script>
@endpush
@endsection
