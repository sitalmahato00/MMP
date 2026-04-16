@extends('layouts.guest')
@section('title', 'Online Result')
@section('meta_description', 'Search the official CTEVT online result portal from the MMP public website.')
@section('breadcrumb', true)

@section('content')
@php
    $form = $resultForm ?? [];
    $fields = collect($form['fields'] ?? []);
    $hiddenFields = collect($form['hidden_fields'] ?? []);
    $formAction = $form['action'] ?? config('services.ctevt_result.url', 'https://itms.ctevt.org.np:5580/search_results');
    $formMethod = strtolower((string) ($form['method'] ?? 'post')) ?: 'post';
    $formAutocomplete = $form['autocomplete'] ?? 'off';
    $formTitle = $form['title'] ?? 'Yearly/Semester Check Results';
    $submitLabel = $form['submit']['label'] ?? 'Search';
    $formSource = (string) ($form['source_state'] ?? $form['source'] ?? 'fallback');
    $isLiveForm = $formSource === 'live';
    $officialSiteUrl = config('services.ctevt_result.check_url', 'https://itms.ctevt.org.np:5580/check_results');
    $normalizeLabel = function (string $label): string {
        $label = trim($label);
        $label = preg_replace('/\s*:\s*$/u', '', $label) ?? $label;
        $label = preg_replace('/\s+/u', ' ', $label) ?? $label;

        return $label;
    };
    $placeholderFor = function (array $field): string {
        return match ($field['name'] ?? '') {
            'exam_symbol_number' => 'Enter your symbol number',
            'dob' => 'YYYY-MM-DD (B.S.)',
            default => trim((string) ($field['placeholder'] ?? '')),
        };
    };
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 items-start">
        <div class="xl:col-span-2 order-1 xl:order-none">
            <div class="rounded-3xl overflow-hidden shadow-xl border border-red-100 bg-gradient-to-br from-[#8B0000] via-[#A21818] to-[#5A0000] text-white">
                <div class="p-8 md:p-10 space-y-5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold uppercase tracking-[0.2em] text-red-50 border border-white/15">
                        Official CTEVT Portal
                    </div>
                    @if($isLiveForm)
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-50 border border-emerald-200/20">
                            Live CTEVT Data
                        </div>
                    @endif
                    <div class="space-y-3">
                        <h1 class="font-serif text-3xl md:text-4xl font-black leading-tight">Online Result Search</h1>
                        <p class="text-red-50/90 leading-relaxed text-sm md:text-base max-w-xl">
                            Use the official CTEVT form below to check yearly or semester results. The search opens on the CTEVT portal and nothing is stored on this site.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Fast access</div>
                            <div class="text-red-50/80 mt-1">Select your details and jump straight to the official search page.</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Mobile friendly</div>
                            <div class="text-red-50/80 mt-1">The layout adapts cleanly on smaller screens.</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">No storage</div>
                            <div class="text-red-50/80 mt-1">This page only forwards your search to the CTEVT system.</div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <div class="font-semibold text-white">Official URL</div>
                            <div class="text-red-50/80 mt-1 break-all">{{ $formAction }}</div>
                        </div>
                    </div>
                </div>
                <div class="px-8 md:px-10 py-4 bg-black/10 text-xs md:text-sm text-red-50/90 border-t border-white/10">
                    Search results open on the official CTEVT domain in a new tab.
                </div>
            </div>
        </div>

        <div class="xl:col-span-3 order-2 xl:order-none">
            <div class="section-header flex items-center justify-between" style="background-color: #8B0000;">
                <span>🔎 Result Search Form</span>
                <span class="text-red-200 text-xs font-semibold">Public Page</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-red-50/40">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div>
                            <h2 class="text-lg md:text-xl font-semibold text-gray-800">{{ $formTitle }}</h2>
                            <p class="text-xs md:text-sm text-gray-500 mt-1">Enter the official details to check the result in a new tab.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.18em] border {{ $isLiveForm ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                Official CTEVT Data
                            </span>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.18em] border {{ $isLiveForm ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100' }}">
                                {{ $isLiveForm ? 'Live' : 'Fallback' }}
                            </span>
                        </div>
                    </div>
                </div>

                <form name="frmCheckResults" id="frmCheckResults" action="{{ $formAction }}" method="{{ $formMethod }}" target="_blank" autocomplete="{{ $formAutocomplete }}" x-data="{ loading: false }" @submit="loading = true; setTimeout(() => loading = false, 3000)" class="bg-gradient-to-b from-white to-red-50/20 p-5 md:p-6 space-y-4">
                    <div class="flex flex-col gap-3">
                        @if($isLiveForm)
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900 w-full sm:w-auto sm:flex-1">
                                This form is loaded live from the official CTEVT page.
                            </div>
                        @else
                            <div class="rounded-2xl border border-red-100 bg-red-50/80 px-4 py-3 text-sm text-red-900 w-full sm:w-auto sm:flex-1">
                                Unable to load live CTEVT form. Please try again later or use the official website.
                            </div>
                        @endif
                    </div>

                    @foreach($hiddenFields as $hiddenField)
                        <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
                    @endforeach

                    @foreach($fields as $field)
                        @php
                            $isSelect = ($field['type'] ?? '') === 'select';
                            $fieldName = (string) ($field['name'] ?? '');
                            $fieldLabel = $normalizeLabel((string) ($field['label'] ?? ''));
                            $fieldPlaceholder = $placeholderFor($field);
                            $isRequired = (bool) ($field['required'] ?? false);
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="{{ $field['id'] ?? $field['name'] }}" class="text-sm md:text-base font-semibold text-gray-700 leading-snug">
                                <span>{{ $fieldLabel }}</span>
                                @if($isRequired)
                                    <span class="ml-1 text-red-500">*</span>
                                @endif
                            </label>
                            <div class="w-full">
                                @if($isSelect)
                                    <x-select name="{{ $field['name'] }}" :required="$isRequired" class="w-full">
                                        @foreach(($field['options'] ?? []) as $option)
                                            <option value="{{ $option['value'] }}" @selected(($option['selected'] ?? false))>{{ $option['label'] }}</option>
                                        @endforeach
                                    </x-select>
                                @else
                                    <x-input type="{{ $field['input_type'] ?? 'text' }}" name="{{ $fieldName }}" placeholder="{{ $fieldPlaceholder }}" :required="$isRequired" class="w-full" />
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-start">
                        <button type="submit" name="submit" value="{{ $submitLabel }}" :disabled="loading" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-[#8B0000] px-6 py-3 text-white font-semibold shadow-sm hover:bg-[#6f0000] transition-colors disabled:cursor-not-allowed disabled:opacity-70">
                            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M8 12H4m13.657-5.657l-2.828 2.828M9.17 14.83l-2.828 2.828m0-11.314l2.828 2.828m7.071 7.071l2.828 2.828" />
                            </svg>
                            <span x-text="loading ? 'Checking result...' : 'Check Result'">Check Result</span>
                        </button>

                        <a href="{{ $officialSiteUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-[#8B0000] hover:border-[#8B0000] hover:bg-red-50 transition-colors">
                            Go to Official CTEVT Website
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
