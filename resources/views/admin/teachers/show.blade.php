@extends('layouts.app')
@section('title', $teacher->user?->name . ' — Teacher Profile')

@section('content')
@php
    $grad = collect(['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'])[$teacher->id % 6];
@endphp

<div x-data="{ tab: '{{ request('tab', 'overview') }}' }" class="space-y-6">

    {{-- ── HERO ── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $grad }} shadow-lg">
        <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        <div class="relative px-8 py-10">
            <div class="flex flex-wrap items-end gap-6">
                @if($teacher->user?->avatar)
                    <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt=""
                         class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white/60 shadow-xl"/>
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white/25 text-4xl font-black text-white ring-4 ring-white/60 shadow-xl">
                        {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-black text-white leading-tight">{{ $teacher->user?->name }}</h1>
                    <p class="mt-1 text-white/75 text-sm">{{ $teacher->employee_id ?? 'No Employee ID' }} &bull; {{ $teacher->department?->name ?? 'No Department' }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/25 px-3 py-1 text-xs font-bold text-white">{{ $teacher->designation ?? 'Teacher' }}</span>
                        <span class="rounded-full bg-white/25 px-3 py-1 text-xs font-bold text-white">{{ ucfirst($teacher->employment_type ?? 'Unknown') }}</span>
                        @if($teacher->is_active)
                            <span class="rounded-full bg-emerald-400/40 px-3 py-1 text-xs font-bold text-white">● Active</span>
                        @else
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-bold text-white/60">● Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2 flex-shrink-0 self-start" x-data="{ confirmDelete: false }">
                    <a href="{{ route('admin.teachers.edit', $teacher) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <button type="button" @click="confirmDelete = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-500/30 hover:bg-red-500/50 px-4 py-2 text-sm font-bold text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                    <a href="{{ route('admin.teachers.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">
                        ← Back
                    </a>
                    {{-- Delete confirm modal --}}
                    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
                         @keydown.escape.window="confirmDelete = false">
                        <div class="absolute inset-0 bg-black/50" @click="confirmDelete = false"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
                        <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <h3 class="text-base font-black text-slate-900">Delete Teacher?</h3>
                            <p class="mt-1 text-sm text-slate-500">This will permanently remove <strong>{{ $teacher->user?->name }}</strong> and their account. This action cannot be undone.</p>
                            <div class="mt-5 flex gap-3">
                                <button type="button" @click="confirmDelete = false" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" class="flex-1">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">Yes, Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI Strip --}}
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                    $heroKpis = [
                        ['val' => $stats['totalSessionsConducted'], 'lbl' => 'Sessions Conducted'],
                        ['val' => $stats['monthSessionsConducted'], 'lbl' => 'Sessions This Month'],
                        ['val' => count($stats['subjectsBySemester']), 'lbl' => 'Subjects Assigned'],
                        ['val' => $stats['avgPassRate'] . '%',        'lbl' => 'Average Pass Rate'],
                    ];
                @endphp
                @foreach($heroKpis as $k)
                <div class="rounded-xl bg-white/15 backdrop-blur-sm px-4 py-3 text-center">
                    <p class="text-2xl font-black text-white">{{ $k['val'] }}</p>
                    <p class="text-xs text-white/70 mt-0.5">{{ $k['lbl'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── TABS ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex border-b border-slate-100 overflow-x-auto">
            @foreach(['overview'=>'Overview','subjects'=>'Subjects & Timetable','attendance'=>'Attendance','performance'=>'Performance','timeline'=>'Activity Log'] as $key => $label)
            <button type="button"
                    @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-b-2 border-[#8B0000] text-[#8B0000] bg-[#8B0000]/5 font-bold' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold transition flex-shrink-0">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ── OVERVIEW ── --}}
        <div x-show="tab === 'overview'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Personal Information</h3>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div><dt class="text-slate-500">Full Name</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->user?->name ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Email</dt><dd class="mt-0.5 font-semibold text-slate-800 truncate">{{ $teacher->user?->email ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Phone</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->user?->phone ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Gender</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ ucfirst($teacher->user?->gender ?? '—') }}</dd></div>
                            <div><dt class="text-slate-500">Date of Birth</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->user?->dob ? bsDate($teacher->user->dob, 'Y, F d') : '—' }}</dd></div>
                            <div><dt class="text-slate-500">Address</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->user?->address ?? '—' }}</dd></div>
                        </dl>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Employment Details</h3>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div><dt class="text-slate-500">Employee ID</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->employee_id ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Department</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->department?->name ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Designation</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->designation ?? '—' }}</dd></div>
                            <div><dt class="text-slate-500">Employment Type</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ ucfirst($teacher->employment_type ?? '—') }}</dd></div>
                            <div><dt class="text-slate-500">Join Date</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->join_date ? bsDate($teacher->join_date, 'Y, F d') : '—' }}</dd></div>
                            <div><dt class="text-slate-500">Status</dt><dd class="mt-0.5 font-semibold {{ $teacher->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $teacher->is_active ? 'Active' : 'Inactive' }}</dd></div>
                            <div><dt class="text-slate-500">Added On</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ bsDate($teacher->created_at, 'Y, F d') }}</dd></div>
                            <div><dt class="text-slate-500">Last Updated</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ bsDate($teacher->updated_at, 'Y, F d') }}</dd></div>
                        </dl>
                    </div>
                </div>
                <div class="space-y-5">
                    @if($teacher->qualification || $teacher->specialization)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Academic Background</h3>
                        <dl class="space-y-3 text-sm">
                            @if($teacher->qualification)
                            <div><dt class="text-slate-500">Qualification</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->qualification }}</dd></div>
                            @endif
                            @if($teacher->specialization)
                            <div><dt class="text-slate-500">Specialization</dt><dd class="mt-0.5 font-semibold text-slate-800">{{ $teacher->specialization }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    @endif
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-5">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Semesters Handled</h3>
                        @if(empty($stats['semestersHandled']))
                            <p class="text-sm text-slate-400">No semesters yet.</p>
                        @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($stats['semestersHandled'] as $sem)
                            <span class="rounded-xl border border-violet-200 bg-violet-50 px-3 py-1.5 text-sm font-bold text-violet-700">Semester {{ $sem }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SUBJECTS & TIMETABLE ── --}}
        <div x-show="tab === 'subjects'" class="p-6 space-y-6">
            <div>
                <h3 class="mb-4 text-sm font-bold text-slate-700">Assigned Subjects</h3>
                @if(empty($stats['subjectsBySemester']))
                    <p class="py-8 text-center text-sm text-slate-400">No subjects assigned.</p>
                @else
                <div class="space-y-4">
                    @foreach($stats['subjectsBySemester'] as $semester => $subjects)
                    <div>
                        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Semester {{ $semester }}</p>
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">Subject</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">Code</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">Program</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">Section</th>
                                        <th class="px-4 py-2.5 text-left text-[11px] font-bold uppercase text-slate-400">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($subjects as $subj)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-2.5 font-medium text-slate-700">{{ $subj['name'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-500 font-mono text-xs">{{ $subj['code'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-500">{{ $subj['program'] }}</td>
                                        <td class="px-4 py-2.5 text-slate-500">{{ $subj['section'] ?? '—' }}</td>
                                        <td class="px-4 py-2.5 text-slate-500">{{ $subj['type'] ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div>
                <h3 class="mb-4 text-sm font-bold text-slate-700">Weekly Timetable</h3>
                @php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                @if($stats['timetableSlots']->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">No timetable slots assigned.</p>
                @else
                <div class="space-y-3">
                    @foreach($days as $day)
                    @php $slots = $stats['timetableSlots']->get($day, collect()); @endphp
                    @if($slots->isNotEmpty())
                    <div class="flex gap-3 items-start">
                        <div class="w-20 shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-center text-[11px] font-bold text-slate-600 mt-1">{{ $day }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($slots as $slot)
                            <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs">
                                <p class="font-bold text-indigo-700">{{ $slot->subject?->name }}</p>
                                <p class="text-indigo-500">{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}</p>
                                @if($slot->room_number)<p class="text-indigo-400">Room {{ $slot->room_number }}</p>@endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ── ATTENDANCE ── --}}
        <div x-show="tab === 'attendance'" class="p-6 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-xl border border-slate-200 p-4 text-center">
                    <p class="text-3xl font-black text-slate-800">{{ $stats['totalSessionsConducted'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Sessions</p>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 text-center">
                    <p class="text-3xl font-black text-[#8B0000]">{{ $stats['monthSessionsConducted'] }}</p>
                    <p class="text-xs text-slate-500 mt-1">This Month</p>
                </div>
            </div>
            <div class="rounded-xl border border-slate-200 p-5">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Monthly Sessions (Last 6 Months)</h3>
                <div style="position:relative;height:160px">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ── PERFORMANCE ── --}}
        <div x-show="tab === 'performance'" class="p-6 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="rounded-xl border border-slate-200 p-4 text-center">
                    <p class="text-3xl font-black text-slate-800">{{ $stats['avgPassRate'] }}<span class="text-xl text-slate-400">%</span></p>
                    <p class="text-xs text-slate-500 mt-1">Avg Pass Rate</p>
                </div>
            </div>
            @if($stats['performanceBySubject']->isNotEmpty())
            <div class="rounded-xl border border-slate-200 p-5">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">Pass Rate by Subject</h3>
                <div style="position:relative;height:160px">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
            <div class="space-y-2.5">
                @foreach($stats['performanceBySubject'] as $subjectName => $rate)
                <div class="flex items-center gap-4">
                    <p class="w-48 text-sm font-medium text-slate-600 truncate shrink-0">{{ $subjectName }}</p>
                    <div class="flex-1 rounded-full bg-slate-100 h-2.5">
                        <div class="h-2.5 rounded-full transition-all {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 60 ? 'bg-amber-400' : 'bg-red-400') }}"
                             style="width: {{ min($rate, 100) }}%"></div>
                    </div>
                    <p class="w-12 text-right text-sm font-bold text-slate-700 shrink-0">{{ $rate }}%</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="rounded-xl border border-slate-200 py-12 text-center">
                <p class="text-sm text-slate-400">No performance data available yet.</p>
                <p class="text-xs text-slate-300 mt-1">Performance is computed from published exam marks.</p>
            </div>
            @endif
        </div>

        {{-- ── TIMELINE ── --}}
        <div x-show="tab === 'timeline'" class="p-6">
            @if(isset($stats['timeline']) && $stats['timeline']->isNotEmpty())
            <div class="relative space-y-0 before:absolute before:left-[23px] before:top-0 before:h-full before:w-px before:bg-slate-200">
                @foreach($stats['timeline'] as $log)
                <div class="relative flex gap-4 pb-5">
                    <div class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-100 shadow-sm">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0 flex-1 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-700">{{ $log->action }}</p>
                            <time class="shrink-0 text-xs text-slate-400">{{ $log->created_at?->diffForHumans() }}</time>
                        </div>
                        @if($log->user)
                        <p class="mt-1 text-xs text-slate-400">by {{ $log->user->name ?? 'System' }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-500">No activity log entries yet.</p>
            </div>
            @endif
        </div>
    </div>

</div>{{-- /x-data --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @php
        $attendanceLabels = json_encode(array_keys($stats['monthlyAttendance']));
        $attendanceValues = json_encode(array_values($stats['monthlyAttendance']));
        $perfLabels = json_encode($stats['performanceBySubject']->keys()->all());
        $perfValues = json_encode($stats['performanceBySubject']->values()->all());
    @endphp

    const attCtx = document.getElementById('attendanceChart');
    if (attCtx && window.Chart) {
        new Chart(attCtx, {
            type: 'bar',
            data: {
                labels: {!! $attendanceLabels !!},
                datasets: [{
                    label: 'Sessions',
                    data: {!! $attendanceValues !!},
                    backgroundColor: '#8B0000cc',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const perfCtx = document.getElementById('performanceChart');
    if (perfCtx && window.Chart) {
        new Chart(perfCtx, {
            type: 'bar',
            data: {
                labels: {!! $perfLabels !!},
                datasets: [{
                    label: 'Pass Rate %',
                    data: {!! $perfValues !!},
                    backgroundColor: '#6366f1cc',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
            }
        });
    }
});
</script>
@endpush
