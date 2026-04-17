@extends('layouts.app')
@section('title', 'Exams')

@section('content')
<x-page-header title="Examinations" subtitle="Create and manage examination schedules.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.exams.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Exam
        </x-btn>
    </x-slot>
</x-page-header>

<x-data-table :paginator="$exams">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Exam</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Session</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Start Date</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($exams as $exam)
    @php
        $status = $exam->is_published ? 'published' : ($exam->marks_open ? 'open' : 'draft');
        $statusColors = ['published'=>'green','open'=>'yellow','draft'=>'gray'];
    @endphp
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $exam->name }}</td>
        <td class="px-5 py-3.5">
            <x-badge color="blue">{{ $exam->academicSession?->name ?? '—' }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">
            {{ $exam->start_date ? bsDate($exam->start_date, 'd F Y') : '—' }}
        </td>
        <td class="px-5 py-3.5">
            <x-badge :color="$statusColors[$status]" :dot="true">{{ ucfirst($status) }}</x-badge>
        </td>
        <td class="px-5 py-3.5">
            <div class="flex items-center justify-end gap-2">
                @if(!$exam->is_published)
                <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                    @csrf @method('PATCH')
                    <x-btn type="submit" variant="secondary" size="sm">Publish</x-btn>
                </form>
                @endif
                <x-table-actions
                    :edit="route('admin.exams.edit', $exam)"
                    :destroy="route('admin.exams.destroy', $exam)"
                    name="{{ $exam->name }}"
                />
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="5">
        <x-empty-state title="No exams created"
                       message="Create an examination to begin the evaluation cycle."
                       action="{{ route('admin.exams.create') }}"
                       actionLabel="Create Exam"/>
    </td></tr>
    @endforelse
</x-data-table>
@endsection
