@extends('layouts.app')
@section('title', 'Results')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Results</h1>
    <p class="text-sm text-slate-500 mt-1">View your children's exam results and marks.</p>
</div>

@forelse($childrenResults as $childData)
@php $s = $childData['student']; @endphp
<div class="mb-6">
    <div class="flex items-center gap-3 mb-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 text-sm font-bold text-white">
            {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
        </div>
        <div>
            <h3 class="font-bold text-slate-900">{{ $s->user?->name }}</h3>
            <p class="text-xs text-slate-500">{{ $s->department?->name }} · Avg: {{ $childData['avgMarks'] ?? '—' }} · {{ $childData['totalRecords'] }} records</p>
        </div>
    </div>

    @forelse($childData['marksByExam'] as $examName => $marks)
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-3">
            <h4 class="text-sm font-bold text-slate-700">{{ $examName }}</h4>
        </div>
        <div class="p-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 text-left text-xs font-semibold text-slate-500">Subject</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Theory</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Practical</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($marks as $mark)
                    <tr>
                        <td class="py-2 text-slate-700">{{ $mark->subject?->name ?? '—' }}</td>
                        <td class="py-2 text-center text-slate-700">{{ $mark->theory ?? '—' }}</td>
                        <td class="py-2 text-center text-slate-700">{{ $mark->practical ?? '—' }}</td>
                        <td class="py-2 text-center font-bold text-slate-900">{{ ($mark->theory ?? 0) + ($mark->practical ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-6 py-8 text-center shadow-sm">
        <p class="text-sm text-slate-500">No published results yet.</p>
    </div>
    @endforelse
</div>
@empty
<div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
    <p class="text-sm text-slate-500">No children linked to your account.</p>
</div>
@endforelse
@endsection
