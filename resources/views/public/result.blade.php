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
    $formTarget = $form['target'] ?? '_blank';
    $formAutocomplete = $form['autocomplete'] ?? 'off';
    $formTitle = $form['title'] ?? 'Yearly/Semester Check Results';
    $submitLabel = $form['submit']['label'] ?? 'Search';
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 items-start">
        <div class="xl:col-span-2">
            <div class="rounded-3xl overflow-hidden shadow-xl border border-red-100 bg-gradient-to-br from-[#8B0000] via-[#A21818] to-[#5A0000] text-white">
                <div class="p-8 md:p-10 space-y-5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold uppercase tracking-[0.2em] text-red-50 border border-white/15">
                        Official CTEVT Portal
                    </div>
                    @if(($form['source'] ?? '') === 'live')
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

        <div class="xl:col-span-3">
            <div class="section-header flex items-center justify-between" style="background-color: #8B0000;">
                <span>🔎 Result Search Form</span>
                <span class="text-red-200 text-xs font-semibold">Public Page</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-red-50/40">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-800">{{ $formTitle }}</h2>
                </div>

                <form name="frmCheckResults" id="frmCheckResults" action="{{ $formAction }}" method="{{ $formMethod }}" target="{{ $formTarget }}" autocomplete="{{ $formAutocomplete }}" class="bg-gradient-to-b from-white to-red-50/20 p-5 md:p-6 space-y-4">
                    @if(($form['source'] ?? '') === 'live')
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                            This form is loaded live from the official CTEVT page.
                        </div>
                    @else
                        <div class="rounded-2xl border border-yellow-100 bg-yellow-50/80 px-4 py-3 text-sm text-yellow-900">
                            The official CTEVT page could not be loaded just now, so a fallback form is being used.
                        </div>
                    @endif

                    @foreach($hiddenFields as $hiddenField)
                        <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
                    @endforeach

                    @foreach($fields as $field)
                        @php $isSelect = ($field['type'] ?? '') === 'select'; @endphp
                        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-3 md:gap-6 items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                            <label for="{{ $field['id'] ?? $field['name'] }}" class="text-base font-semibold text-gray-700">{{ $field['label'] ?? '' }}</label>
                            <div class="{{ $isSelect ? 'max-w-sm' : 'max-w-md' }}">
                                @if($isSelect)
                                    <x-select name="{{ $field['name'] }}" :required="($field['required'] ?? false)">
                                        @foreach(($field['options'] ?? []) as $option)
                                            <option value="{{ $option['value'] }}" @selected(($option['selected'] ?? false))>{{ $option['label'] }}</option>
                                        @endforeach
                                    </x-select>
                                @else
                                    <x-input type="{{ $field['input_type'] ?? 'text' }}" name="{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" :required="($field['required'] ?? false)" />
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2 flex justify-center md:justify-start">
                        <input type="submit" name="submit" value="{{ $submitLabel }}" class="inline-flex items-center justify-center rounded-lg bg-[#8B0000] px-6 py-2.5 text-white font-semibold shadow-sm hover:bg-[#6f0000] transition-colors cursor-pointer">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
