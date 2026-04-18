@extends('layouts.app')
@section('title', $parent->user?->name ?? 'Parent')

@section('content')
@php
    $isActive = $parent->user?->is_active ?? false;
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$parent->id % 6];
@endphp

<div x-data="{ tab: '{{ request('tab', 'overview') }}' }">

{{-- HERO HEADER --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 shadow-lg">
    <div class="absolute inset-0 opacity-5" style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative px-6 py-7">
        <div class="flex flex-wrap items-start gap-5">
            @if($parent->user?->avatar)
                <img src="{{ asset('storage/'.$parent->user->avatar) }}" alt=""
                     class="h-20 w-20 flex-shrink-0 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg"/>
            @else
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-3xl font-black text-white shadow-lg ring-4 ring-white/10">
                    {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-black text-white leading-tight">{{ $parent->user?->name }}</h1>
                    @if($isActive)
                        <span class="rounded-lg px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-200">Active</span>
                    @else
                        <span class="rounded-lg px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-600 ring-1 ring-slate-200">Inactive</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-400">{{ ucfirst($parent->relation_to_student ?? 'Parent') }} · {{ $parent->occupation ?? 'Occupation not set' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if($parent->user?->phone)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $parent->user->phone }}
                    </span>
                    @endif
                    @if($parent->user?->email)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $parent->user->email }}
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500/30 px-3 py-1.5 text-xs font-bold text-violet-200">
                        {{ $parent->students->count() }} {{ Str::plural('Child', $parent->students->count()) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-shrink-0 flex-wrap gap-2">
                <a href="{{ route('admin.parents.edit', $parent) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <a href="{{ route('admin.parents.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back
                </a>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $childrenStats->count() }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Children</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                @php $avgAtt = $childrenStats->avg('attendancePct'); @endphp
                <p class="text-2xl font-black {{ $avgAtt === null ? 'text-slate-300' : ($avgAtt >= 75 ? 'text-emerald-400' : ($avgAtt >= 50 ? 'text-amber-400' : 'text-red-400')) }}">
                    {{ $avgAtt !== null ? round($avgAtt).'%' : '—' }}
                </p>
                <p class="mt-0.5 text-[11px] text-slate-400">Avg Attendance</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                @php $avgMarks = $childrenStats->avg('avgMarks'); @endphp
                <p class="text-2xl font-black text-white">{{ $avgMarks !== null ? round($avgMarks, 1) : '—' }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Avg Marks</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3 text-center">
                <p class="text-2xl font-black text-white">{{ $lastLogin ? $lastLogin->diffForHumans(short: true) : 'Never' }}</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Last Login</p>
            </div>
        </div>
    </div>
</div>

{{-- TABS --}}
<div class="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm overflow-x-auto">
    @foreach(['overview' => 'Overview', 'children' => 'Children', 'account' => 'Account'] as $key => $label)
    <button @click="tab='{{ $key }}'"
            :class="tab==='{{ $key }}' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- OVERVIEW TAB --}}
<div x-show="tab==='overview'" x-cloak class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Personal Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Personal Information</h3>
            </div>
            <div class="p-5 space-y-3">
                @php
                    $fields = [
                        ['label' => 'Full Name',  'value' => $parent->user?->name],
                        ['label' => 'Email',      'value' => $parent->user?->email],
                        ['label' => 'Phone',      'value' => $parent->user?->phone],
                        ['label' => 'Address',    'value' => $parent->user?->address],
                        ['label' => 'Relation',   'value' => ucfirst($parent->relation_to_student ?? 'Parent')],
                        ['label' => 'Occupation', 'value' => $parent->occupation],
                    ];
                @endphp
                @foreach($fields as $f)
                <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                    <span class="text-sm text-slate-900 text-right">{{ $f['value'] ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Account Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Account Details</h3>
            </div>
            <div class="p-5 space-y-3">
                @php
                    $accountFields = [
                        ['label' => 'Account Status', 'value' => $isActive ? 'Active' : 'Inactive'],
                        ['label' => 'Last Login',     'value' => $lastLogin?->format('M d, Y h:i A') ?? 'Never'],
                        ['label' => 'Created',        'value' => $parent->created_at?->format('M d, Y')],
                        ['label' => 'Children Linked', 'value' => $parent->students->count()],
                    ];
                @endphp
                @foreach($accountFields as $f)
                <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                    <span class="text-sm text-slate-900 text-right">{{ $f['value'] ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- CHILDREN TAB --}}
<div x-show="tab==='children'" x-cloak class="space-y-4">
    @forelse($childrenStats as $childData)
    @php
        $s = $childData['student'];
        $attPct = $childData['attendancePct'];
        $avgM = $childData['avgMarks'];
        $attColor = $attPct === null ? 'slate' : ($attPct >= 75 ? 'emerald' : ($attPct >= 50 ? 'amber' : 'red'));
    @endphp
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5">
            <div class="flex flex-wrap items-start gap-4">
                {{-- Avatar --}}
                @if($s->user?->avatar)
                    <img src="{{ asset('storage/'.$s->user->avatar) }}" class="h-14 w-14 rounded-xl object-cover ring-2 ring-slate-100"/>
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-xl font-bold text-white">
                        {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.students.show', $s) }}" class="text-lg font-bold text-slate-900 hover:text-[#8B0000]">{{ $s->user?->name }}</a>
                        <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">{{ $s->student_no }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span>{{ $s->department?->name ?? '—' }}</span>
                        <span>→</span>
                        <span>{{ $s->program?->name ?? '—' }}</span>
                        <span>→</span>
                        <span class="font-bold text-violet-600">Sem {{ $s->current_semester }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.students.show', $s) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    View Child
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            {{-- Stats Row --}}
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="rounded-xl bg-{{ $attColor }}-50 p-3 text-center">
                    <p class="text-xl font-black text-{{ $attColor }}-700">{{ $attPct !== null ? $attPct.'%' : '—' }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Attendance</p>
                    @if($attPct !== null)
                    <div class="mt-2 h-1.5 rounded-full bg-{{ $attColor }}-100 overflow-hidden">
                        <div class="h-full rounded-full bg-{{ $attColor }}-500" style="width: {{ $attPct }}%"></div>
                    </div>
                    @endif
                </div>
                <div class="rounded-xl bg-blue-50 p-3 text-center">
                    <p class="text-xl font-black text-blue-700">{{ $avgM !== null ? $avgM : '—' }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Avg Marks</p>
                </div>
                <div class="rounded-xl bg-violet-50 p-3 text-center">
                    <p class="text-xl font-black text-violet-700">{{ $childData['totalMarks'] }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Exam Records</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-xl font-black text-slate-700">Sem {{ $s->current_semester }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-500">Current</p>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <h3 class="mt-4 text-lg font-bold text-slate-900">No children linked</h3>
        <p class="mt-1 text-sm text-slate-500">Link students to this parent account via the Edit page.</p>
        <a href="{{ route('admin.parents.edit', $parent) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
            Link Children
        </a>
    </div>
    @endforelse
</div>

{{-- ACCOUNT TAB --}}
<div x-show="tab==='account'" x-cloak class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Account Management</h3>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                <div>
                    <p class="text-sm font-bold text-slate-900">Account Status</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $isActive ? 'This account is active and can log in.' : 'This account is disabled.' }}</p>
                </div>
                @if($isActive)
                    <span class="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                @else
                    <span class="rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Disabled</span>
                @endif
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.parents.edit', $parent) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Account
                </a>
                <form method="POST" action="{{ route('admin.parents.destroy', $parent) }}"
                      onsubmit="return confirm('Are you sure you want to delete this parent account? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
@endsection
