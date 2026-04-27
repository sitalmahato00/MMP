@extends('layouts.guest')
@section('title', ($program->name ?? 'Program') . ' — ' . ($department->name ?? 'Department'))
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Program Hero --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
                <div class="h-2" style="background-color: #003D82;"></div>
                <div class="p-8">
                    <div class="flex items-center gap-2 mb-3">
                        <a href="{{ route('public.department.show', $department->slug) }}" class="text-xs font-bold text-blue-800 hover:underline">{{ $department->name }}</a>
                        <span class="text-gray-300">/</span>
                        <span class="text-xs text-gray-500">{{ $program->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $program->name }}</h1>
                        <span class="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 uppercase">{{ $program->code }}</span>
                        @if($program->is_active)
                            <span class="rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Active</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">{{ $program->duration_years }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Years Duration</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">{{ $program->total_semesters }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Semesters</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">{{ $program->subjects->count() }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Subjects</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">{{ $program->subjects->sum('credit_hours') ?: '—' }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Credit Hours</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Program Details --}}
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
                <div class="section-header" style="background-color: #003D82;">📋 Program Details</div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <span class="text-lg">📝</span>
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase">Program Code</div>
                                <div class="text-sm font-bold text-gray-900">{{ $program->code }}</div>
                            </div>
                        </div>
                        @if($program->ctevt_code)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="text-lg">🏛️</span>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">CTEVT Code</div>
                                    <div class="text-sm font-bold text-gray-900">{{ $program->ctevt_code }}</div>
                                </div>
                            </div>
                        @endif
                        @if($program->affiliation_type)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="text-lg">🔗</span>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">Affiliation</div>
                                    <div class="text-sm font-bold text-gray-900">{{ $program->affiliation_type }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <span class="text-lg">🏢</span>
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase">Department</div>
                                <div class="text-sm font-bold text-gray-900">{{ $department->name }}</div>
                            </div>
                        </div>
                        @if($program->coordinator)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="text-lg">👨‍🏫</span>
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">Coordinator</div>
                                    <div class="text-sm font-bold text-gray-900">{{ $program->coordinator->user->name ?? $program->coordinator->full_name }}</div>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <span class="text-lg">⏱️</span>
                            <div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase">Duration</div>
                                <div class="text-sm font-bold text-gray-900">{{ $program->duration_years }} Years ({{ $program->total_semesters }} Semesters)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Eligibility --}}
            @if($program->eligibility)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
                    <div class="section-header" style="background-color: #003D82;">✅ Eligibility Criteria</div>
                    <div class="p-6">
                        <p class="text-gray-700 leading-relaxed">{{ $program->eligibility }}</p>
                    </div>
                </div>
            @endif

            {{-- Syllabus --}}
            @if($program->syllabus_url)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
                    <div class="section-header" style="background-color: #003D82;">📖 Syllabus</div>
                    <div class="p-6">
                        <a href="{{ $program->syllabus_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 rounded-lg border-2 border-[#003D82] bg-white px-6 py-3 text-sm font-bold text-[#003D82] transition-colors hover:bg-[#003D82] hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            View / Download Syllabus (PDF)
                        </a>
                    </div>
                </div>
            @endif

            {{-- About This Program --}}
            @if($program->description)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-md">
                    <div class="section-header" style="background-color: #003D82;">📝 About This Program</div>
                    <div class="p-6">
                        <div class="text-gray-700 leading-relaxed space-y-4">
                            @foreach(preg_split('/\n\s*\n/', trim($program->description)) as $paragraph)
                                @php
                                    $cleanParagraph = trim($paragraph);
                                    // Skip if paragraph is "Curriculum Structure" or similar headings
                                    if (stripos($cleanParagraph, 'curriculum structure') !== false && strlen($cleanParagraph) < 30) {
                                        continue;
                                    }
                                @endphp
                                @if($cleanParagraph)
                                    <p>{{ $cleanParagraph }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif


        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Department Info --}}
            <div>
                <div class="section-header" style="background-color: #003D82;">🏢 Department</div>
                <div class="bg-white border border-gray-200 border-t-0 p-5 rounded-b-lg shadow-md">
                    <h3 class="font-bold text-gray-900">{{ $department->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Code: {{ $department->code }}</p>
                    <a href="{{ route('public.department.show', $department->slug) }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-bold text-blue-800 hover:underline">
                        ← Back to Department
                    </a>
                </div>
            </div>

            @if($program->coordinator)
                <div>
                    <div class="section-header" style="background-color: #003D82;">👨‍🏫 Program Coordinator</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-5 rounded-b-lg shadow-md">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-blue-50 border-2 flex items-center justify-center text-2xl flex-shrink-0" style="border-color: #003D82;">👨‍💼</div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $program->coordinator->user->name ?? $program->coordinator->full_name }}</div>
                                <div class="text-xs text-blue-700">Program Coordinator</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Program Summary --}}
            <div>
                <div class="section-header" style="background-color: #003D82;">📊 Summary</div>
                <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between">
                        <span class="text-xs text-gray-500">Duration</span>
                        <span class="text-xs font-bold text-gray-900">{{ $program->duration_years }} Years</span>
                    </div>
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between">
                        <span class="text-xs text-gray-500">Semesters</span>
                        <span class="text-xs font-bold text-gray-900">{{ $program->total_semesters }}</span>
                    </div>
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between">
                        <span class="text-xs text-gray-500">Total Subjects</span>
                        <span class="text-xs font-bold text-gray-900">{{ $program->subjects->count() }}</span>
                    </div>
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between">
                        <span class="text-xs text-gray-500">Total Credit Hours</span>
                        <span class="text-xs font-bold text-gray-900">{{ $program->subjects->sum('credit_hours') ?: '—' }}</span>
                    </div>
                    @if($program->affiliation_type)
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between">
                            <span class="text-xs text-gray-500">Affiliation</span>
                            <span class="text-xs font-bold text-gray-900">{{ $program->affiliation_type }}</span>
                        </div>
                    @endif
                    @if($program->ctevt_code)
                        <div class="px-4 py-3 flex justify-between">
                            <span class="text-xs text-gray-500">CTEVT Code</span>
                            <span class="text-xs font-bold text-gray-900">{{ $program->ctevt_code }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Links --}}
            <div>
                <div class="section-header" style="background-color: #003D82;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                    <a href="{{ route('public.department.show', $department->slug) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm font-bold text-blue-800 hover:bg-blue-50 transition-colors"><span class="text-blue-600">›</span> {{ $department->name }}</a>
                    <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> All Departments</a>
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Notices</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Downloads</a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-blue-800 hover:bg-blue-50 transition-colors"><span>🔐</span> Student Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

