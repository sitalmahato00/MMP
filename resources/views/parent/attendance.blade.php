@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Attendance</h1>
    <p class="text-sm text-slate-500 mt-1">Track your children's attendance records.</p>
</div>

@forelse($childrenData as $childData)
@php
    $s = $childData['student'];
    $pct = $childData['pct'];
    $attColor = $pct === null ? 'slate' : ($pct >= 75 ? 'emerald' : ($pct >= 50 ? 'amber' : 'red'));
@endphp
<div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between border-b border-slate-100 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-bold text-white">
                {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h3 class="font-bold text-slate-900">{{ $s->user?->name }}</h3>
                <p class="text-xs text-slate-500">{{ $s->department?->name }} · {{ $s->program?->name }}</p>
            </div>
        </div>
        <div class="flex gap-4 text-xs mt-2 sm:mt-0">
            <span class="font-bold text-emerald-600">Present: {{ $childData['present'] }}</span>
            <span class="font-bold text-red-600">Absent: {{ $childData['absent'] }}</span>
            <span class="font-bold text-amber-600">Late: {{ $childData['late'] }}</span>
            <span class="font-bold text-{{ $attColor }}-700">{{ $pct !== null ? $pct.'%' : '—' }}</span>
        </div>
    </div>
    <div class="p-5">
        @if($childData['recentRecords']->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 text-left text-xs font-semibold text-slate-500">Date</th>
                        <th class="py-2 text-left text-xs font-semibold text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($childData['recentRecords'] as $att)
                    <tr>
                        <td class="py-2 text-slate-700">{{ bsDate($att->date ?? $att->created_at, 'M d, Y') }}</td>
                        <td class="py-2">
                            @php
                                $colors = ['present' => 'emerald', 'absent' => 'red', 'late' => 'amber', 'excused' => 'blue'];
                                $c = $colors[$att->status] ?? 'slate';
                            @endphp
                            <span class="rounded-lg bg-{{ $c }}-50 px-2 py-0.5 text-xs font-bold text-{{ $c }}-700">{{ ucfirst($att->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-sm text-slate-500 italic text-center py-4">No attendance records yet.</p>
        @endif
    </div>
</div>
@empty
<div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
    <p class="text-sm text-slate-500">No children linked to your account.</p>
</div>
@endforelse
@endsection
