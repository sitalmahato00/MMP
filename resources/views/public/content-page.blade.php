@extends('layouts.guest')
@section('title', $page->title ?? 'Page')
@section('meta_description', $page->meta_description ?? '')
@section('breadcrumb', true)

@section('content')
@php
    $siteSettings = $siteSettings ?? collect();
    $isContactPage = ($page->slug ?? null) === 'contact-us';
    $hasMainContent = trim(strip_tags($page->content ?? '')) !== '';
    $contactEmail = optional($siteSettings->get('contact_email'))->value;
    $contactPhone = optional($siteSettings->get('contact_phone'))->value;
    $contactAddress = optional($siteSettings->get('contact_address'))->value;
    $googleMapsIframe = optional($siteSettings->get('google_maps_iframe'))->value;
    $rawContent = (string) ($page->content ?? '');
    $isHtmlContent = preg_match('/<\s*[a-z][^>]*>/i', $rawContent) === 1;
    $formattedContent = $isHtmlContent
        ? $rawContent
        : nl2br(e(str_replace("\t", '    ', $rawContent)));
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
            @if($hasMainContent)
                <div>
                    <div class="section-header">{{ $page->title }}</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-8 prose prose-sm max-w-none prose-headings:text-red-900 prose-a:text-red-700">
                        {!! $formattedContent !!}
                    </div>
                </div>
            @endif

            @if($isContactPage)
                <div>
                    <div class="section-header">Contact Details</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-red-100 bg-red-50/60 p-5">
                                <div class="text-xs font-bold uppercase tracking-wider text-[#8B0000] mb-2">Email</div>
                                <div class="text-sm text-gray-700 break-words">{{ $contactEmail ?: 'Not available yet.' }}</div>
                            </div>
                            <div class="rounded-xl border border-red-100 bg-red-50/60 p-5">
                                <div class="text-xs font-bold uppercase tracking-wider text-[#8B0000] mb-2">Phone</div>
                                <div class="text-sm text-gray-700">{{ $contactPhone ?: 'Not available yet.' }}</div>
                            </div>
                            <div class="rounded-xl border border-red-100 bg-red-50/60 p-5">
                                <div class="text-xs font-bold uppercase tracking-wider text-[#8B0000] mb-2">Address</div>
                                <div class="text-sm text-gray-700">{{ $contactAddress ?: 'Not available yet.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($googleMapsIframe)
                    <div>
                        <div class="section-header">Campus Location</div>
                        <div class="bg-white border border-gray-200 border-t-0 p-3 [&_iframe]:w-full [&_iframe]:min-h-[340px] [&_iframe]:border-0">
                            {!! $googleMapsIframe !!}
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="space-y-8">
            <div>
                <div class="section-header">Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">&rsaquo;</span> Notices</a>
                    <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">&rsaquo;</span> Our Programs</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">&rsaquo;</span> Downloads</a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-800"><span>&rsaquo;</span> Student Portal</a>
                </div>
            </div>

            @if($isContactPage)
                <div>
                    <div class="section-header">Office Summary</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-5 space-y-3 text-sm text-gray-700">
                        <p><span class="font-semibold text-red-900">Email:</span> {{ $contactEmail ?: 'Not available yet.' }}</p>
                        <p><span class="font-semibold text-red-900">Phone:</span> {{ $contactPhone ?: 'Not available yet.' }}</p>
                        <p><span class="font-semibold text-red-900">Address:</span> {{ $contactAddress ?: 'Not available yet.' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
