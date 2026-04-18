@extends('layouts.app')
@section('title', 'Parent Dashboard')

@section('content')
@php
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
@endphp

{{-- Welcome Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">
        {{ ucfirst($parent?->relation_to_student ?? 'Parent') }} · {{ $children->count() }} {{ Str::plural('child', $children->count()) }} linked
        @if($session)
            · {{ $session->name }}
        @endif
    </p>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $children->count() }}</p>
                <p class="text-xs text-slate-500">Children</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            @php $avgAtt = $childrenSummaries->avg('attendancePct'); @endphp
            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $avgAtt !== null && $avgAtt >= 75 ? 'bg-emerald-50' : ($avgAtt !== null && $avgAtt >= 50 ? 'bg-amber-50' : 'bg-red-50') }}">
                <svg class="w-5 h-5 {{ $avgAtt !== null && $avgAtt >= 75 ? 'text-emerald-600' : ($avgAtt !== null && $avgAtt >= 50 ? 'text-amber-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $avgAtt !== null ? round($avgAtt).'%' : '—' }}</p>
                <p class="text-xs text-slate-500">Avg Attendance</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            @php $avgMarks = $childrenSummaries->avg('avgMarks'); @endphp
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $avgMarks !== null ? round($avgMarks, 1) : '—' }}</p>
                <p class="text-xs text-slate-500">Avg Marks</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            @php $totalPending = $childrenSummaries->sum('pendingAssignments'); @endphp
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $totalPending }}</p>
                <p class="text-xs text-slate-500">Pending Work</p>
            </div>
        </div>
    </div>
</div>

{{-- Children Cards --}}
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Your Children</h2>
    @if($childrenSummaries->count())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($childrenSummaries as $i => $childData)
        @php
            $s = $childData['student'];
            $attPct = $childData['attendancePct'];
            $attColor = $attPct === null ? 'slate' : ($attPct >= 75 ? 'emerald' : ($attPct >= 50 ? 'amber' : 'red'));
            $grad = $gradients[$i % 6];
        @endphp
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="p-5">
                <div class="flex items-start gap-4">
                    @if($s->user?->avatar)
                        <img src="{{ asset('storage/'.$s->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-lg font-bold text-white">
                            {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-slate-900">{{ $s->user?->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $s->department?->name }} · {{ $s->program?->name }} · Sem {{ $s->current_semester }}</p>
                    </div>
                    <a href="{{ route('parent.child.show', $s) }}"
                       class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                        View
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-2">
                    <div class="rounded-xl bg-{{ $attColor }}-50 p-2.5 text-center">
                        <p class="text-lg font-black text-{{ $attColor }}-700">{{ $attPct !== null ? $attPct.'%' : '—' }}</p>
                        <p class="text-[10px] text-slate-500">Attendance</p>
                    </div>
                    <div class="rounded-xl bg-blue-50 p-2.5 text-center">
                        <p class="text-lg font-black text-blue-700">{{ $childData['avgMarks'] ?? '—' }}</p>
                        <p class="text-[10px] text-slate-500">Avg Marks</p>
                    </div>
                    <div class="rounded-xl bg-violet-50 p-2.5 text-center">
                        <p class="text-lg font-black text-violet-700">{{ $childData['totalExams'] }}</p>
                        <p class="text-[10px] text-slate-500">Exams</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-2.5 text-center">
                        <p class="text-lg font-black text-amber-700">{{ $childData['pendingAssignments'] }}</p>
                        <p class="text-[10px] text-slate-500">Pending</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <h3 class="mt-3 font-bold text-slate-900">No children linked</h3>
        <p class="mt-1 text-sm text-slate-500">Contact the administration to link your children to this account.</p>
    </div>
    @endif
</div>

{{-- Recent Notices --}}
<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-900">Recent Notices</h2>
        <a href="{{ route('parent.notices.index') }}" class="text-xs font-semibold text-[#8B0000] hover:underline">View All →</a>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        @forelse($recentNotices as $notice)
        <div class="flex items-start gap-3 px-5 py-4 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
            <div class="mt-1.5 h-2 w-2 rounded-full bg-blue-500 flex-shrink-0"></div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900">{{ $notice->title }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ bsDate($notice->created_at, 'Y, F d') }}</p>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <p class="text-sm text-slate-500">No notices yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
