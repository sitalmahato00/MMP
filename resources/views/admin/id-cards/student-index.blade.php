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
                    ? Storage::disk('public')->url($settings['site_logo'])
                    : null;
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
            :disabled="!student || generating"
            class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span x-text="generating ? 'Generating…' : 'Download'"></span>
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
                            <input type="text" x-model="validUpto" placeholder="e.g. 2083-06-30 BS"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-red-300">
                        </div>

                        {{-- Issue Date --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Issue Date</label>
                            <input type="text" x-model="issueDate" placeholder="e.g. 2080-06-01 BS"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-red-300">
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
                        @click="generateCard()"
                        :disabled="generating"
                        class="mt-5 w-full rounded-xl py-3 text-sm font-bold text-white shadow transition disabled:opacity-60"
                        :style="`background: ${cardColor};`">
                        <span x-text="generating ? 'Generating…' : 'Generate ID Card'"></span>
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

                {{-- Live card preview ── matches the PDF design ── --}}
                <div x-show="student" x-cloak class="flex justify-center" id="id-card-preview">
                    <div class="w-72 overflow-hidden rounded-2xl shadow-2xl" style="font-family: 'Segoe UI', Arial, sans-serif;">

                        {{-- Header --}}
                        <div :style="`background: ${cardColor}; padding: 14px 12px; display: flex; align-items: center; gap: 10px;`">
                            <img
                                :src="logoUrl || 'https://ui-avatars.com/api/?name=MMP&background=fff&color=8B0000&size=60'"
                                style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.6);object-fit:cover;flex-shrink:0;">
                            <div style="color:white;font-weight:700;font-size:13px;line-height:1.25;letter-spacing:0.3px;">
                                <span x-text="collegeName.toUpperCase()"></span><br>
                                <span style="font-size:10px;font-weight:400;opacity:0.85;" x-text="affiliation"></span>
                            </div>
                        </div>

                        {{-- Photo --}}
                        <div style="background:white;display:flex;justify-content:center;padding:18px 0 10px;">
                            <img
                                :src="student?.photo_url || 'https://ui-avatars.com/api/?name=S&background=8B0000&color=fff&size=120'"
                                :style="`width:90px;height:90px;border-radius:50%;border:3px solid ${cardColor};object-fit:cover;`">
                        </div>

                        {{-- Name & Program --}}
                        <div style="background:white;text-align:center;padding:0 14px 10px;">
                            <div style="font-size:15px;font-weight:700;color:#0f172a;letter-spacing:0.5px;" x-text="student?.name?.toUpperCase() || 'STUDENT NAME'"></div>
                            <div style="font-size:10px;color:#475569;text-transform:uppercase;margin-top:3px;letter-spacing:0.5px;" x-text="student?.program?.toUpperCase() || 'PROGRAM NAME'"></div>
                        </div>

                        {{-- Divider --}}
                        <div :style="`height:2px;background:${cardColor};margin:0 14px;`"></div>

                        {{-- Details --}}
                        <div style="background:white;padding:10px 18px;font-size:10.5px;color:#1e293b;line-height:1.9;">
                            <div>Student ID No: &nbsp;<strong x-text="student?.student_no || '—'"></strong></div>
                            <div x-show="student?.dob">Date of Birth: &nbsp;<strong x-text="student?.dob"></strong></div>
                            <div x-show="validUpto">Valid up to: &nbsp;<strong x-text="validUpto"></strong></div>
                            <div x-show="issueDate">Issue Date: &nbsp;<strong x-text="issueDate"></strong></div>
                        </div>

                        {{-- Barcode + QR + Signature --}}
                        <div style="background:white;padding:8px 14px;display:flex;justify-content:space-between;align-items:flex-end;gap:6px;">
                            {{-- Barcode --}}
                            <div x-show="barcodeType === 'barcode' || barcodeType === 'both'" style="flex:1;min-width:0;">
                                <div style="display:flex;gap:1px;height:28px;align-items:stretch;overflow:hidden;">
                                    <template x-for="i in 40">
                                        <div :style="`background: ${(i*7 + (student?.student_no?.charCodeAt(i % (student?.student_no?.length||1)) || 65)) % 3 !== 2 ? '#000' : '#fff'}; width: ${(i*3) % 4 === 0 ? 3 : 1.5}px; height: 100%; flex-shrink: 0;`"></div>
                                    </template>
                                </div>
                                <div style="font-size:7px;text-align:center;margin-top:2px;font-family:monospace;letter-spacing:1px;" x-text="student?.student_no"></div>
                            </div>
                            {{-- QR --}}
                            <div x-show="barcodeType === 'qr' || barcodeType === 'both'" style="flex-shrink:0;">
                                <img
                                    :src="student ? `https://api.qrserver.com/v1/create-qr-code/?size=55x55&data=${encodeURIComponent(student.student_no)}` : ''"
                                    style="width:50px;height:50px;" alt="QR">
                            </div>
                            {{-- Signature --}}
                            <div style="flex-shrink:0;text-align:center;font-size:8px;color:#475569;width:56px;">
                                <div style="border-top:1px solid #475569;padding-top:3px;" x-text="principal || 'Principal'"></div>
                            </div>
                        </div>

                        {{-- College info footer --}}
                        <div :style="`background:${cardColor};color:white;padding:7px 10px;font-size:8px;text-align:center;line-height:1.6;`">
                            <span x-text="address"></span><br>
                            <template x-if="phone">
                                <span>Ph: <span x-text="phone"></span></span>
                            </template>
                            <template x-if="email">
                                <span> | Email: <span x-text="email"></span></span>
                            </template>
                        </div>

                        {{-- Bottom strip --}}
                        <div style="background:#1a1a1a;color:white;text-align:center;padding:7px;font-size:11px;font-weight:700;letter-spacing:3px;">
                            STUDENT IDENTITY CARD
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
                    :disabled="!student || generating"
                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Download as PDF
                </button>
                <button
                    @click="printCard()"
                    :disabled="!student"
                    class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 hover:bg-green-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Download as Image
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden generate form --}}
    <form id="generate-form" method="POST" action="{{ route('admin.id-cards.students.bulk-pdf') }}" target="_blank">
        @csrf
        <input type="hidden" name="valid_upto"   x-bind:value="validUpto">
        <input type="hidden" name="issue_date"   x-bind:value="issueDate">
        <input type="hidden" name="barcode_type" x-bind:value="barcodeType">
        <input type="hidden" name="template"     x-bind:value="template">
    </form>
</div>

@push('scripts')
<script>
function idCardGen(config) {
    return {
        ...config,
        searchQuery: '',
        searchResults: [],
        showDropdown: false,
        searching: false,
        student: null,
        template: 'red',
        validUpto: config.defaultYear + '-06-30',
        issueDate: new Date().getFullYear() + '-01-01',
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
        },

        clearStudent() {
            this.student      = null;
            this.searchQuery  = '';
            this.showDropdown = false;
        },

        generateCard() {
            if (!this.student || this.generating) return;
            this.generating = true;
            const form = document.getElementById('generate-form');
            form.querySelectorAll('input[name="ids[]"]').forEach(e => e.remove());
            const inp = document.createElement('input');
            inp.type  = 'hidden'; inp.name = 'ids[]'; inp.value = this.student.id;
            form.appendChild(inp);
            form.submit();
            setTimeout(() => { this.generating = false; }, 3000);
        },

        printCard() {
            if (!this.student) return;
            const el  = document.getElementById('id-card-preview');
            const win = window.open('', '_blank', 'width=400,height=700');
            win.document.write('<html><head><title>Print ID Card</title><style>body{margin:0;padding:20px;font-family:sans-serif;}@media print{@page{margin:0;size:auto;}body{margin:10mm;}}</style></head><body>' + el.innerHTML + '</body></html>');
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 500);
        },
    };
}
</script>
@endpush
@endsection
