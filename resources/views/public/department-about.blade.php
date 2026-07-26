@extends('layouts.guest')
@section('title', 'About ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Page Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="h-1.5" style="background-color: #003D82;"></div>
                <div class="p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-3">
                        <h1 class="text-2xl font-black text-gray-900 font-serif">About {{ $department->name }}</h1>
                        <span class="px-2.5 py-1 rounded-md text-xs font-black text-[#003D82] bg-blue-50 border border-blue-200">
                            {{ $department->code }}
                        </span>
                    </div>
                    <p class="text-gray-600 leading-relaxed">
                        {{ $department->description ?? 'The ' . $department->name . ' department is committed to excellence in technical education, producing skilled graduates for industry and community service.' }}
                    </p>
                </div>
            </div>

            {{-- Vision & Mission --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: #003D82;">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <h2 class="font-bold text-gray-900 text-lg">Vision</h2>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        To become a center of excellence in {{ $department->name }} education, producing competent professionals who contribute to national development and are recognized globally for their technical expertise.
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: #003D82;">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h2 class="font-bold text-gray-900 text-lg">Mission</h2>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        To provide quality {{ strtolower($department->name) }} education through innovative teaching methods, hands-on laboratory training, industry collaboration, and ethical professional development.
                    </p>
                </div>
            </div>

            {{-- Objectives --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 border-b border-gray-100" style="background-color: #003D82;">
                    <h2 class="font-bold text-white text-sm">Objectives</h2>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        @foreach([
                            'Equip students with practical technical skills aligned with industry demands.',
                            'Foster critical thinking, problem-solving, and innovation in the field.',
                            'Develop professional ethics and communication skills in graduates.',
                            'Maintain strong industry partnerships for internships and placements.',
                            'Continuously update curriculum to reflect emerging technologies.',
                            'Promote research, community service, and social responsibility.',
                        ] as $obj)
                        <li class="flex items-start gap-3 text-sm text-gray-700">
                            <svg class="w-4 h-4 text-[#003D82] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $obj }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Department Facts --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 border-b border-gray-100" style="background-color: #003D82;">
                    <h2 class="font-bold text-white text-sm">Department at a Glance</h2>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="text-2xl font-black text-[#003D82]">{{ $stats['programs'] }}</div>
                        <div class="text-xs text-gray-600 mt-1">Programs</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="text-2xl font-black text-[#003D82]">{{ $stats['faculty'] }}</div>
                        <div class="text-xs text-gray-600 mt-1">Faculty</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="text-2xl font-black text-[#003D82]">{{ $stats['labs'] }}</div>
                        <div class="text-xs text-gray-600 mt-1">Labs</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="text-2xl font-black text-[#003D82]">{{ $stats['students'] }}</div>
                        <div class="text-xs text-gray-600 mt-1">Students</div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-2.5 border-b border-gray-100" style="background-color: #003D82;">
                    <h2 class="font-bold text-white text-sm">Contact Information</h2>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#003D82] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Office Address</p>
                            <p class="text-sm text-gray-600">Budhiganga-4, Morang, Koshi Province, Nepal</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#003D82] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Email</p>
                            <a href="mailto:info@mmp.edu.np" class="text-sm text-blue-600 hover:underline">info@mmp.edu.np</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#003D82] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Phone</p>
                            <a href="tel:+97721590696" class="text-sm text-blue-600 hover:underline">+977 21 590696</a>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#003D82] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Office Hours</p>
                            <p class="text-sm text-gray-600">Sun–Fri: 9:00 AM – 5:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end main --}}

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'about',
                'downloads'  => $downloads,
                'events'     => $events,
            ])
        </div>
    </div>
</div>
@endsection
