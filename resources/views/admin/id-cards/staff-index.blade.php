@extends('layouts.app')
@section('title', 'Staff ID Card Generation')

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
@endphp

<div
    x-data="idCardGen({
        searchUrl: '{{ route('admin.id-cards.staff.search') }}',
        bulkPdfUrl: '{{ route('admin.id-cards.staff.bulk-pdf') }}',
        bulkListUrl: '{{ route('admin.id-cards.staff.bulk-list') }}',
        defaultYear: '{{ $defaultYear }}',
        collegeName: @js($collegeName),
        affiliation: @js($affiliation),
        address: @js($address),
        phone: @js($phone),
        email: @js($email),
        principal: @js($principal),
        logoUrl: @js($logoUrl),
        defaultTemplate: 'blue',
    })"
    class="space-y-6"
>
    {{-- Page header --}}
    <div class="text-center">
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
            STAFF ID CARD GENERATION SYSTEM
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Generate professional ID cards for teaching and non-teaching staff
        </p>
    </div>

    {{-- Top-right action bar --}}
    <div class="flex justify-end gap-3">
        <button
            @click="printCard()"
            :disabled="!staff"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print
        </button>
        <button
            @click="generateCard()"
            :disabled="!staff"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-950 disabled:opacity-40 disabled:cursor-not-allowed transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download PDF
        </button>
    </div>

    {{-- Two-panel main area --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- LEFT: Generation form --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-800 dark:text-slate-100">ID Card Generation</h2>

                <div class="mb-5">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Select Staff</label>

                    <div class="relative">
                        <input
                            type="text"
                            x-model="searchQuery"
                            @input.debounce.300ms="searchStaff()"
                            @focus="searchStaff()"
                            @keydown.escape="showDropdown = false"
                            placeholder="Search by Name, Code, Designation…"
                            class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-sm shadow-sm placeholder-slate-400 focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <span class="absolute inset-y-0 right-3 flex items-center text-slate-400">
                            <svg x-show="!searching" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <svg x-show="searching" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>

                        <div
                            x-show="showDropdown && searchResults.length > 0"
                            x-cloak
                            @click.away="showDropdown = false"
                            class="absolute left-0 right-0 top-full z-50 mt-1 rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800 overflow-hidden">
                            <template x-for="s in searchResults" :key="s.id">
                                <button
                                    type="button"
                                    @click="selectStaff(s)"
                                    class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-blue-50 dark:hover:bg-slate-700 transition">
                                    <img :src="s.photo_url || 'https://ui-avatars.com/api/?name=S&background=1e3a5f&color=fff'" class="h-8 w-8 rounded-lg object-cover flex-shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-semibold text-slate-800 dark:text-slate-100" x-text="s.name"></p>
                                        <p class="text-xs text-slate-400" x-text="s.designation + ' · ' + (s.department || '—')"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="staff" x-cloak class="mt-3 flex gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                        <img :src="staff?.photo_url || 'https://ui-avatars.com/api/?name=S&background=1e3a5f&color=fff'" class="h-14 w-14 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="staff?.name"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="'Staff Code: ' + (staff?.staff_code || '—')"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="staff?.designation"></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="staff?.department"></p>
                        </div>
                        <button @click="clearStaff()" class="flex-shrink-0 text-slate-400 hover:text-blue-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="staff" x-cloak>
                    <div class="mb-3 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Card Information</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Select Template</label>
                            <select x-model="template" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                <option value="blue">Official ID Card (Blue)</option>
                                <option value="red">Official ID Card (Red)</option>
                                <option value="green">Official ID Card (Green)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Valid Up To</label>
                            <input type="text" x-model="validUpto" placeholder="e.g. 2083-06-30 BS" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Issue Date</label>
                            <input type="text" x-model="issueDate" placeholder="e.g. 2080-06-01 BS" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Barcode / QR</label>
                            <select x-model="barcodeType" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                <option value="both">Both Barcode &amp; QR Code</option>
                                <option value="barcode">Barcode Only</option>
                                <option value="qr">QR Code Only</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">Card Type</label>
                            <select x-model="cardType" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-blue-300">
                                <option value="regular">Regular</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                    </div>

                    <button
                        @click="printCard()"
                        :disabled="!staff"
                        class="mt-5 w-full rounded-xl py-3 text-sm font-bold text-white shadow transition disabled:opacity-60 disabled:cursor-not-allowed"
                        :style="`background: ${cardColor};`">
                        Print ID Card
                    </button>
                </div>

                <div x-show="!staff" class="flex flex-col items-center justify-center py-10 text-center text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-12 w-12 text-slate-200 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm">Search and select a staff member above<br>to configure their ID card.</p>
                </div>
            </div>
        </div>

        {{-- RIGHT: ID Card Preview --}}
        <div class="lg:col-span-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="mb-4 text-base font-semibold text-slate-800 dark:text-slate-100">ID Card Preview</h2>

                <div x-show="!staff" class="flex h-80 flex-col items-center justify-center text-center text-slate-300 dark:text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306-2.566 2-4 2"/>
                    </svg>
                    <p class="text-sm">Select a staff member to preview the ID card</p>
                </div>

                <div x-show="staff" x-cloak class="flex justify-center" id="id-card-preview">
                    <div class="w-72 overflow-hidden rounded-2xl shadow-2xl" style="font-family: 'Segoe UI', Arial, sans-serif;">

                        {{-- Header: extra bottom padding creates space so photo overlaps --}}
                        <div :style="`background:${cardColor}; padding:14px 12px 52px; display:flex; align-items:center; gap:10px;`">
                            <img
                                :src="logoUrl || 'https://ui-avatars.com/api/?name=MMP&background=fff&color=1e3a5f&size=60'"
                                style="width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.6);object-fit:cover;flex-shrink:0;">
                            <div style="color:white;font-weight:700;font-size:13px;line-height:1.25;letter-spacing:0.3px;">
                                <span x-text="collegeName.toUpperCase()"></span><br>
                                <span style="font-size:10px;font-weight:400;opacity:0.85;" x-text="affiliation"></span>
                            </div>
                        </div>

                        {{-- White body: photo centered at the header/body boundary via absolute position --}}
                        <div style="background:white; position:relative; padding-top:58px;">

                            {{-- Photo circle overlapping the header bottom --}}
                            <div style="position:absolute; top:-48px; left:0; right:0; display:flex; justify-content:center;">
                                <div style="width:96px;height:96px;border-radius:50%;background:white;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <img
                                        :src="staff?.photo_url || 'https://ui-avatars.com/api/?name=S&background=1e3a5f&color=fff&size=120'"
                                        style="width:96px;height:96px;border-radius:50%;object-fit:cover;display:block;">
                                </div>
                            </div>

                            {{-- Name & Designation --}}
                            <div style="text-align:center; padding:4px 14px 6px;">
                                <div style="font-size:16px;font-weight:900;color:#1e3a5f;letter-spacing:1px;text-transform:uppercase;" x-text="staff?.name?.toUpperCase() || 'STAFF NAME'"></div>
                                <div style="font-size:10.5px;font-weight:800;color:#1a1a1a;text-transform:uppercase;margin-top:2px;letter-spacing:0.8px;" x-text="staff?.designation?.toUpperCase() || 'DESIGNATION'"></div>
                            </div>

                            {{-- Details --}}
                            <div style="padding:2px 16px 6px; font-size:10px; color:#1e293b; line-height:1.85; text-align:center;">
                                <div><span style="color:#475569;">Staff Code:</span> &nbsp;<strong x-text="staff?.staff_code || '—'"></strong></div>
                                <div x-show="staff?.department"><span style="color:#475569;">Department:</span> &nbsp;<strong x-text="staff?.department"></strong></div>
                                <div x-show="address"><span style="color:#475569;">Campus:</span> &nbsp;<strong x-text="address"></strong></div>
                                <div x-show="validUpto"><span style="color:#475569;">Valid up to:</span> &nbsp;<strong x-text="validUpto"></strong></div>
                            </div>

                            {{-- Barcode + QR + Signature --}}
                            <div style="padding:8px 14px; display:flex; justify-content:space-between; align-items:flex-end; gap:6px;">
                                <div x-show="barcodeType === 'barcode' || barcodeType === 'both'" style="flex:1;min-width:0;">
                                    <div style="display:flex;gap:1px;height:28px;align-items:stretch;overflow:hidden;">
                                        <template x-for="i in 40">
                                            <div :style="`background: ${(i*7 + (staff?.staff_code?.charCodeAt(i % (staff?.staff_code?.length||1)) || 65)) % 3 !== 2 ? '#000' : '#fff'}; width: ${(i*3) % 4 === 0 ? 3 : 1.5}px; height: 100%; flex-shrink: 0;`"></div>
                                        </template>
                                    </div>
                                    <div style="font-size:7px;text-align:center;margin-top:2px;font-family:monospace;letter-spacing:1px;" x-text="staff?.staff_code"></div>
                                </div>
                                <div x-show="barcodeType === 'qr' || barcodeType === 'both'" style="flex-shrink:0;">
                                    <img
                                        :src="staff ? `https://api.qrserver.com/v1/create-qr-code/?size=55x55&data=${encodeURIComponent(staff.staff_code || staff.id)}` : ''"
                                        style="width:50px;height:50px;" alt="QR">
                                </div>
                                <div style="flex-shrink:0;text-align:center;font-size:8px;color:#475569;width:56px;">
                                    <div style="border-top:1px solid #475569;padding-top:3px;" x-text="principal || 'Principal'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- College info footer --}}
                        <div :style="`background:${cardColor};color:white;padding:7px 10px;font-size:8px;text-align:center;line-height:1.6;`">
                            <span x-text="address"></span><br>
                            <template x-if="phone"><span>Ph: <span x-text="phone"></span></span></template>
                            <template x-if="email"><span> | Email: <span x-text="email"></span></span></template>
                        </div>

                        {{-- Bottom strip --}}
                        <div style="background:#1a1a1a;color:white;text-align:center;padding:7px;font-size:11px;font-weight:700;letter-spacing:3px;">
                            STAFF IDENTITY CARD
                        </div>

                    </div>{{-- end .w-72 --}}
                </div>{{-- end #id-card-preview --}}
            </div>
        </div>
    </div>

    {{-- How It Works / Features / Works on all sections --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">How It Works</h3>
            <ol class="space-y-3">
                @foreach([['Select Staff','Search and select the staff member.'],['Fill Card Details','Choose template, validity, and preferences.'],['Generate','Click generate to create ID card instantly.'],['Print / Download','Print or download the ID card as PDF.']] as $idx => $step)
                <li class="flex items-start gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-blue-900 text-xs font-bold text-white">{{ $idx + 1 }}</span>
                    <div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $step[0] }}</p>
                        <p class="text-xs text-slate-400">{{ $step[1] }}</p>
                    </div>
                </li>
                @endforeach
            </ol>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Features</h3>
            <ul class="space-y-2">
                @foreach(['Official Staff ID Card Format','Barcode & QR Code Support','Custom Validity Date','Multiple Card Templates','Bulk ID Card Generation','Print & Download Option','Teaching & Non-Teaching Staff'] as $f)
                <li class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $f }}
                </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-4 text-center text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Staff Categories</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach([['Teaching Staff','M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],['Admin Staff','M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],['Lab Staff','M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],['Support Staff','M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z']] as [$label, $svgPath])
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

    {{-- Bulk Generation + Export Options --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Bulk Generation</h3>
            <p class="mb-4 text-xs text-slate-400">Generate ID cards for multiple staff at once.</p>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.id-cards.staff.bulk-list') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Select Staff
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300">Export Options</h3>
            <p class="mb-4 text-xs text-slate-400">Download or print the generated ID card.</p>
            <div class="flex flex-wrap gap-3">
                <button @click="generateCard()" :disabled="!staff"
                   class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-700 hover:bg-blue-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download as PDF
                </button>
                <button @click="printCard()" :disabled="!staff"
                   class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-700 hover:bg-green-100 disabled:opacity-40 disabled:cursor-not-allowed dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Print Card
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden generate form --}}
    <form id="generate-form" method="POST" action="{{ route('admin.id-cards.staff.bulk-pdf') }}" target="_blank">
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
        staff: null,
        template: config.defaultTemplate || 'blue',
        validUpto: config.defaultYear + '-06-30',
        issueDate: new Date().getFullYear() + '-01-01',
        barcodeType: 'both',
        cardType: 'regular',
        generating: false,

        get cardColor() {
            return { red: '#8B0000', blue: '#1e3a5f', green: '#14532d' }[this.template] || '#1e3a5f';
        },

        async searchStaff() {
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

        selectStaff(s) {
            this.staff       = s;
            this.searchQuery = s.name;
            this.showDropdown = false;
        },

        clearStaff() {
            this.staff        = null;
            this.searchQuery  = '';
            this.showDropdown = false;
        },

        generateCard() {
            if (!this.staff) return;
            const cardEl = document.querySelector('#id-card-preview .w-72');
            if (!cardEl) return;
            const win = window.open('', '_blank', 'width=800,height=900');
            win.document.write(`<!DOCTYPE html>
<html>
<head>
<title>Export ID Card — ${this.staff.name}</title>
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

        printCard() {
            if (!this.staff) return;
            const cardEl = document.querySelector('#id-card-preview .w-72');
            if (!cardEl) return;
            const win = window.open('', '_blank', 'width=360,height=640');
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
    padding: 24px;
    min-height: 100vh;
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
    html, body { background: #fff; padding: 0; display: block; width: 86mm; }
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
