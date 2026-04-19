@extends('layouts.app')
@section('title', 'Admission Applications')

@section('content')
@php
    $statusTone = [
        'pending' => [
            'badge' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'row' => 'border-l-slate-300',
        ],
        'reviewed' => [
            'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'row' => 'border-l-sky-400',
        ],
        'contacted' => [
            'badge' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'row' => 'border-l-violet-400',
        ],
        'accepted' => [
            'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'row' => 'border-l-emerald-400',
        ],
        'rejected' => [
            'badge' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'row' => 'border-l-rose-400',
        ],
    ];

    $toneStyles = [
        'slate' => ['chip' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-500'],
        'zinc' => ['chip' => 'bg-zinc-100 text-zinc-700', 'dot' => 'bg-zinc-500'],
        'emerald' => ['chip' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500'],
        'rose' => ['chip' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
        'violet' => ['chip' => 'bg-violet-100 text-violet-700', 'dot' => 'bg-violet-500'],
        'sky' => ['chip' => 'bg-sky-100 text-sky-700', 'dot' => 'bg-sky-500'],
    ];

    $applicationPayload = $applications->getCollection()->map(function ($app) {
        $createdAtBs = bsDate($app->created_at, 'Y, F d h:i A') ?: optional($app->created_at)->format('Y-m-d H:i');
        $updatedAtBs = bsDate($app->updated_at, 'Y, F d h:i A') ?: optional($app->updated_at)->format('Y-m-d H:i');
        $isNew = optional($app->created_at)->gt(now()->subHours(24));
        $documentsMissing = blank($app->gpa) || blank($app->previous_school);

        return [
            'id' => $app->id,
            'full_name' => $app->full_name,
            'email' => $app->email,
            'phone' => $app->phone,
            'gender' => $app->gender,
            'address' => $app->address,
            'dob' => $app->dob ? (bsDate($app->dob, 'Y, F d') ?: optional($app->dob)->format('Y-m-d')) : null,
            'guardian_name' => $app->guardian_name,
            'guardian_phone' => $app->guardian_phone,
            'previous_school' => $app->previous_school,
            'gpa' => $app->gpa,
            'department' => $app->department?->name,
            'message' => $app->message,
            'status' => $app->status,
            'admin_notes' => $app->admin_notes,
            'created_at_bs' => $createdAtBs,
            'updated_at_bs' => $updatedAtBs,
            'is_new' => $isNew,
            'documents_missing' => $documentsMissing,
        ];
    })->values();
@endphp

<div
    x-data="{
        openDrawer: false,
        selectedApplication: null,
        autoRefresh: false,
        filterDrawer: false,
        bulkAction: '',
        selectedIds: [],
        allIds: @js($applications->pluck('id')->all()),
        applications: @js($applicationPayload),
        openDetails(id) {
            this.selectedApplication = this.applications.find((item) => item.id === id) || null;
            this.openDrawer = true;
        },
        closeDetails() {
            this.openDrawer = false;
        },
        toggleAll(event) {
            this.selectedIds = event.target.checked ? [...this.allIds] : [];
        },
        applyBulkStatus(status) {
            this.bulkAction = status;
            this.$refs.bulkStatus.value = status;
            this.$refs.bulkIds.value = JSON.stringify(this.selectedIds);
            this.$refs.bulkForm.submit();
        },
        timelineFor(app) {
            if (!app) return [];
            const steps = [
                { label: 'Application submitted', at: app.created_at_bs, done: true },
                { label: 'Reviewed by admin', at: app.status !== 'pending' ? app.updated_at_bs : null, done: ['reviewed','contacted','accepted','rejected'].includes(app.status) },
                { label: 'Contacted applicant', at: ['contacted','accepted','rejected'].includes(app.status) ? app.updated_at_bs : null, done: ['contacted','accepted','rejected'].includes(app.status) },
                { label: app.status === 'accepted' ? 'Application accepted' : app.status === 'rejected' ? 'Application rejected' : 'Final decision pending', at: ['accepted','rejected'].includes(app.status) ? app.updated_at_bs : null, done: ['accepted','rejected'].includes(app.status) },
            ];
            return steps;
        },
        init() {
            this.$watch('autoRefresh', (value) => {
                if (!value) {
                    return;
                }
                setTimeout(() => {
                    if (this.autoRefresh) {
                        window.location.reload();
                    }
                }, 60000);
            });
        }
    }"
    class="space-y-6"
>
    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950">Admission Applications</h1>
                <p class="mt-2 text-sm text-slate-500">Manage and review online admission applications submitted via public portal.</p>
            </div>
            <label class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600">
                <input type="checkbox" x-model="autoRefresh" class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                Auto refresh 60s
            </label>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($overviewStats as $card)
                @php
                    $tone = $toneStyles[$card['tone']] ?? $toneStyles['slate'];
                    $trendClass = $card['trend']['direction'] === 'up'
                        ? 'text-emerald-600'
                        : ($card['trend']['direction'] === 'down' ? 'text-rose-600' : 'text-slate-400');
                    $trendArrow = $card['trend']['direction'] === 'up' ? '↑' : ($card['trend']['direction'] === 'down' ? '↓' : '•');
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $card['label'] }}</p>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tone['chip'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $tone['dot'] }}"></span>
                            {{ $card['trend']['label'] }}
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 text-xs font-semibold {{ $trendClass }}">{{ $trendArrow }} last 30 days vs previous 30 days</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex items-center justify-between gap-3 lg:hidden">
            <p class="text-sm font-semibold text-slate-700">Search & filters</p>
            <button type="button" @click="filterDrawer = true" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700">Open Filters</button>
        </div>

        <form method="GET" action="{{ route('admin.applications.index') }}" class="hidden lg:block">
            <div class="grid gap-3 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, email, or phone..."
                        class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100"
                    >
                </div>
                <div class="lg:col-span-2">
                    <select name="status" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                        <option value="">All Status</option>
                        @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'contacted' => 'Contacted', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <select name="department_id" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected((string) request('department_id') === (string) $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <select name="gender" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                        <option value="">All</option>
                        <option value="male" @selected(request('gender') === 'male')>Male</option>
                        <option value="female" @selected(request('gender') === 'female')>Female</option>
                        <option value="other" @selected(request('gender') === 'other')>Other</option>
                    </select>
                </div>
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="w-full rounded-full bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#750000]">Search</button>
                    <a href="{{ route('admin.applications.index') }}" class="w-full rounded-full border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-50">Reset</a>
                </div>
            </div>
            <div class="mt-3 grid gap-3 lg:grid-cols-8">
                <div class="lg:col-span-3 text-xs text-slate-500 self-center">Applied between</div>
                <div class="lg:col-span-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                </div>
                <div class="lg:col-span-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                </div>
                <div class="lg:col-span-1 text-xs text-slate-500 self-center">BS shown in list</div>
            </div>
        </form>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <p class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-700">{{ $applications->firstItem() ?? 0 }}-{{ $applications->lastItem() ?? 0 }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($applications->total()) }}</span> applications
            </p>

            <form x-ref="bulkForm" method="POST" action="{{ route('admin.applications.bulk-update-status') }}" class="flex flex-wrap items-center gap-2">
                @csrf
                @method('PATCH')
                <input x-ref="bulkStatus" type="hidden" name="status" value="">
                <input x-ref="bulkIds" type="hidden" name="application_ids" value="">

                <button type="button" @click="if (selectedIds.length) applyBulkStatus('accepted')" class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">Bulk Accept</button>
                <button type="button" @click="if (selectedIds.length) applyBulkStatus('rejected')" class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">Bulk Reject</button>
                <button type="button" @click="if (selectedIds.length) applyBulkStatus('reviewed')" class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700 transition hover:bg-sky-100">Bulk Mark Reviewed</button>
                <button type="button" @click="if (selectedIds.length) applyBulkStatus('contacted')" class="rounded-full border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700 transition hover:bg-violet-100">Bulk Mark Contacted</button>
            </form>
        </div>

        <div class="hidden lg:block overflow-hidden">
            <div class="mmp-table-wrap">
                <table class="mmp-table divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <th class="px-4 py-3 text-left w-10">
                                <input type="checkbox" @change="toggleAll($event)" class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                            </th>
                            <th class="px-4 py-3 text-left">Applicant</th>
                            <th class="px-4 py-3 text-left">Contact</th>
                            <th class="px-4 py-3 text-left">Department</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Applied Date (BS)</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($applications as $app)
                            @php
                                $tone = $statusTone[$app->status] ?? $statusTone['pending'];
                                $isNew = optional($app->created_at)->gt(now()->subHours(24));
                                $documentsMissing = blank($app->gpa) || blank($app->previous_school);
                                $initial = strtoupper(substr($app->full_name, 0, 1));
                                $gradient = ['from-blue-500 to-indigo-600', 'from-violet-500 to-purple-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-rose-500 to-pink-600'][$app->id % 5];
                            @endphp
                            <tr class="border-l-4 {{ $tone['row'] }} hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <input type="checkbox" value="{{ $app->id }}" x-model="selectedIds" class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-xs font-black text-white">{{ $initial }}</div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $app->full_name }}</p>
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                                @if($app->gender)
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600 capitalize">{{ $app->gender }}</span>
                                                @endif
                                                @if($isNew)
                                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">New</span>
                                                @endif
                                                @if($documentsMissing)
                                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700">Missing docs</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">
                                    <p>{{ $app->email }}</p>
                                    <p class="text-xs text-slate-400">{{ $app->phone }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $app->department?->name ?? '—' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $tone['badge'] }}">{{ ucfirst($app->status) }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-500">{{ bsDate($app->created_at, 'Y, F d h:i A') ?: $app->created_at }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openDetails({{ $app->id }})" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="View Application">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @foreach(['accepted' => 'emerald', 'rejected' => 'rose', 'contacted' => 'violet', 'reviewed' => 'sky'] as $state => $color)
                                            <form method="POST" action="{{ route('admin.applications.update-status', $app) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $state }}">
                                                <button class="rounded-lg p-1.5 text-{{ $color }}-500 transition hover:bg-{{ $color }}-50" title="Mark {{ ucfirst($state) }}">
                                                    @if($state === 'accepted')
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    @elseif($state === 'rejected')
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    @elseif($state === 'contacted')
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-2 10H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2z"/></svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500">No applications found for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 p-4 lg:hidden">
            @forelse($applications as $app)
                @php
                    $tone = $statusTone[$app->status] ?? $statusTone['pending'];
                    $isNew = optional($app->created_at)->gt(now()->subHours(24));
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm border-l-4 {{ $tone['row'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $app->full_name }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ $app->email }} · {{ $app->phone }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $app->department?->name ?? '—' }} · {{ bsDate($app->created_at, 'Y, F d') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $tone['badge'] }}">{{ ucfirst($app->status) }}</span>
                            @if($isNew)
                                <p class="mt-1 text-[11px] font-semibold text-emerald-600">New</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        <button type="button" @click="openDetails({{ $app->id }})" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">View</button>
                    </div>
                </article>
            @empty
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-6 text-center text-slate-500">No applications found for selected filters.</p>
            @endforelse
        </div>

        @if($applications->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $applications->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">Applications by Department</h2>
                <span class="text-xs font-semibold text-slate-400">Bar chart</span>
            </div>
            <div class="space-y-3">
                @forelse($insights['department'] as $row)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-bold">{{ $row['value'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-gradient-to-r from-sky-500 to-blue-600" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No department data yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">Gender Distribution</h2>
                <span class="text-xs font-semibold text-slate-400">Donut style</span>
            </div>
            <div class="space-y-3">
                @forelse($insights['gender'] as $row)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 text-sm">
                        <span class="capitalize text-slate-600">{{ $row['label'] }}</span>
                        <span class="font-bold text-slate-900">{{ $row['value'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No gender data yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">Application Status Distribution</h2>
                <span class="text-xs font-semibold text-slate-400">Bar chart</span>
            </div>
            <div class="space-y-3">
                @forelse($insights['status'] as $row)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs text-slate-600">
                            <span class="capitalize">{{ $row['label'] }}</span>
                            <span class="font-bold">{{ $row['value'] }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-gradient-to-r from-[#8B0000] to-rose-600" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No status data yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900">Applications Over Time</h2>
                <span class="text-xs font-semibold text-slate-400">Last 14 days</span>
            </div>
            <div class="space-y-2">
                @foreach($insights['daily'] as $point)
                    @php
                        $maxDaily = max(1, collect($insights['daily'])->max('value'));
                        $width = round(($point['value'] / $maxDaily) * 100, 1);
                    @endphp
                    <div class="grid grid-cols-[70px_1fr_32px] items-center gap-2 text-xs">
                        <span class="text-slate-500">{{ $point['label'] }}</span>
                        <div class="h-2 rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500" style="width: {{ $width }}%"></div>
                        </div>
                        <span class="text-right font-semibold text-slate-700">{{ $point['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <div x-show="openDrawer" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/45" @click="closeDetails()"></div>
        <aside class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto bg-white shadow-2xl">
            <div class="sticky top-0 z-10 border-b border-slate-100 bg-white px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Applicant profile</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900" x-text="selectedApplication?.full_name"></h3>
                    </div>
                    <button type="button" @click="closeDetails()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">Close</button>
                </div>
            </div>

            <div class="space-y-5 p-5" x-show="selectedApplication">
                <section class="grid gap-4 md:grid-cols-2">
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-black text-slate-900">Profile</h4>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Gender</dt><dd class="font-semibold text-slate-900 capitalize" x-text="selectedApplication?.gender || '—'"></dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Date of Birth</dt><dd class="font-semibold text-slate-900" x-text="selectedApplication?.dob || '—'"></dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Department</dt><dd class="font-semibold text-slate-900" x-text="selectedApplication?.department || '—'"></dd></div>
                        </dl>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-black text-slate-900">Contact</h4>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Email</dt><dd class="font-semibold text-slate-900" x-text="selectedApplication?.email || '—'"></dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Phone</dt><dd class="font-semibold text-slate-900" x-text="selectedApplication?.phone || '—'"></dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Address</dt><dd class="font-semibold text-slate-900 text-right" x-text="selectedApplication?.address || '—'"></dd></div>
                        </dl>
                    </article>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="text-sm font-black text-slate-900">Education & guardian</h4>
                    <div class="mt-3 grid gap-3 md:grid-cols-2 text-sm">
                        <div><p class="text-slate-500">Previous School</p><p class="font-semibold text-slate-900" x-text="selectedApplication?.previous_school || 'Not provided'"></p></div>
                        <div><p class="text-slate-500">GPA</p><p class="font-semibold text-slate-900" x-text="selectedApplication?.gpa || 'Not provided'"></p></div>
                        <div><p class="text-slate-500">Guardian Name</p><p class="font-semibold text-slate-900" x-text="selectedApplication?.guardian_name || 'Not provided'"></p></div>
                        <div><p class="text-slate-500">Guardian Phone</p><p class="font-semibold text-slate-900" x-text="selectedApplication?.guardian_phone || 'Not provided'"></p></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="text-sm font-black text-slate-900">Uploaded documents</h4>
                    <p class="mt-2 text-sm text-slate-500" x-show="selectedApplication?.documents_missing">Required documents are missing or incomplete.</p>
                    <p class="mt-2 text-sm text-slate-500" x-show="!selectedApplication?.documents_missing">Marksheet, photo, and citizenship verified in current form data.</p>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="text-sm font-black text-slate-900">Timeline</h4>
                    <div class="mt-3 space-y-3">
                        <template x-for="step in timelineFor(selectedApplication)" :key="step.label">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-2.5 w-2.5 rounded-full" :class="step.done ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900" x-text="step.label"></p>
                                    <p class="text-xs text-slate-500" x-text="step.at || 'Pending'"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="text-sm font-black text-slate-900">Admin actions</h4>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach(['accepted' => 'Accept', 'rejected' => 'Reject', 'contacted' => 'Mark Contacted', 'reviewed' => 'Mark Reviewed'] as $status => $label)
                            <form method="POST" :action="selectedApplication ? '{{ url('/admin/applications') }}/' + selectedApplication.id + '/status' : '#'}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{{ $label }}</button>
                            </form>
                        @endforeach
                    </div>
                    <form method="POST" :action="selectedApplication ? '{{ url('/admin/applications') }}/' + selectedApplication.id + '/status' : '#'" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" :value="selectedApplication?.status || 'pending'">
                        <textarea name="admin_notes" rows="3" placeholder="Add admin notes..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100" x-text="selectedApplication?.admin_notes || ''"></textarea>
                        <button class="mt-2 rounded-xl bg-[#8B0000] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#750000]">Save Notes</button>
                    </form>
                </section>
            </div>
        </aside>
    </div>

    <div x-show="filterDrawer" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-slate-900/45" @click="filterDrawer = false"></div>
        <aside class="absolute bottom-0 left-0 right-0 rounded-t-3xl bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-black text-slate-900">Filters</h3>
                <button type="button" @click="filterDrawer = false" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">Close</button>
            </div>
            <form method="GET" action="{{ route('admin.applications.index') }}" class="space-y-3">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All Status</option>
                    @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'contacted' => 'Contacted', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected((string) request('department_id') === (string) $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select name="gender" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All</option>
                    <option value="male" @selected(request('gender') === 'male')>Male</option>
                    <option value="female" @selected(request('gender') === 'female')>Female</option>
                    <option value="other" @selected(request('gender') === 'other')>Other</option>
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                </div>
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button type="submit" class="rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white">Search</button>
                    <a href="{{ route('admin.applications.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600">Reset</a>
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection
