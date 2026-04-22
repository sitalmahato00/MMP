@extends('layouts.app')

@section('title', 'My Timetable')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        My Timetable
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Weekly class schedule</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Today's Classes --}}
    @if($todaySlots->isNotEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
                <h2 class="text-sm font-semibold text-slate-900">Today's Classes</h2>
                <p class="text-xs text-slate-500">{{ bsDate(now(), 'l, F d, Y') }}</p>
            </div>
            <div class="p-4 sm:p-6 space-y-3">
                @foreach($todaySlots as $slot)
                    <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
                        <div class="flex flex-col items-center justify-center rounded-lg bg-blue-50 px-3 py-2 min-w-fit">
                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</span>
                            <span class="text-[10px] text-blue-500">to</span>
                            <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900">{{ $slot->subject->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $slot->timetable->program->name }} - {{ $slot->room ?? 'Room TBA' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                Upcoming
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Weekly Timetable Grid --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">Weekly Schedule</h2>
        </div>
        <div class="overflow-x-auto">
            <div class="min-w-full p-4 sm:p-6">
                @php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($days as $day)
                        <div class="rounded-lg border border-slate-200 overflow-hidden">
                            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100">
                                <h3 class="font-semibold text-slate-900 capitalize">{{ $day }}</h3>
                            </div>
                            <div class="divide-y divide-slate-100">
                                @forelse($slotsByDay->get($day, collect()) as $slot)
                                    <div class="p-3 hover:bg-slate-50 transition">
                                        <p class="text-xs font-semibold text-blue-600">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-900">{{ $slot->subject->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $slot->room ?? 'Room TBA' }}</p>
                                    </div>
                                @empty
                                    <div class="p-3 text-center">
                                        <p class="text-xs text-slate-400">No classes</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- All Slots List --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">All Classes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Subject</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Program</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Day</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($slots as $slot)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $slot->subject->name }}</p>
                                <p class="text-xs text-slate-500">{{ $slot->subject->code }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $slot->timetable->program->name }}</td>
                            <td class="px-4 py-3 text-center capitalize">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    {{ $slot->day_of_week }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $slot->room ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <p class="text-sm text-slate-500">No timetable slots scheduled</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
