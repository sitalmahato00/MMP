@extends('layouts.guest')
@section('title', 'Online Result')
@section('meta_description', 'Search the official CTEVT online result portal from the MMP public website.')
@section('breadcrumb', true)

@section('content')
@php
    $form = $resultForm ?? [];
    $fields = collect($form['fields'] ?? []);
    $hiddenFields = collect($form['hidden_fields'] ?? []);
    $formTitle = $form['title'] ?? 'Yearly/Semester Check Results';
    $officialResultUrl = config('services.ctevt_result.url', 'https://itms.ctevt.org.np:5580/search_results');
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 items-start">
        <div class="xl:col-span-2 order-1 xl:order-none">
            <div class="rounded-3xl overflow-hidden shadow-xl border border-blue-100 bg-gradient-to-br from-[#003D82] via-[#A21818] to-[#5A0000] text-white">
                <div class="p-8 md:p-10 space-y-5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-50 border border-white/15">
                        Official CTEVT Portal
                    </div>
                    <div class="space-y-3">
                        <h1 class="font-serif text-3xl md:text-4xl font-black leading-tight">Online Result Search</h1>
                        <p class="text-blue-50/90 leading-relaxed text-sm md:text-base max-w-xl">
                            Use the exact CTEVT inputs below. The form validates on MMP and then opens the official result page in a new tab.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Fast access</div>
                            <div class="text-blue-50/80 mt-1">Submit once and land on the live CTEVT result page.</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Mobile friendly</div>
                            <div class="text-blue-50/80 mt-1">The layout adapts cleanly on smaller screens.</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Exact inputs</div>
                            <div class="text-blue-50/80 mt-1">Examination Year, Level, Symbol Number, Date of Birth (B.S.).</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Official URL</div>
                            <div class="text-blue-50/80 mt-1 break-all">{{ $officialResultUrl }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-8 md:px-10 py-4 bg-black/10 text-xs md:text-sm text-blue-50/90 border-t border-white/10">
                    The form uses the same field names and values as the official CTEVT page.
                </div>
            </div>
        </div>

        <div class="xl:col-span-3 order-2 xl:order-none">
            <div class="section-header flex items-center justify-between" style="background-color: #003D82;">
                <span>🔎 Result Search Form</span>
                <span class="text-blue-200 text-xs font-semibold">Public Page</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-red-50/40">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">{{ $formTitle }}</h2>
                            <p class="text-xs md:text-sm text-gray-500 mt-1">Enter the same details shown on the official CTEVT page.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.18em] border bg-emerald-50 text-emerald-700 border-emerald-100">
                                Official Input Set
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-b from-white to-red-50/20 p-5 md:p-6 space-y-4">
                    @if($errors->any())
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/80 px-4 py-3 text-sm text-blue-900">
                            Please correct the highlighted fields and try again.
                        </div>
                    @else
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                            This form uses the same official CTEVT field names and opens the result in a new tab.
                        </div>
                    @endif

                    <div class="rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)] text-sm text-gray-600">
                        Official inputs: <span class="font-semibold text-gray-800">src_year</span>, <span class="font-semibold text-gray-800">src_level</span>, <span class="font-semibold text-gray-800">exam_symbol_number</span>, <span class="font-semibold text-gray-800">dob</span>.
                    </div>
                    <form name="frmCheckResults" id="frmCheckResults" action="{{ route('public.result.submit') }}" method="post" target="_blank" autocomplete="off" class="space-y-4">
                        @csrf

                        @foreach($hiddenFields as $hiddenField)
                            <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
                        @endforeach

                        @php
                            $selectedYear = old('src_year', '2082');
                            $selectedLevel = old('src_level');
                            $symbolValue = old('exam_symbol_number');
                            $dobValue = old('dob');
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="src_year" class="text-sm md:text-base font-semibold text-gray-700 leading-snug">Examination Year:</label>
                            <div class="w-full">
                                <select name="src_year" id="src_year" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150 {{ $errors->has('src_year') ? 'border-blue-400 bg-blue-50' : '' }}">
                                    <option value="">-- Select --</option>
                                    @foreach(['2082', '2081', '2080', '2079', '2078', '2077'] as $year)
                                        <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('src_year')<p class="mt-1 text-xs text-blue-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="src_level" class="text-sm md:text-base font-semibold text-gray-700 leading-snug">Level :</label>
                            <div class="w-full">
                                <select name="src_level" id="src_level" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150 {{ $errors->has('src_level') ? 'border-blue-400 bg-blue-50' : '' }}">
                                    <option value="">-- Select --</option>
                                    <option value="2" @selected($selectedLevel === '2')>Pre-diploma</option>
                                    <option value="3" @selected($selectedLevel === '3')>Diploma/PCL</option>
                                </select>
                                @error('src_level')<p class="mt-1 text-xs text-blue-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="exam_symbol_number" class="text-sm md:text-base font-semibold text-gray-700 leading-snug">Symbol Number :</label>
                            <div class="w-full">
                                <input type="text" name="exam_symbol_number" id="exam_symbol_number" value="{{ $symbolValue }}" placeholder="e.g. 1000234" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150 {{ $errors->has('exam_symbol_number') ? 'border-blue-400 bg-blue-50' : '' }}">
                                @error('exam_symbol_number')<p class="mt-1 text-xs text-blue-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="dob" class="text-sm md:text-base font-semibold text-gray-700 leading-snug">Date of Birth (B.S.) :</label>
                            <div class="w-full">
                                <x-bs-date-picker name="dob" :value="$dobValue" placeholder="YYYY-MM-DD" :required="true"
                                                  class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150"/>
                                @error('dob')<p class="mt-1 text-xs text-blue-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="pt-2 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-start">
                            <button type="submit" name="submit" value="Search" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-[#003D82] px-6 py-3 text-white font-semibold shadow-sm hover:bg-[#6f0000] transition-colors">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

