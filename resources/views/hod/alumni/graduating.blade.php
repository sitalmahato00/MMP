@extends('layouts.app')

@section('title', 'Graduating Students')

@section('content')

<div class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hod.alumni.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Graduating Students</h1>
        </div>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $department->name }} — Prepare final semester students for alumni status
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('hod.alumni.records') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
            Alumni Records
        </a>
    </div>
</div>

{{-- ── FILTERS ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('hod.alumni.graduating') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or email…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->name }}</option>
            @endforeach
        </select>
        {{-- Status + Apply --}}
        <div class="flex gap-2">
            <select name="status" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All Status</option>
                <option value="ready" @selected(request('status') == 'ready')>Ready for Preparation</option>
                <option value="prepared" @selected(request('status') == 'prepared')>Already Prepared</option>
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search','program_id','status']))
            <a href="{{ route('hod.alumni.graduating') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

    {{-- Students Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Student</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Program</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Semester</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->user->avatar_url }}" alt="{{ $student->user->name }}" 
                                         class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $student->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $student->program->name }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-slate-900">Semester {{ $student->current_semester }}</div>
                                @if($student->is_graduating)
                                    <div class="text-xs text-emerald-600 font-medium">Final semester</div>
                                @else
                                    <div class="text-xs text-slate-500">of {{ $student->program->total_semesters }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($student->has_alumni_record)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Alumni Prepared
                                    </span>
                                @elseif($student->is_graduating)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Ready
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                        Not Graduating
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if(!$student->has_alumni_record && $student->is_graduating)
                                    <form method="POST" action="{{ route('hod.alumni.prepare', $student) }}" 
                                          onsubmit="return confirm('Are you sure you want to prepare this student for alumni status?')">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-sm font-bold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Prepare Alumni
                                        </button>
                                    </form>
                                @elseif($student->has_alumni_record)
                                    <span class="text-sm text-slate-400 font-medium">Already prepared</span>
                                @else
                                    <span class="text-sm text-slate-400 font-medium">Not eligible yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <p class="mt-3 text-sm font-medium text-slate-500">No students found</p>
                                <p class="mt-1 text-xs text-slate-400">Try adjusting your filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 bg-slate-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
