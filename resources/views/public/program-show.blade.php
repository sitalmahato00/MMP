@extends('layouts.guest')
@section('title', ($program->name ?? 'Program') . ' — ' . ($department->name ?? 'Department'))
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Program Hero --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="h-2" style="background-color: #8B0000;"></div>
                <div class="p-8">
                    <div class="flex items-center gap-2 mb-3">
                        <a href="{{ route('public.department.show', $department->slug) }}" class="text-xs font-bold text-red-800 hover:underline">{{ $department->name }}</a>
                        <span class="text-gray-300">/</span>
                        <span class="text-xs text-gray-500">{{ $program->name }}</span>
                    </div>
                    <div class="flex items-center gap-3 mb-4">
                        <h1 class="text-2xl font-black font-serif text-gray-900">{{ $program->name }}</h1>
                        <span class="rounded-md bg-red-50 border border-red-100 px-2 py-0.5 text-xs font-bold text-red-800 uppercase">{{ $program->code }}</span>
                        @if($program->is_active)
                            <span class="rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Active</span>
                        @endif
                    </div>

                    @if($program->description)
                        <p class="text-gray-600 leading-relaxed mb-6">{{ $program->description }}</p>
                    @endif

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">{{ $program->duration_years }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Years Duration</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">{{ $program->total_semesters }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Semesters</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">{{ $program->subjects->count() }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Subjects</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-black text-red-800">{{ $program->subjects->sum('credit_hours') ?: '—' }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Credit Hours</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Program Details --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="section-header" style="background-color: #8B0000;">📋 Program Details</div>
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
                                    <div class="text-sm font-bold text-gray-900">{{ $program->coordinator->name }}</div>
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
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="section-header" style="background-color: #8B0000;">✅ Eligibility Criteria</div>
                    <div class="p-6">
                        <p class="text-gray-700 leading-relaxed">{{ $program->eligibility }}</p>
                    </div>
                </div>
            @endif

            {{-- Syllabus --}}
            @if($program->syllabus_url)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="section-header" style="background-color: #8B0000;">📖 Syllabus</div>
                    <div class="p-6">
                        <a href="{{ $program->syllabus_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 rounded-lg border-2 border-[#8B0000] bg-white px-6 py-3 text-sm font-bold text-[#8B0000] transition-colors hover:bg-[#8B0000] hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            View / Download Syllabus (PDF)
                        </a>
                    </div>
                </div>
            @endif

            {{-- Semester-wise Subjects --}}
            @if($program->subjects->count())
                <div class="section-header" style="background-color: #8B0000;">📚 Semester-wise Subjects</div>

                @for($sem = 1; $sem <= $program->total_semesters; $sem++)
                    @php $semSubjects = $program->subjects->where('semester', $sem); @endphp
                    @if($semSubjects->count())
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                            <div class="px-6 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold text-white" style="background-color: #8B0000;">{{ $sem }}</span>
                                    <span class="text-sm font-bold text-gray-800">Semester {{ $sem }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $semSubjects->count() }} subjects · {{ $semSubjects->sum('credit_hours') }} credit hrs
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50/50 text-gray-500 text-xs">
                                            <th class="px-4 py-2.5 text-left font-semibold">#</th>
                                            <th class="px-4 py-2.5 text-left font-semibold">Subject</th>
                                            <th class="px-4 py-2.5 text-left font-semibold">Code</th>
                                            <th class="px-4 py-2.5 text-center font-semibold">Type</th>
                                            <th class="px-4 py-2.5 text-center font-semibold">Full Marks</th>
                                            <th class="px-4 py-2.5 text-center font-semibold">Pass Marks</th>
                                            <th class="px-4 py-2.5 text-center font-semibold">Credit Hrs</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach($semSubjects as $subject)
                                            <tr class="hover:bg-red-50/30">
                                                <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-2.5 font-medium text-gray-900">{{ $subject->name }}</td>
                                                <td class="px-4 py-2.5 text-gray-500 font-mono text-xs">{{ $subject->code }}</td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold {{ $subject->type === 'practical' ? 'bg-blue-50 text-blue-700' : ($subject->type === 'theory' ? 'bg-amber-50 text-amber-700' : 'bg-purple-50 text-purple-700') }}">
                                                        {{ ucfirst($subject->type ?? 'Theory') }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2.5 text-center text-gray-600">
                                                    {{ ($subject->full_marks_theory ?? 0) + ($subject->full_marks_practical ?? 0) ?: '—' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-center text-gray-600">
                                                    {{ ($subject->pass_marks_theory ?? 0) + ($subject->pass_marks_practical ?? 0) ?: '—' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-center text-gray-600">{{ $subject->credit_hours ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gray-50 font-bold text-xs text-gray-700">
                                            <td class="px-4 py-2" colspan="4">Total</td>
                                            <td class="px-4 py-2 text-center">{{ $semSubjects->sum(fn($s) => ($s->full_marks_theory ?? 0) + ($s->full_marks_practical ?? 0)) ?: '—' }}</td>
                                            <td class="px-4 py-2 text-center">{{ $semSubjects->sum(fn($s) => ($s->pass_marks_theory ?? 0) + ($s->pass_marks_practical ?? 0)) ?: '—' }}</td>
                                            <td class="px-4 py-2 text-center">{{ $semSubjects->sum('credit_hours') ?: '—' }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                @endfor
            @else
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center shadow-sm">
                    <div class="text-4xl mb-3">📚</div>
                    <p class="font-semibold text-gray-700">Subject details not yet available.</p>
                    <p class="text-sm text-gray-500 mt-1">Subject information will appear here once published.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Department Info --}}
            <div>
                <div class="section-header" style="background-color: #8B0000;">🏢 Department</div>
                <div class="bg-white border border-gray-200 border-t-0 p-5">
                    <h3 class="font-bold text-gray-900">{{ $department->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Code: {{ $department->code }}</p>
                    <a href="{{ route('public.department.show', $department->slug) }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-bold text-red-800 hover:underline">
                        ← Back to Department
                    </a>
                </div>
            </div>

            @if($program->coordinator)
                <div>
                    <div class="section-header" style="background-color: #8B0000;">👨‍🏫 Program Coordinator</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-5">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-red-50 border-2 flex items-center justify-center text-2xl flex-shrink-0" style="border-color: #8B0000;">👨‍💼</div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $program->coordinator->name }}</div>
                                <div class="text-xs text-red-700">Program Coordinator</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Program Summary --}}
            <div>
                <div class="section-header" style="background-color: #8B0000;">📊 Summary</div>
                <div class="bg-white border border-gray-200 border-t-0">
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
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    <a href="{{ route('public.department.show', $department->slug) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm font-bold text-red-800 hover:bg-red-50 transition-colors"><span class="text-red-600">›</span> {{ $department->name }}</a>
                    <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> All Departments</a>
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Notices</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Downloads</a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-800 hover:bg-red-50 transition-colors"><span>🔐</span> Student Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
