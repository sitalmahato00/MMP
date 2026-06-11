@extends('layouts.app')
@section('title', 'Parents')

@section('content')
@php
    $statusMap = [
        'active'   => ['label'=>'Active',   'cls'=>'bg-blue-50 text-blue-700'],
        'inactive' => ['label'=>'Inactive', 'cls'=>'bg-slate-100 text-slate-600'],
    ];
@endphp

<div x-data="{
    view: localStorage.getItem('mmp_parents_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_parents_view', v); },
}" class="space-y-5">

<x-page-header title="Parents & Guardians" subtitle="Manage parent accounts and linked children">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.parents.create') }}">Add Parent</x-btn>
    </x-slot>
</x-page-header>

{{-- KPI CARDS --}}
@php
$parentKpis = [
    ['label'=>'Total Parents',    'value'=>number_format($totalParents),   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'grad'=>'135deg,#2563EB,#3B82F6'],
    ['label'=>'Linked Children',  'value'=>number_format($linkedChildren), 'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'grad'=>'135deg,#10B981,#22C55E'],
    ['label'=>'Unlinked Parents', 'value'=>number_format($unlinkedParents),'icon'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 'grad'=>'135deg,#DC2626,#EF4444'],
    ['label'=>'Recently Added',   'value'=>number_format($recentlyAdded),  'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'grad'=>'135deg,#7C3AED,#A855F7'],
];
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach($parentKpis as $kpi)
    <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
         style="background: linear-gradient({{ $kpi['grad'] }});">
        <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xl font-black leading-tight text-white">{{ $kpi['value'] }}</p>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80 truncate">{{ $kpi['label'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- FILTERS --}}
<form method="GET" action="{{ route('admin.parents.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="relative xl:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name, phone, email…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"/>
        </div>
        <select name="department_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">Child Department</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
            @endforeach
        </select>
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">Child Program</option>
            @foreach($programs as $prog)
                <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            <option value="">All Status</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Inactive</option>
        </select>
        <div class="flex gap-2">
            <select name="linked" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Parents</option>
                <option value="linked" @selected(request('linked') === 'linked')>With Children</option>
                <option value="unlinked" @selected(request('linked') === 'unlinked')>No Children</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition whitespace-nowrap">Apply</button>
            @if(request()->hasAny(['search','department_id','program_id','status','linked']))
            <a href="{{ route('admin.parents.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

{{-- MAIN TABLE --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($parents->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $parents->firstItem() }}–{{ $parents->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($parents->total()) }}</span> parents
            @else
                No parents match your filters
            @endif
        </p>
        <div class="flex items-center gap-1 rounded-lg border border-slate-200 p-0.5">
            <button @click="setView('table')" :class="view==='table' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'" class="rounded-md p-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </button>
            <button @click="setView('cards')" :class="view==='cards' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600'" class="rounded-md p-1.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </button>
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="view==='table'" x-cloak>
        @if($parents->count())
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/60">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Parent/Guardian</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Relation</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Linked Children</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($parents as $parent)
                    @php $isActive = $parent->user?->is_active ?? false; @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                @if($parent->user?->avatar)
                                    <img src="{{ asset('storage/'.$parent->user->avatar) }}" class="h-9 w-9 rounded-xl object-cover ring-2 ring-slate-100"/>
                                @else
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-bold text-white">
                                        {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.parents.show', $parent) }}" class="font-semibold text-slate-900 hover:text-[#8B0000] truncate block">{{ $parent->user?->name }}</a>
                                    <p class="text-xs text-slate-400">{{ $parent->occupation ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-sm text-slate-700">{{ $parent->user?->phone ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $parent->user?->email }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ ucfirst($parent->relation_to_student ?? 'Parent') }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @forelse($parent->students as $student)
                                    <a href="{{ route('admin.students.show', $student) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition">
                                        {{ $student->user?->name }}
                                    </a>
                                @empty
                                    <span class="text-xs text-slate-400 italic">No children linked</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($isActive)
                                <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">Active</span>
                            @else
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <x-table-actions
                                :show="route('admin.parents.show', $parent)"
                                :edit="route('admin.parents.edit', $parent)"
                                :destroy="route('admin.parents.destroy', $parent)"
                                name="{{ addslashes($parent->user?->name ?? 'this parent') }}"
                            />
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-16 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">No parents found</h3>
            <p class="mt-1 text-sm text-slate-500">Parents will appear here after creation or import.</p>
            <a href="{{ route('admin.parents.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add First Parent
            </a>
        </div>
        @endif
    </div>

    {{-- CARD VIEW --}}
    <div x-show="view==='cards'" x-cloak class="p-5">
        @if($parents->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($parents as $parent)
            @php $isActive = $parent->user?->is_active ?? false; @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    @if($parent->user?->avatar)
                        <img src="{{ asset('storage/'.$parent->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-lg font-bold text-white">
                            {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('admin.parents.show', $parent) }}" class="font-bold text-slate-900 hover:text-[#8B0000] truncate block">{{ $parent->user?->name }}</a>
                        <p class="text-xs text-slate-500">{{ ucfirst($parent->relation_to_student ?? 'Parent') }} · {{ $parent->occupation ?? '—' }}</p>
                    </div>
                    @if($isActive)
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">Active</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">Inactive</span>
                    @endif
                </div>
                <div class="mt-3 space-y-1.5">
                    <p class="text-xs text-slate-500"><span class="font-semibold text-slate-600">Phone:</span> {{ $parent->user?->phone ?? '—' }}</p>
                    <p class="text-xs text-slate-500"><span class="font-semibold text-slate-600">Email:</span> {{ $parent->user?->email }}</p>
                </div>
                <div class="mt-3 flex flex-wrap gap-1">
                    @forelse($parent->students as $student)
                        <a href="{{ route('admin.students.show', $student) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-100 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $student->user?->name }}
                        </a>
                    @empty
                        <span class="text-[11px] text-slate-400 italic">No children linked</span>
                    @endforelse
                </div>
                <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                    <a href="{{ route('admin.parents.show', $parent) }}" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                    <a href="{{ route('admin.parents.edit', $parent) }}" class="flex-1 rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Edit</a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-12 text-center">
            <p class="text-sm text-slate-500">No parents match your filters.</p>
        </div>
        @endif
    </div>

    {{-- PAGINATION --}}
    @if($parents->hasPages())
    <div class="border-t border-slate-100 px-5 py-4">
        {{ $parents->links() }}
    </div>
    @endif
</div>

</div>
@endsection
