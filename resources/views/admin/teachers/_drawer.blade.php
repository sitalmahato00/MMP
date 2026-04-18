@php
    $grad = collect(['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'])[$teacher->id % 6];
    $desigMap = ['HOD'=>['bg-purple-100','text-purple-700'],'Coordinator'=>['bg-blue-100','text-blue-700'],'Teacher'=>['bg-slate-100','text-slate-600']];
    $desigCls = $desigMap[$teacher->designation] ?? ['bg-slate-100','text-slate-600'];
    $empMap   = ['permanent'=>['bg-emerald-100','text-emerald-700'],'contract'=>['bg-amber-100','text-amber-700'],'part-time'=>['bg-sky-100','text-sky-700']];
    $empCls   = $empMap[$teacher->employment_type] ?? ['bg-slate-100','text-slate-600'];
@endphp

<div x-data="{ tab: 'overview' }">

    {{-- ── HERO ── --}}
    <div class="relative bg-gradient-to-br {{ $grad }} px-6 pb-6 pt-8">
        <div class="flex items-end gap-4">
            @if($teacher->user?->avatar)
                <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt=""
                     class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white shadow-lg"/>
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/25 text-3xl font-black text-white ring-4 ring-white shadow-lg">
                    {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0 pb-1">
                <h2 class="text-xl font-black text-white leading-tight truncate">{{ $teacher->user?->name }}</h2>
                <p class="text-white/80 text-sm">{{ $teacher->employee_id ?? '—' }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $desigCls[0] }} {{ $desigCls[1] }}">{{ $teacher->designation ?? 'Teacher' }}</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $empCls[0] }} {{ $empCls[1] }}">{{ ucfirst($teacher->employment_type ?? '—') }}</span>
                    @if($teacher->is_active)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">● Active</span>
                    @else
                        <span class="rounded-full bg-white/30 px-2.5 py-0.5 text-xs font-semibold text-white">● Inactive</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-5 flex gap-2" x-data="{ confirmDelete: false }">
            <a href="{{ route('admin.teachers.show', $teacher) }}"
               class="flex-1 rounded-xl bg-white/20 hover:bg-white/30 px-3 py-2 text-center text-xs font-bold text-white transition">
                Full Profile →
            </a>
            <a href="{{ route('admin.teachers.edit', $teacher) }}"
               class="flex-1 rounded-xl bg-white/20 hover:bg-white/30 px-3 py-2 text-center text-xs font-bold text-white transition">
                Edit
            </a>
            <button type="button" @click="confirmDelete = true"
                    class="rounded-xl bg-red-500/30 hover:bg-red-500/50 px-3 py-2 text-xs font-bold text-white transition">
                Delete
            </button>
            {{-- Delete confirm --}}
            <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center px-4"
                 @keydown.escape.window="confirmDelete = false">
                <div class="absolute inset-0 bg-black/50" @click="confirmDelete = false"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
                <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h3 class="text-base font-black text-slate-900">Delete Teacher?</h3>
                    <p class="mt-1 text-sm text-slate-500">Remove <strong>{{ $teacher->user?->name }}</strong> permanently?</p>
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

    {{-- ── STATS STRIP ── --}}
    <div class="grid grid-cols-4 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50/60">
        @php
            $strip = [
                ['val' => $stats['totalSessionsConducted'], 'lbl' => 'Sessions'],
                ['val' => $stats['monthSessionsConducted'], 'lbl' => 'This Month'],
                ['val' => count($stats['subjectsBySemester']), 'lbl' => 'Subjects'],
                ['val' => $stats['avgPassRate'] . '%',        'lbl' => 'Pass Rate'],
            ];
        @endphp
        @foreach($strip as $s)
        <div class="py-3 text-center">
            <p class="text-lg font-black text-slate-800">{{ $s['val'] }}</p>
            <p class="text-[10px] text-slate-500">{{ $s['lbl'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── TABS ── --}}
    <div class="flex border-b border-slate-100 overflow-x-auto">
        @foreach(['overview'=>'Overview','subjects'=>'Subjects','timetable'=>'Timetable','attendance'=>'Attendance','performance'=>'Performance'] as $key => $label)
        <button type="button"
                @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-b-2 border-[#8B0000] text-[#8B0000] font-bold' : 'text-slate-500 hover:text-slate-700'"
                class="whitespace-nowrap px-4 py-3 text-xs font-semibold transition flex-shrink-0">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── OVERVIEW ── --}}
    <div x-show="tab === 'overview'" class="p-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 space-y-2.5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Personal</p>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Email</dt><dd class="font-medium text-slate-700 truncate">{{ $teacher->user?->email ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Phone</dt><dd class="font-medium text-slate-700">{{ $teacher->user?->phone ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Gender</dt><dd class="font-medium text-slate-700">{{ ucfirst($teacher->user?->gender ?? '—') }}</dd></div>
                </dl>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 space-y-2.5">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Employment</p>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Department</dt><dd class="font-medium text-slate-700 truncate">{{ $teacher->department?->name ?? '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Joined</dt><dd class="font-medium text-slate-700">{{ $teacher->join_date ? bsDate($teacher->join_date, 'Y, F d') : '—' }}</dd></div>
                    <div class="flex gap-2"><dt class="w-24 text-slate-500 shrink-0">Type</dt><dd class="font-medium text-slate-700">{{ ucfirst($teacher->employment_type ?? '—') }}</dd></div>
                </dl>
            </div>
        </div>
        @if($teacher->qualification || $teacher->specialization)
        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Academic</p>
            <dl class="space-y-1.5 text-sm">
                @if($teacher->qualification)
                <div class="flex gap-2"><dt class="w-28 text-slate-500 shrink-0">Qualification</dt><dd class="font-medium text-slate-700">{{ $teacher->qualification }}</dd></div>
                @endif
                @if($teacher->specialization)
                <div class="flex gap-2"><dt class="w-28 text-slate-500 shrink-0">Specialization</dt><dd class="font-medium text-slate-700">{{ $teacher->specialization }}</dd></div>
                @endif
            </dl>
        </div>
        @endif
        @if($teacher->user?->address)
        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm">
            <span class="text-slate-400">Address: </span><span class="font-medium text-slate-700">{{ $teacher->user->address }}</span>
        </div>
        @endif
    </div>

    {{-- ── SUBJECTS ── --}}
    <div x-show="tab === 'subjects'" class="p-5">
        @if(empty($stats['subjectsBySemester']))
        <p class="py-12 text-center text-sm text-slate-400">No subjects assigned yet.</p>
        @else
        <div class="space-y-4">
            @foreach($stats['subjectsBySemester'] as $semester => $subjects)
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Semester {{ $semester }}</p>
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase text-slate-400">Subject</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase text-slate-400">Code</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase text-slate-400">Program</th>
                                <th class="px-3 py-2 text-left text-[10px] font-bold uppercase text-slate-400">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subjects as $subj)
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-700">{{ $subj['name'] }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $subj['code'] }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $subj['program'] }}</td>
                                <td class="px-3 py-2 text-slate-500">{{ $subj['section'] ?? '—' }}</td>
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

    {{-- ── TIMETABLE ── --}}
    <div x-show="tab === 'timetable'" class="p-5">
        @php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
        @if($stats['timetableSlots']->isEmpty())
        <p class="py-12 text-center text-sm text-slate-400">No timetable slots assigned.</p>
        @else
        <div class="space-y-3">
            @foreach($days as $day)
            @php $slots = $stats['timetableSlots']->get($day, collect()); @endphp
            @if($slots->isNotEmpty())
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $day }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($slots as $slot)
                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs">
                        <p class="font-bold text-blue-700">{{ $slot->subject?->name }}</p>
                        <p class="text-blue-500">{{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}</p>
                        @if($slot->room_number)<p class="text-blue-400">Room {{ $slot->room_number }}</p>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── ATTENDANCE ── --}}
    <div x-show="tab === 'attendance'" class="p-5 space-y-4">
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                <p class="text-2xl font-black text-slate-800">{{ $stats['totalSessionsConducted'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Total Sessions</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                <p class="text-2xl font-black text-[#8B0000]">{{ $stats['monthSessionsConducted'] }}</p>
                <p class="text-xs text-slate-500 mt-0.5">This Month</p>
            </div>
        </div>
        <div class="rounded-xl border border-slate-100 p-4">
            <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">Sessions Conducted (Last 6 Months)</p>
            @php $maxAtt = max(array_values($stats['monthlyAttendance']) ?: [1]); @endphp
            <div class="flex items-end gap-1.5 h-20">
                @foreach($stats['monthlyAttendance'] as $label => $count)
                @php $pct = $maxAtt > 0 ? round(($count / $maxAtt) * 100) : 0; @endphp
                <div class="flex flex-1 flex-col items-center gap-1">
                    <span class="text-[10px] font-bold text-slate-600">{{ $count }}</span>
                    <div class="w-full rounded-t-md bg-[#8B0000]/80 transition-all" style="height:{{ max($pct, 4) }}%"></div>
                    <span class="text-[9px] text-slate-400 truncate w-full text-center">{{ explode(' ', $label)[0] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── PERFORMANCE ── --}}
    <div x-show="tab === 'performance'" class="p-5 space-y-4">
        <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center mb-4">
            <p class="text-3xl font-black text-slate-800">{{ $stats['avgPassRate'] }}<span class="text-lg text-slate-500">%</span></p>
            <p class="text-xs text-slate-500">Avg Pass Rate</p>
        </div>
        @if($stats['performanceBySubject']->isNotEmpty())
        <div class="space-y-2">
            @foreach($stats['performanceBySubject'] as $subjectName => $rate)
            <div class="flex items-center gap-3">
                <p class="w-36 truncate text-xs font-medium text-slate-600 shrink-0">{{ $subjectName }}</p>
                <div class="flex-1 rounded-full bg-slate-100 h-2">
                    <div class="h-2 rounded-full {{ $rate >= 80 ? 'bg-emerald-500' : ($rate >= 60 ? 'bg-amber-400' : 'bg-red-400') }} transition-all"
                         style="width: {{ min($rate, 100) }}%"></div>
                </div>
                <p class="w-10 text-right text-xs font-bold text-slate-700">{{ $rate }}%</p>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-sm text-slate-400 py-6">No performance data available.</p>
        @endif
    </div>

</div>

{{-- No client-side scripts needed — charts are pure CSS --}}
