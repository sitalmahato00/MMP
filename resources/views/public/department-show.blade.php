@extends('layouts.guest')
@section('title', $department->name ?? 'Department')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="h-2" style="background-color: #8B0000;"></div>
                @if($department->photo_url)
                    <img src="{{ $department->photo_url }}" alt="{{ $department->name }}" class="w-full h-72 object-cover">
                @endif
                <div class="p-8">
                    <h2 class="text-2xl font-black font-serif text-gray-900 mb-4">{{ $department->name }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">{{ $department->description ?? 'This department offers a comprehensive CTEVT-approved 3-year diploma program designed to equip students with hands-on technical skills required in today\'s industry.' }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">3</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Years Duration</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">6</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Semesters</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">CTEVT</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Affiliated</div>
                        </div>
                    </div>

                    @if($department->syllabus_url)
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $department->syllabus_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white border border-[#8B0000] text-[#8B0000] font-bold px-5 py-2.5 rounded-lg transition-colors text-sm hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View Syllabus
                            </a>
                            <a href="{{ $department->syllabus_url }}" download class="inline-flex items-center gap-2 bg-[#8B0000] hover:bg-[#5c0000] text-white font-bold px-5 py-2.5 rounded-lg transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Download Syllabus PDF
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if($department->programs && $department->programs->count() > 0)
                <div>
                    <div class="section-header" style="background-color: #8B0000;">📚 Semester Subjects</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-6">
                        @foreach($department->programs as $program)
                            <div class="rounded-lg border border-gray-100 p-4 mb-3">
                                <h4 class="font-bold text-gray-800 mb-1">{{ $program->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $program->duration_years }} years · {{ $program->total_semesters }} semesters</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @if($department->hod)
                <div>
                    <div class="section-header" style="background-color: #8B0000;">👨‍🏫 Head of Department</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-5">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-red-50 border-2 flex items-center justify-center text-3xl flex-shrink-0" style="border-color: #8B0000;">👨‍💼</div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $department->hod->name }}</div>
                                <div class="text-sm text-red-700">Head of Department</div>
                                <div class="text-xs text-gray-500">{{ $department->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Notices</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Downloads</a>
                    <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> All Programs</a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-800 hover:bg-red-50 transition-colors"><span>🔐</span> Student Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
