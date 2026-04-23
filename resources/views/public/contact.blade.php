@extends('layouts.guest')
@section('title', 'Contact Us')
@section('meta_description', 'Get in touch with Manmohan Memorial Polytechnic. Address, phone, email, and location map for MMP, Budhiganga-4, Morang, Koshi Province, Nepal.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Main Contact Content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Contact Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Address --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center group hover:border-[#003D82]/30 hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FEF2F2;">
                        <svg class="w-6 h-6" style="color: #003D82;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Address</h3>
                    <p class="text-sm text-gray-600">{{ optional($siteSettings->get('contact_address'))->value ?: 'Budhiganga-4, Morang, Koshi Province, Nepal' }}</p>
                </div>

                {{-- Phone --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center group hover:border-[#003D82]/30 hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FEF2F2;">
                        <svg class="w-6 h-6" style="color: #003D82;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Phone</h3>
                    <p class="text-sm text-gray-600">{{ optional($siteSettings->get('contact_phone'))->value ?: '+977 21 590696, +977 21 590697' }}</p>
                </div>

                {{-- Email --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center group hover:border-[#003D82]/30 hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FEF2F2;">
                        <svg class="w-6 h-6" style="color: #003D82;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                    <a href="mailto:{{ optional($siteSettings->get('contact_email'))->value ?: 'info@mmp.edu.np' }}" class="text-sm text-[#003D82] hover:underline">
                        {{ optional($siteSettings->get('contact_email'))->value ?: 'info@mmp.edu.np' }}
                    </a>
                </div>
            </div>

            {{-- About Content --}}
            @php $contactContent = optional($siteSettings->get('contact_us_content'))->value; @endphp
            @if($contactContent)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="section-header rounded-t-xl">About Finding Us</div>
                <div class="p-6 prose prose-sm max-w-none text-gray-700 leading-relaxed">
                    {!! $contactContent !!}
                </div>
            </div>
            @endif

            {{-- Google Map --}}
            @php $mapEmbed = optional($siteSettings->get('google_maps_iframe'))->value; @endphp
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="section-header">Location Map</div>
                @if($mapEmbed && str_contains($mapEmbed, 'iframe'))
                    <div class="w-full h-80 [&>iframe]:w-full [&>iframe]:h-full">
                        {!! $mapEmbed !!}
                    </div>
                @else
                    {{-- Default embed for MMP location --}}
                    <div class="w-full h-80">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3572.8!2d87.2833!3d26.5333!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zManmohan+Memorial+Polytechnic!5e0!3m2!1sen!2snp!4v1234567890"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Manmohan Memorial Polytechnic Location">
                        </iframe>
                    </div>
                    <p class="text-xs text-gray-400 px-4 py-2 text-center">Set your exact map embed in Admin → Web Control → Contact & Info</p>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Office Hours --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="section-header rounded-t-xl">Office Hours</div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Sunday – Friday</span>
                        <span class="text-gray-900 font-semibold">9:00 AM – 5:00 PM</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Saturday</span>
                        <span class="text-gray-500">Closed</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 font-medium">Public Holidays</span>
                        <span class="text-gray-500">Closed</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500">For urgent matters outside office hours, please email us.</p>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="section-header rounded-t-xl">Quick Links</div>
                <div class="p-4">
                    <ul class="space-y-2.5 text-sm">
                        @foreach([
                            ['href' => route('public.page', 'what-is-mmp'), 'label' => 'About MMP'],
                            ['href' => route('public.departments'), 'label' => 'Our Programs'],
                            ['href' => route('public.page', 'scholarship-schemes'), 'label' => 'Scholarship Schemes'],
                            ['href' => route('public.notices'), 'label' => 'Notice Board'],
                            ['href' => route('public.downloads'), 'label' => 'Downloads & Forms'],
                        ] as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="flex items-center gap-2 text-gray-700 hover:text-[#003D82] transition-colors">
                                <span class="text-[#003D82] font-bold">›</span> {{ $link['label'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Admission Inquiry --}}
            <div class="rounded-xl p-5 text-white text-center" style="background: linear-gradient(135deg, #003D82, #001F4D);">
                <svg class="w-10 h-10 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <h3 class="font-bold font-serif text-lg mb-2">Admissions Open</h3>
                <p class="text-blue-200 text-sm mb-4">Diploma programs in Engineering & Technology. Apply today!</p>
                <a href="{{ route('public.departments') }}" class="inline-block bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold px-5 py-2 rounded text-sm transition-colors">
                    View Programs
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

