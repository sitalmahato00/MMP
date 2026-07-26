@extends('layouts.guest')
@section('title', ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@php
    $noticeTypeColors = [
        'exam'       => 'bg-red-50 text-red-700 border-red-200',
        'academic'   => 'bg-blue-50 text-blue-700 border-blue-200',
        'department' => 'bg-purple-50 text-purple-700 border-purple-200',
        'general'    => 'bg-gray-50 text-gray-700 border-gray-200',
        'admission'  => 'bg-green-50 text-green-700 border-green-200',
        'scholarship'=> 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'internship' => 'bg-orange-50 text-orange-700 border-orange-200',
        'workshop'   => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'holiday'    => 'bg-pink-50 text-pink-700 border-pink-200',
    ];
    $noticeTypeLabels = [
        'exam'       => 'Examination',
        'academic'   => 'Academic',
        'department' => 'Department',
        'general'    => 'General',
        'admission'  => 'Admission',
        'scholarship'=> 'Scholarship',
        'internship' => 'Internship',
        'workshop'   => 'Workshop',
        'holiday'    => 'Holiday',
    ];
    $noticeIcons = [
        'exam'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'academic'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        'department' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'general'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
    ];
@endphp

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- ── MAIN CONTENT ─────────────────────────────────── --}}
        <div class="lg:col-span-3 space-y-8">

            {{-- HERO BANNER --}}
            <div class="relative rounded-2xl overflow-hidden shadow-lg">
                @if($department->photo_url)
                    <img src="{{ $department->photo_url }}" alt="{{ $department->name }}"
                         class="w-full h-56 md:h-72 object-cover">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#003D82]/85 via-[#003D82]/50 to-transparent"></div>
                @else
                    <div class="w-full h-56 md:h-64" style="background: linear-gradient(135deg, #003D82 0%, #0052B3 100%);"></div>
                @endif
                <div class="absolute inset-0 flex flex-col justify-end p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl md:text-3xl font-black text-white font-serif leading-tight">{{ $department->name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black text-white border border-white/30 bg-white/20 backdrop-blur-sm">
                            {{ $department->code }}
                        </span>
                    </div>
                    <p class="text-blue-100 text-sm md:text-base leading-relaxed max-w-2xl line-clamp-2">
                        {{ $department->description ?? 'Committed to excellence in teaching, research, and professional development.' }}
                    </p>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <a href="{{ route('public.contact') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white border-2 border-white/50 bg-white/10 backdrop-blur-sm hover:bg-white hover:text-[#003D82] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Contact Department
                        </a>
                        <a href="{{ route('public.department.about', $department->slug) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white border-2 border-white/50 bg-white/10 backdrop-blur-sm hover:bg-white hover:text-[#003D82] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            About Department
                        </a>
                    </div>
                </div>
            </div>

            {{-- STATISTICS CARDS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                @php
                    $statItems = [
                        ['icon' => 'book-open', 'value' => $stats['programs'],    'label' => 'Programs Offered',  'color' => 'blue'],
                        ['icon' => 'users',     'value' => $stats['faculty'],     'label' => 'Faculty Members',   'color' => 'indigo'],
                        ['icon' => 'flask',     'value' => $stats['labs'],        'label' => 'Laboratories',      'color' => 'purple'],
                        ['icon' => 'academic',  'value' => $stats['students'],    'label' => 'Students',          'color' => 'green'],
                        ['icon' => 'calendar',  'value' => $stats['established'], 'label' => 'Established',       'color' => 'orange'],
                        ['icon' => 'badge',     'value' => $stats['affiliation'], 'label' => 'Affiliated',        'color' => 'red'],
                    ];
                    $iconPaths = [
                        'book-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
                        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                        'flask'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
                        'academic'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
                        'calendar'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                        'badge'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
                    ];
                @endphp
                @foreach($statItems as $stat)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-3 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex justify-center mb-1.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50">
                            <svg class="w-4 h-4 text-[#003D82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconPaths[$stat['icon']] !!}
                            </svg>
                        </div>
                    </div>
                    <div class="text-lg font-black text-[#003D82] leading-tight">{{ $stat['value'] }}</div>
                    <div class="text-[10px] text-gray-500 font-medium leading-tight mt-0.5">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>

            {{-- ⭐ LATEST DEPARTMENT NOTICES (HIGHEST PRIORITY) --}}
            <div>
                <div class="flex items-center justify-between rounded-t-lg px-4 py-2.5 mb-0" style="background-color: #003D82;">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-white font-bold text-sm">Latest Department Notices</span>
                    </div>
                    <a href="{{ route('public.department.notices', $department->slug) }}"
                       class="text-xs font-semibold text-blue-200 hover:text-white transition-colors flex items-center gap-1">
                        View All Notices →
                    </a>
                </div>

                <div class="bg-white rounded-b-xl border border-t-0 border-gray-200 shadow-sm overflow-hidden divide-y divide-gray-100">
                    @forelse($notices as $notice)
                    @php
                        $typeKey = $notice->type ?? 'general';
                        $badgeClass = $noticeTypeColors[$typeKey] ?? $noticeTypeColors['general'];
                        $badgeLabel = $noticeTypeLabels[$typeKey] ?? ucfirst($typeKey);
                        $iconPath = $noticeIcons[$typeKey] ?? $noticeIcons['general'];
                        $hasAttachment = $notice->attachment || $notice->attachments?->count();
                    @endphp
                    <div class="flex items-start gap-3 sm:gap-4 px-4 py-3.5 hover:bg-blue-50/40 transition-colors group">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center bg-blue-50 border border-blue-100 mt-0.5">
                            <svg class="w-4 h-4 text-[#003D82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconPath !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                                <span class="text-[11px] text-gray-400">
                                    {{ \Carbon\Carbon::parse($notice->published_at ?? $notice->created_at)->format('d M Y') }}
                                </span>
                            </div>
                            <h4 class="font-semibold text-gray-900 text-sm leading-snug group-hover:text-[#003D82] transition-colors line-clamp-1">
                                {{ $notice->title }}
                            </h4>
                            @if($notice->content)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                {{ \Illuminate\Support\Str::limit(strip_tags($notice->content), 100) }}
                            </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($hasAttachment)
                            <a href="{{ $notice->attachment ? asset('storage/' . $notice->attachment) : '#' }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-[#003D82] hover:bg-blue-50 transition-colors"
                               title="Download attachment" target="_blank" rel="noopener">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                            @endif
                            <a href="{{ route('public.notice.show', $notice->slug) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-[#003D82] hover:bg-blue-50 transition-colors"
                               title="View notice">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-10 text-center text-gray-500">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <p class="font-medium text-sm">No notices available yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- HEAD OF DEPARTMENT + FACULTY PREVIEW (2-col on md+) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Head of Department --}}
                @php
                    $hod = $department->hod;
                    $hodTeacherData = $hodTeacher ?? null;
                @endphp
                @if($hod)
                <div>
                    <div class="rounded-t-lg px-4 py-2.5" style="background-color: #003D82;">
                        <span class="text-white font-bold text-sm">Head of Department</span>
                    </div>
                    <div class="bg-white border border-t-0 border-gray-200 rounded-b-xl shadow-sm p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#003D82] flex-shrink-0 bg-blue-50">
                                <img src="{{ $hod->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($hod->name ?? 'HOD') . '&background=003D82&color=fff' }}"
                                     alt="{{ $hod->name }}" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm leading-tight">{{ $hod->name }}</h3>
                                <p class="text-xs text-[#003D82] font-semibold mt-0.5">Head of Department</p>
                                @if($hod->designation)
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $hod->designation }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-0.5">{{ $department->name }}</p>
                                @if($hodTeacherData?->qualification)
                                <p class="text-xs text-gray-600 mt-1">{{ $hodTeacherData->qualification }}</p>
                                @endif
                                @if($hod->email)
                                <a href="mailto:{{ $hod->email }}" class="text-xs text-blue-600 hover:underline block mt-1 truncate">
                                    {{ $hod->email }}
                                </a>
                                @endif
                            </div>
                        </div>
                        @if($hodTeacherData?->specialization)
                        <blockquote class="mt-3 pl-3 border-l-2 border-[#003D82] text-xs text-gray-600 italic">
                            {{ $hodTeacherData->specialization }}
                        </blockquote>
                        @endif
                        <div class="mt-4">
                            <a href="{{ route('public.people.profile', ['type' => 'hod', 'id' => $department->id]) }}"
                               class="inline-flex items-center gap-2 text-xs font-bold text-[#003D82] border-2 border-[#003D82] px-3 py-1.5 rounded-lg hover:bg-[#003D82] hover:text-white transition-all duration-200">
                                View Full Profile →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Faculty Preview --}}
                <div>
                    <div class="flex items-center justify-between rounded-t-lg px-4 py-2.5" style="background-color: #003D82;">
                        <span class="text-white font-bold text-sm">Faculty Members</span>
                        <a href="{{ route('public.department.people', $department->slug) }}"
                           class="text-xs font-semibold text-blue-200 hover:text-white transition-colors">
                            View All →
                        </a>
                    </div>
                    <div class="bg-white border border-t-0 border-gray-200 rounded-b-xl shadow-sm p-4">
                        @php $facultyPreview = $teachers->filter(fn($t) => (int)$t->user_id !== (int)$department->hod_id)->take(4); @endphp
                        @if($facultyPreview->count())
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($facultyPreview as $teacher)
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-50 border border-gray-200 flex-shrink-0">
                                    <img src="{{ $teacher->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacher->user?->name ?? 'T') . '&background=003D82&color=fff&size=80' }}"
                                         alt="{{ $teacher->user?->name }}" class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $teacher->user?->name }}</p>
                                    <p class="text-[10px] text-gray-500 truncate">{{ $teacher->designation }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 text-center py-4">Faculty details coming soon.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PROGRAMS OFFERED --}}
            <div>
                <div class="flex items-center justify-between rounded-t-lg px-4 py-2.5" style="background-color: #003D82;">
                    <span class="text-white font-bold text-sm">Programs Offered</span>
                    <a href="{{ route('public.department.programs', $department->slug) }}"
                       class="text-xs font-semibold text-blue-200 hover:text-white transition-colors flex items-center gap-1">
                        View All Programs →
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    @forelse($department->programs->take(4) as $program)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 p-5">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[#003D82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm leading-tight">{{ $program->name }}</h3>
                                <p class="text-[10px] text-gray-500 mt-0.5">{{ $program->code }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 text-[10px] text-gray-600 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $program->duration_years }} Years
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] text-gray-600 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-full">
                                {{ $program->total_semesters }} Semesters
                            </span>
                            @if($program->affiliation_type)
                            <span class="inline-flex items-center gap-1 text-[10px] text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full font-semibold">
                                {{ $program->affiliation_type }}
                            </span>
                            @endif
                        </div>
                        @if($program->description)
                        <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ $program->description }}</p>
                        @endif
                        <a href="{{ route('public.program.show', [$department->slug, $program->slug ?: \Illuminate\Support\Str::slug($program->name)]) }}"
                           class="inline-flex items-center gap-1 text-xs font-bold text-[#003D82] hover:underline">
                            Learn More →
                        </a>
                    </div>
                    @empty
                    <div class="sm:col-span-2 bg-white rounded-xl border border-gray-200 p-8 text-center">
                        <p class="text-gray-500 text-sm">No programs listed yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- DEPARTMENT ACTIVITIES / GALLERY --}}
            @if($gallery->count())
            <div>
                <div class="flex items-center justify-between rounded-t-lg px-4 py-2.5 mb-3" style="background-color: #003D82;">
                    <span class="text-white font-bold text-sm">Department Activities</span>
                    <a href="{{ route('public.department.gallery', $department->slug) }}"
                       class="text-xs font-semibold text-blue-200 hover:text-white transition-colors">View Gallery →</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach($gallery->take(8) as $media)
                    <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 group cursor-pointer relative shadow-sm hover:shadow-md transition-shadow">
                        <img src="{{ $media->url }}" alt="{{ $media->title ?? 'Gallery' }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if($media->title)
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2">
                            <p class="text-white text-[10px] font-medium line-clamp-2">{{ $media->title }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 flex items-center justify-center gap-6 bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                    <span class="text-sm font-semibold text-gray-700">📸 {{ $gallery->count() }}+ Photos</span>
                    <a href="{{ route('public.department.gallery', $department->slug) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white transition-colors"
                       style="background-color: #003D82;">
                        View Full Gallery →
                    </a>
                </div>
            </div>
            @endif

        </div>{{-- end main content --}}

        {{-- ── RIGHT SIDEBAR ──────────────────────────────────── --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'overview',
                'downloads'  => $downloads,
                'events'     => $events,
            ])
        </div>

    </div>
</div>

{{-- QUICK LINKS BAR (mobile) --}}
<div class="lg:hidden border-t border-gray-200 bg-white py-3 px-4 mt-4">
    <div class="flex items-center justify-around gap-2 overflow-x-auto">
        @foreach([
            ['label' => 'Notices',  'icon' => 'bell',   'route' => route('public.department.notices', $department->slug)],
            ['label' => 'People',   'icon' => 'users',  'route' => route('public.department.people', $department->slug)],
            ['label' => 'Programs', 'icon' => 'book',   'route' => route('public.department.programs', $department->slug)],
            ['label' => 'Gallery',  'icon' => 'photo',  'route' => route('public.department.gallery', $department->slug)],
            ['label' => 'Contact',  'icon' => 'mail',   'route' => route('public.contact')],
        ] as $ql)
        <a href="{{ $ql['route'] }}" class="flex flex-col items-center gap-1 text-[10px] font-semibold text-gray-600 hover:text-[#003D82] transition-colors px-2 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-[#003D82]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($ql['icon'] === 'bell')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    @elseif($ql['icon'] === 'users')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    @elseif($ql['icon'] === 'book')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    @elseif($ql['icon'] === 'photo')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    @endif
                </svg>
            </div>
            {{ $ql['label'] }}
        </a>
        @endforeach
    </div>
</div>
@endsection
