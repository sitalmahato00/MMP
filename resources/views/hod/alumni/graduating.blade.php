@extends('layouts.app')
@section('title', 'Graduating Students')

@section('content')
<x-page-header title="Graduating Students" subtitle="Prepare final semester students for alumni status."
               back="{{ route('hod.alumni.index') }}"/>

{{-- Filters --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="relative flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search students..." 
                   class="w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <div class="min-w-[200px]">
            <select name="program_id" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <select name="status" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="ready" @selected(request('status') === 'ready')>Ready for Alumni</option>
                <option value="prepared" @selected(request('status') === 'prepared')>Already Prepared</option>
            </select>
        </div>
        <div>
            <x-btn type="submit">Filter</x-btn>
        </div>
    </form>
</div>

{{-- Students List --}}
@if($students->count() > 0)
    <div class="space-y-3">
        @foreach($students as $student)
            @if($student->is_graduating)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            @if($student->user?->avatar)
                                <img src="{{ asset('storage/'.$student->user->avatar) }}" alt="" 
                                     class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-200">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-lg font-bold text-white">
                                    {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
                                </div>
                            @endif

                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800">{{ $student->user?->name }}</h3>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                                    <span>{{ $student->program?->name }}</span>
                                    <span>•</span>
                                    <span>Semester {{ $student->semester }}</span>
                                    <span>•</span>
                                    <span>{{ $student->user?->email }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    @if($student->has_alumni_record)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            Alumni Prepared
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                            Ready for Alumni
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            @if(!$student->has_alumni_record)
                                <form method="POST" action="{{ route('hod.alumni.prepare', $student) }}" 
                                      onsubmit="return confirm('Prepare {{ $student->user?->name }} for alumni status?')">
                                    @csrf
                                    <x-btn type="submit" size="sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Prepare Alumni
                                    </x-btn>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Prepared
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $students->links() }}
    </div>
@else
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-800">No graduating students found</h3>
        <p class="mt-1 text-sm text-slate-500">Students in their final semester will appear here.</p>
    </div>
@endif
@endsection