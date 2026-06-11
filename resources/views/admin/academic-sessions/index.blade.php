@extends('layouts.app')
@section('title', 'Academic Sessions')

@section('content')
@php
    $semesterFormErrorKeys = ['semester_number', 'start_date', 'end_date', 'status', 'delay_reason', 'notes', 'is_active'];
    $advanceFormErrorKeys = ['confirm_advance', 'selected_semesters'];
@endphp

<div class="space-y-6"
    x-data='{ 
        openCreateSemester: {{ $errors->hasAny($semesterFormErrorKeys) ? 'true' : 'false' }},
        openEditSemester: null,
        showEndModal: {{ $errors->has('confirm_end') ? 'true' : 'false' }},
        endPreview: null,
        endLoading: false,
        showAdvanceModal: {{ ($errors->hasAny($advanceFormErrorKeys) || session("open_advance_modal")) ? 'true' : 'false' }},
        advancePreview: null,
        advanceLoading: false,
        advanceSubmitting: false,
        advanceError: null,
        confirmAdvance: false,
        selectedAdvanceSemesters: [],
        semesterDurationMonths: 6,
        init() {
            if (this.showEndModal) {
                this.loadEndPreview();
            }

            if (this.showAdvanceModal) {
                this.loadAdvancePreview();
            }
        },
        formatBsDate(date) {
            return `${date.year}-${String(date.month + 1).padStart(2, "0")}-${String(date.date).padStart(2, "0")}`;
        },
        setDefaultAdvanceSelection() {
            if (!Array.isArray(this.advancePreview?.running_semesters)) {
                this.selectedAdvanceSemesters = [];
                return;
            }

            this.selectedAdvanceSemesters = this.advancePreview.running_semesters.map((semester) => String(semester.number));
        },
        selectAllAdvanceSemesters(checked) {
            if (!Array.isArray(this.advancePreview?.running_semesters)) {
                this.selectedAdvanceSemesters = [];
                return;
            }

            this.selectedAdvanceSemesters = checked
                ? this.advancePreview.running_semesters.map((semester) => String(semester.number))
                : [];
        },
        syncSemesterDuration(event) {
            const form = event?.target?.closest("form");
            if (!form || typeof NepaliDate === "undefined") {
                return;
            }

            const startInput = form.querySelector("input[name=\"start_date\"]");
            const endInput = form.querySelector("input[name=\"end_date\"]");

            if (!startInput || !endInput) {
                return;
            }

            const startValue = (startInput.value || "").trim();

            if (!startValue) {
                return;
            }

            try {
                const semesterEnd = new NepaliDate(startValue);
                semesterEnd.setMonth(semesterEnd.getMonth() + this.semesterDurationMonths);

                endInput.value = this.formatBsDate(semesterEnd.getBS());
                endInput.dispatchEvent(new Event("input", { bubbles: true }));
                endInput.dispatchEvent(new Event("change", { bubbles: true }));
            } catch (error) {
                // Ignore invalid partial input until the date becomes valid.
            }
        },
        loadEndPreview() {
            this.endLoading = true;
            this.endPreview = null;
            fetch("{{ $selectedSession ? route('admin.academic-sessions.preview-end', $selectedSession) : '#' }}", {
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
            })
            .then(r => r.json())
            .then(data => { this.endPreview = data; this.endLoading = false; })
            .catch(() => { this.endLoading = false; });
        },
        loadAdvancePreview() {
            this.advanceLoading = true;
            this.advancePreview = null;
            this.confirmAdvance = false;
            this.advanceError = null;
            fetch("{{ $selectedSession ? route('admin.academic-sessions.preview-advance', $selectedSession) : '#' }}", {
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
            })
            .then(r => r.json())
            .then(data => { this.advancePreview = data; this.setDefaultAdvanceSelection(); this.advanceLoading = false; })
            .catch(() => { this.advanceLoading = false; });
        },
        async submitAdvance() {
            if (this.selectedAdvanceSemesters.length === 0 || !this.confirmAdvance || this.advanceSubmitting) return;
            this.advanceSubmitting = true;
            this.advanceError = null;
            const body = new FormData();
            const tokenMeta = document.querySelector("meta[name=\"csrf-token\"]");
            body.append("_token", tokenMeta ? tokenMeta.content : "");
            body.append("confirm_advance", "1");
            this.selectedAdvanceSemesters.forEach(n => body.append("selected_semesters[]", n));
            try {
                const response = await fetch("{{ $selectedSession ? route('admin.academic-sessions.advance', $selectedSession) : '#' }}", {
                    method: "POST",
                    headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
                    body,
                });
                const data = await response.json();
                if (response.ok && data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    this.advanceError = data.error;
                    this.advanceSubmitting = false;
                } else {
                    this.advanceError = "Unexpected response. Please try again.";
                    this.advanceSubmitting = false;
                }
            } catch (err) {
                this.advanceError = "Network error: " + err.message;
                this.advanceSubmitting = false;
            }
        }
    }'
    x-init="init()">

    <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session Controls</p>
                <h2 class="mt-1 text-base font-black text-slate-950">Filter sessions or start a new one</h2>
            </div>
            <a href="{{ route('admin.academic-sessions.create') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-red-200 hover:text-[#8B0000]">New Session</a>
        </div>

        <form method="GET" action="{{ route('admin.academic-sessions.index') }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session Type</label>
                <select name="session_scope" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    <option value="all" @selected($sessionScope === 'all')>All Sessions</option>
                    <option value="running" @selected($sessionScope === 'running')>Running / Upcoming</option>
                    <option value="archived" @selected($sessionScope === 'archived')>Archived</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session</label>
                <select name="session_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    @forelse($sessions as $session)
                        <option value="{{ $session->id }}" @selected($selectedSession && $selectedSession->id === $session->id)>
                            {{ $session->name }} ({{ ucfirst($session->status) }})
                        </option>
                    @empty
                        <option value="">No sessions found</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Semester</label>
                <select name="semester_filter" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    <option value="">All Semesters</option>
                    @foreach($semesterNumberOptions as $semesterNumber)
                        <option value="{{ $semesterNumber }}" @selected($semesterFilter === $semesterNumber)>Semester {{ $semesterNumber }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status</label>
                <select name="status_filter" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    <option value="all" @selected($statusFilter === 'all')>All Statuses</option>
                    @foreach($semesterStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Lifecycle</label>
                <div class="flex gap-2">
                    <select name="lifecycle_filter" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="all" @selected($lifecycleFilter === 'all')>All</option>
                        <option value="running" @selected($lifecycleFilter === 'running')>Active Only</option>
                        <option value="archived" @selected($lifecycleFilter === 'archived')>Archived Only</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#6e0000]">Apply</button>
                </div>
            </div>
        </form>
    </section>

    @if(!$selectedSession)
        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-8 text-center shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <h2 class="text-lg font-bold text-slate-900">No session selected</h2>
            <p class="mt-1 text-sm text-slate-500">Create a session first, then configure running semesters.</p>
        </section>
    @else
        @php
            $overviewBadgeClass = match ($overview['status']) {
                'active' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                'upcoming' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
                'ended' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
            };
        @endphp

        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session Overview</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">
                        {{ $selectedSession->name }}
                        @if($selectedSession->name_bs)
                            <span class="text-slate-500">/ {{ $selectedSession->name_bs }}</span>
                        @endif
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] {{ $overviewBadgeClass }}">{{ $overview['statusLabel'] }}</span>

                    @if(!$selectedSession->is_locked)
                        <a href="{{ route('admin.academic-sessions.edit', $selectedSession) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:text-[#8B0000]">Edit Session</a>
                    @endif

                    @if(!$selectedSession->is_active && !$selectedSession->is_locked)
                        <form method="POST" action="{{ route('admin.academic-sessions.set-current', $selectedSession) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:text-[#8B0000]">Set Active</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                     style="background: linear-gradient(135deg,#F97316,#FB923C);">
                    <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xl font-black leading-tight text-white">{{ number_format($overview['departments']) }}</p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Total Departments</p>
                        </div>
                    </div>
                </div>
                <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                     style="background: linear-gradient(135deg,#10B981,#22C55E);">
                    <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xl font-black leading-tight text-white">{{ number_format($overview['runningSemesters']) }}</p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Running Semesters</p>
                        </div>
                    </div>
                </div>
                <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                     style="background: linear-gradient(135deg,#4F46E5,#6366F1);">
                    <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xl font-black leading-tight text-white">{{ number_format($overview['students']) }}</p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Enrolled Students</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Semester Status Summary --}}
            @if($overview['semesterStatusCounts']['total'] > 0)
                <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                    <span class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Semester Status:</span>
                    @if($overview['semesterStatusCounts']['upcoming'] > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-700 ring-1 ring-sky-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                            {{ $overview['semesterStatusCounts']['upcoming'] }} Upcoming
                        </span>
                    @endif
                    @if($overview['semesterStatusCounts']['running'] > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            {{ $overview['semesterStatusCounts']['running'] }} Running
                        </span>
                    @endif
                    @if($overview['semesterStatusCounts']['delayed'] > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            {{ $overview['semesterStatusCounts']['delayed'] }} Delayed
                        </span>
                    @endif
                    @if($overview['semesterStatusCounts']['completed'] > 0)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                            {{ $overview['semesterStatusCounts']['completed'] }} Completed
                        </span>
                    @endif
                </div>
            @endif

            {{-- Advance / End Session Actions --}}
            @if($selectedSession->is_active && !$selectedSession->is_locked)
                @if($overview['semesterStatusCounts']['running'] > 0)
                    <div class="mt-4 flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50/50 px-4 py-3">
                        <div>
                            <p class="text-sm font-bold text-blue-900">Advance Semesters</p>
                            <p class="text-xs text-blue-700/70">Completes current semesters, promotes students, graduates final-semester students, and starts the next cycle.</p>
                        </div>
                        <button type="button" @click="showAdvanceModal = true; loadAdvancePreview()" class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-600 hover:text-white">Advance →</button>
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 px-4 py-3">
                    <div>
                        <p class="text-sm font-bold text-red-900">End This Session</p>
                        <p class="text-xs text-red-700/70">Promotes students to next semester, graduates final-semester students, and locks the session.</p>
                    </div>
                    <button type="button" @click="showEndModal = true; loadEndPreview()" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-xs font-bold text-red-700 transition hover:bg-red-600 hover:text-white">End Session</button>
                </div>
            @endif
        </section>

        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-950">Semesters</h3>
                    <p class="text-sm text-slate-500">Manage semester schedules, statuses, and timelines for this session.</p>
                </div>
                <button type="button" @click="openCreateSemester = true" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-red-200 hover:text-[#8B0000]">Add Semester</button>
            </div>

            @if(empty($semesterCards))
                <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No semester setup matches the current filters.</p>
            @else
                <div class="mt-4 flex gap-4 overflow-x-auto pb-1">
                    @foreach($semesterCards as $card)
                        @php
                            $statusBadgeClass = match ($card['status_tone']) {
                                'sky' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
                                'emerald' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                                'rose' => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
                                'amber' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
                                default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                            };

                            $progressClass = match ($card['status_tone']) {
                                'sky' => 'bg-sky-400',
                                'emerald' => 'bg-emerald-500',
                                'rose' => 'bg-rose-500',
                                'amber' => 'bg-amber-400',
                                default => 'bg-slate-400',
                            };
                        @endphp

                        <article class="min-w-[260px] rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-bold text-slate-900">{{ $card['title'] }}</h4>
                                    <p class="mt-1 text-xs text-slate-500">{{ $card['start_date_bs'] }} - {{ $card['end_date_bs'] }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusBadgeClass }}">{{ $card['status_label'] }}</span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Students</p>
                                    <p class="mt-1 font-bold text-slate-800">{{ number_format($card['students']) }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Lifecycle</p>
                                    <p class="mt-1 font-bold text-slate-800">{{ $card['is_active'] ? 'Running' : 'Archived' }}</p>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                                    <span>Progress</span>
                                    <span>{{ $card['progress'] }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $progressClass }}" style="width: {{ $card['progress'] }}%"></div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.academic-sessions.semesters.destroy', [$selectedSession, $card['id']]) }}" onsubmit="return confirm('Delete Semester {{ $card['title'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50">Delete</button>
                                </form>
                                <button type="button" @click="openEditSemester = {{ $card['id'] }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-red-200 hover:text-[#8B0000]">Edit</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── Session Timeline (Gantt) ──────────────────────── --}}
        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-lg font-black text-slate-950">Session Timeline</h3>
                    <p class="text-sm text-slate-500">Gantt-style overview of semester overlaps and delay phases.</p>
                </div>
                @if($timelineStart && $timelineEnd)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $timelineStart }} – {{ $timelineEnd }}</span>
                @endif
            </div>

            {{-- Legend --}}
            <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-bold">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-2.5 py-1 text-sky-700"><span class="h-2 w-2 rounded-full bg-sky-400"></span> Upcoming</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Running</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-amber-700"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Delayed</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-1 text-rose-700"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Critical</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-slate-600"><span class="h-2 w-2 rounded-full bg-slate-400"></span> Completed</span>
            </div>

            @if(empty($timelineRows))
                <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No timeline data for the selected filters.</p>
            @else
                <div class="mt-5 overflow-x-auto">
                    <div class="min-w-[600px]">
                        {{-- ── Horizontal Month Axis ────────────────── --}}
                        <div class="grid grid-cols-[90px_1fr] gap-0">
                            {{-- Corner label --}}
                            <div class="border-b border-r border-slate-200 px-2 py-2">
                                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">Semester</span>
                            </div>
                            {{-- Month header --}}
                            <div class="relative border-b border-slate-200" style="height: 44px;">
                                @php $prevYear = ''; @endphp
                                @foreach($timelineMonths as $i => $month)
                                    <div class="absolute top-0 bottom-0 border-l border-slate-200 flex flex-col justify-center"
                                         style="left: {{ $month['left'] }}%; width: {{ $month['width'] }}%;">
                                        @if($month['year'] !== $prevYear)
                                            <span class="px-1.5 text-[9px] font-bold uppercase tracking-wider text-slate-400 leading-none">{{ $month['year'] }}</span>
                                        @endif
                                        <span class="px-1.5 text-[11px] font-bold text-slate-700 truncate leading-tight">{{ $month['label'] }}</span>
                                    </div>
                                    @php $prevYear = $month['year']; @endphp
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Semester Rows ────────────────────────── --}}
                        @foreach($timelineRows as $row)
                            <div class="grid grid-cols-[90px_1fr] gap-0 group">
                                {{-- Row label --}}
                                <div class="flex items-center border-b border-r border-slate-100 px-2 py-0" style="height: 40px;">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full {{ $row['dotClass'] }}"></span>
                                        <span class="text-xs font-bold text-slate-700">Sem {{ $row['semester_number'] }}</span>
                                    </div>
                                </div>
                                {{-- Bar area --}}
                                <div class="relative border-b border-slate-100" style="height: 40px;">
                                    {{-- Month grid lines --}}
                                    @foreach($timelineMonths as $month)
                                        <div class="absolute top-0 bottom-0 border-l border-slate-100" style="left: {{ $month['left'] }}%;"></div>
                                    @endforeach

                                    {{-- Today marker --}}
                                    @if($timelineTodayPct !== null)
                                        <div class="absolute top-0 bottom-0 w-px bg-red-400/60 z-10" style="left: {{ $timelineTodayPct }}%;"></div>
                                    @endif

                                    {{-- Semester bar --}}
                                    <div class="absolute top-1.5 bottom-1.5 rounded-full {{ $row['barClass'] }} shadow-sm transition-all group-hover:brightness-110 cursor-default"
                                         style="left: {{ $row['left'] }}%; width: {{ $row['width'] }}%;"
                                         title="Sem {{ $row['semester_number'] }}: {{ $row['start_label'] }} – {{ $row['end_label'] }} ({{ $row['status_label'] }})">
                                        {{-- Start dot --}}
                                        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-3.5 rounded-full bg-white/40 border-2 border-white/70 shadow-sm"></div>
                                        {{-- End dot --}}
                                        <div class="absolute right-0 top-1/2 -translate-y-1/2 h-3.5 w-3.5 rounded-full bg-white/40 border-2 border-white/70 shadow-sm"></div>
                                        {{-- Label inside bar --}}
                                        <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold text-white/90 truncate px-4">
                                            {{ $row['status_label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- ── Today label row ──────────────────────── --}}
                        @if($timelineTodayPct !== null)
                            <div class="grid grid-cols-[90px_1fr] gap-0">
                                <div></div>
                                <div class="relative" style="height: 16px;">
                                    <div class="absolute text-[9px] font-bold text-red-500 -translate-x-1/2 whitespace-nowrap" style="left: {{ $timelineTodayPct }}%;">
                                        Today
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <h3 class="text-lg font-black text-slate-950">Department Impact</h3>
            <p class="mt-1 text-sm text-slate-500">Semester number is locked across all departments. Student counts and date spread can differ.</p>

            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-[0.08em] text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Current Semesters</th>
                            <th class="px-4 py-3">Date Window</th>
                            <th class="px-4 py-3">Students</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @forelse($departmentImpactRows as $row)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $row['department'] }} <span class="text-xs font-normal text-slate-500">({{ $row['code'] }})</span></td>
                                <td class="px-4 py-3">{{ $row['semester_label'] }}</td>
                                <td class="px-4 py-3">{{ $row['date_window'] }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($row['students']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">No department impact data yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($selectedSession)
        <div x-show="openCreateSemester" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40" @click="openCreateSemester = false"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-black text-slate-950">Add Semester</h3>
                        <p class="text-sm text-slate-500">Configure semester timeline and delay rules for {{ $selectedSession->name }}.</p>
                    </div>
                    <button type="button" @click="openCreateSemester = false" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50">✕</button>
                </div>

                @if($errors->any())
                    <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-xs font-bold text-red-800">Please fix these errors:</p>
                        <ul class="mt-1 space-y-0.5 text-xs text-red-700">
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.academic-sessions.semesters.store', $selectedSession) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Semester</label>
                            <select name="semester_number" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" required>
                                @foreach($semesterNumberOptions as $semesterNumber)
                                    <option value="{{ $semesterNumber }}">Semester {{ $semesterNumber }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Status</label>
                            <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" required>
                                @foreach($semesterStatusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Start Date (BS)</label>
                            <x-bs-date-picker name="start_date" :required="true" @change="syncSemesterDuration($event)"/>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Expected End Date (BS)</label>
                            <x-bs-date-picker name="end_date"/>
                            <p class="mt-1 text-[11px] text-slate-400">Defaults to 6 months after the start date.</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Delay Reason (if delayed)</label>
                        <select name="delay_reason" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                            <option value="">No delay reason</option>
                            @foreach($delayReasonOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" placeholder="Optional details"></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-red-200">
                        Activate Semester
                    </label>

                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                        <x-btn type="button" variant="secondary" @click="openCreateSemester = false">Cancel</x-btn>
                        <x-btn type="submit">Save Semester</x-btn>
                    </div>
                </form>
            </div>
        </div>

        @foreach($allSemesters as $semester)
            <div x-show="openEditSemester === {{ $semester->id }}" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40" @click="openEditSemester = null"></div>
                <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-black text-slate-950">Edit Semester {{ $semester->semester_number }}</h3>
                            <p class="text-sm text-slate-500">Update timeline, delay reason, and status.</p>
                        </div>
                        <button type="button" @click="openEditSemester = null" class="rounded-lg border border-slate-200 p-2 text-slate-500 hover:bg-slate-50">✕</button>
                    </div>

                    <form method="POST" action="{{ route('admin.academic-sessions.semesters.update', [$selectedSession, $semester]) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Semester</label>
                                <select name="semester_number" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" required>
                                    @foreach($semesterNumberOptions as $semesterNumber)
                                        <option value="{{ $semesterNumber }}" @selected((int) $semester->semester_number === (int) $semesterNumber)>Semester {{ $semesterNumber }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Status</label>
                                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" required>
                                    @foreach($semesterStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($semester->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Start Date (BS)</label>
                                <x-bs-date-picker name="start_date" :value="bsDate($semester->start_date)" :required="true" @change="syncSemesterDuration($event)"/>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Expected End Date (BS)</label>
                                <x-bs-date-picker name="end_date" :value="bsDate($semester->end_date)"/>
                                <p class="mt-1 text-[11px] text-slate-400">Defaults to 6 months after the start date.</p>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Delay Reason (if delayed)</label>
                            <select name="delay_reason" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                <option value="">No delay reason</option>
                                @foreach($delayReasonOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($semester->delay_reason === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Notes</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" placeholder="Optional details">{{ $semester->notes }}</textarea>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="is_active" value="1" @checked($semester->is_active) class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-red-200">
                            Activate Semester
                        </label>

                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                            <x-btn type="button" variant="secondary" @click="openCreateSemester = false">Cancel</x-btn>
                            <x-btn type="submit">Save Semester</x-btn>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    {{-- ── Advance Semesters Confirmation Modal ──────────── --}}
    @if($selectedSession?->is_active && !$selectedSession?->is_locked)
        <div x-show="showAdvanceModal" x-cloak class="fixed inset-0 z-50 flex items-start justify-center bg-black/40 px-4 py-6 backdrop-blur-sm" @keydown.escape.window="showAdvanceModal = false">
            <div @click.outside="showAdvanceModal = false" class="w-full max-w-lg max-h-[calc(100vh-3rem)] overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-black text-blue-900">Advance Semesters: {{ $selectedSession->name }}</h3>
                        <p class="mt-1 text-xs text-slate-500">Review the impact before advancing.</p>
                    </div>
                    <button @click="showAdvanceModal = false" class="rounded-lg border border-slate-200 p-2 text-slate-400 hover:text-slate-700">✕</button>
                </div>

                {{-- Loading --}}
                <template x-if="advanceLoading">
                    <div class="mt-4 flex items-center justify-center py-8">
                        <svg class="h-6 w-6 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span class="ml-2 text-sm text-slate-500">Loading preview...</span>
                    </div>
                </template>

                {{-- Preview Content --}}
                <template x-if="advancePreview && !advanceLoading">
                    <div class="mt-4 space-y-4">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Advance Selection</p>
                            <p class="mt-1 text-sm text-slate-700">Default all running semesters are selected. Uncheck any semester you do not want to advance.</p>
                            <p class="mt-2 text-xs text-slate-500">
                                <span class="font-semibold text-slate-700" x-text="selectedAdvanceSemesters.length"></span>
                                semester(s) selected.
                            </p>
                        </div>

                        <template x-if="advancePreview.needs_new_session && !advancePreview.target_session_exists">
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                No upcoming session was found. A new session will be created automatically when you advance:
                                <span class="font-semibold" x-text="advancePreview.target_session"></span>
                            </div>
                        </template>

                        <template x-if="advancePreview.running_semesters?.length">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-3 py-2.5">
                                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200"
                                            :checked="selectedAdvanceSemesters.length > 0 && selectedAdvanceSemesters.length === advancePreview.running_semesters.length"
                                            @change="selectAllAdvanceSemesters($event.target.checked)"
                                        >
                                        Select All
                                    </label>
                                    <span class="text-xs text-slate-500">Checked by default</span>
                                </div>

                                <template x-for="semester in advancePreview.running_semesters" :key="semester.id">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 bg-white px-3 py-3 transition hover:border-slate-200">
                                        <input
                                            type="checkbox"
                                            name="selected_semesters[]"
                                            x-model="selectedAdvanceSemesters"
                                            :value="String(semester.number)"
                                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200"
                                        >
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900" x-text="semester.label"></p>
                                                    <p class="text-xs text-slate-500">
                                                        <span x-text="semester.start_label"></span> → <span x-text="semester.end_label"></span>
                                                    </p>
                                                </div>
                                                <span
                                                    class="rounded-full px-2.5 py-1 text-[10px] font-bold"
                                                    :class="semester.status === 'running' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'"
                                                    x-text="semester.status_label"
                                                ></span>
                                            </div>

                                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                                    <span class="block font-semibold text-slate-500">Students</span>
                                                    <span class="font-bold text-slate-800" x-text="semester.student_count"></span>
                                                </div>
                                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                                    <span class="block font-semibold text-slate-500">Promote</span>
                                                    <span class="font-bold text-emerald-700" x-text="semester.promote_count"></span>
                                                </div>
                                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                                    <span class="block font-semibold text-slate-500">Graduate</span>
                                                    <span class="font-bold text-amber-700" x-text="semester.graduate_count"></span>
                                                </div>
                                            </div>

                                            <div class="mt-3 rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                                                Next semester starts from the previous end date <span class="font-semibold" x-text="semester.next_start_label"></span>
                                                and defaults to <span class="font-semibold" x-text="semester.next_status_label"></span>.
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="!advancePreview.running_semesters?.length">
                            <p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500">
                                No running semesters are available to advance.
                            </p>
                        </template>

                        <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                            <p class="text-xs font-bold text-red-800">Review before continuing.</p>
                            <ul class="mt-1 space-y-0.5 text-xs text-red-700">
                                <li>• Only checked semesters will be advanced</li>
                                <li>• New semesters start from the previous semester end date</li>
                                <li>• New semester status is set from its dates</li>
                                <li>• If the cycle needs a new session, it will be created automatically</li>
                            </ul>
                        </div>

                        <template x-if="advanceError">
                            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700" x-text="advanceError"></div>
                        </template>

                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" x-model="confirmAdvance" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200">
                            I confirm advancing the selected semesters
                        </label>
                        <div class="mt-4 flex items-center justify-end gap-2">
                            <button type="button" @click="showAdvanceModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                            <button
                                type="button"
                                @click="submitAdvance()"
                                class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="selectedAdvanceSemesters.length === 0 || !confirmAdvance || advanceSubmitting">
                                <span x-show="!advanceSubmitting">Advance Selected Semesters →</span>
                                <span x-show="advanceSubmitting">Advancing…</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    @endif

    {{-- ── End Session Confirmation Modal ─────────────────── --}}
    @if($selectedSession?->is_active && !$selectedSession?->is_locked)
        <div x-show="showEndModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @keydown.escape.window="showEndModal = false">
            <div @click.outside="showEndModal = false" class="mx-4 w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-black text-red-900">End Session: {{ $selectedSession->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">This action is irreversible. The session will be locked.</p>
                    </div>
                    <button @click="showEndModal = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Loading state --}}
                <div x-show="endLoading" class="mt-5 flex items-center justify-center py-8">
                    <svg class="h-6 w-6 animate-spin text-slate-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span class="ml-2 text-sm text-slate-500">Loading impact preview...</span>
                </div>

                {{-- Preview data --}}
                <div x-show="!endLoading && endPreview" class="mt-5 space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Impact Summary</p>
                        <div class="mt-3 grid grid-cols-3 gap-3">
                            <div class="text-center">
                                <p class="text-2xl font-black text-slate-900" x-text="endPreview?.total_students ?? 0"></p>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Students</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black text-emerald-700" x-text="endPreview?.to_promote ?? 0"></p>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">To Promote</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black text-sky-700" x-text="endPreview?.to_graduate ?? 0"></p>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-sky-600">To Graduate</p>
                            </div>
                        </div>
                    </div>

                    {{-- Per-semester breakdown --}}
                    <template x-if="endPreview?.by_semester && Object.keys(endPreview.by_semester).length > 0">
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500 mb-2">Breakdown by Semester</p>
                            <template x-for="(counts, label) in endPreview.by_semester" :key="label">
                                <div class="flex items-center justify-between border-b border-slate-100 py-1.5 last:border-0">
                                    <span class="text-sm font-semibold text-slate-700" x-text="label"></span>
                                    <div class="flex gap-3">
                                        <span x-show="counts.promote > 0" class="text-xs font-bold text-emerald-700">
                                            <span x-text="counts.promote"></span> promote
                                        </span>
                                        <span x-show="counts.graduate > 0" class="text-xs font-bold text-sky-700">
                                            <span x-text="counts.graduate"></span> graduate
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="endPreview?.running_semesters > 0">
                        <p class="text-xs text-amber-700">
                            <span class="font-bold" x-text="endPreview.running_semesters"></span> running semester(s) will be marked as completed.
                        </p>
                    </template>

                    {{-- Next session upcoming semesters --}}
                    <template x-if="endPreview?.next_semester_numbers?.length > 0">
                        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3">
                            <p class="text-xs font-bold text-sky-800">Next Session Semesters</p>
                            <p class="mt-1 text-xs text-sky-700">
                                Current: <span class="font-bold" x-text="endPreview.current_semester_numbers?.join(', ')"></span>
                                &rarr; Next: <span class="font-bold" x-text="endPreview.next_semester_numbers?.join(', ')"></span>
                                <span class="text-sky-600">(includes Sem 1 for new admissions)</span>
                            </p>
                            <template x-if="endPreview.next_session_name">
                                <p class="mt-1 text-xs text-sky-600">
                                    Will be auto-created as <span class="font-semibold">upcoming</span> in session: <span class="font-bold" x-text="endPreview.next_session_name"></span>
                                </p>
                            </template>
                            <template x-if="!endPreview.next_session_name">
                                <p class="mt-1 text-xs text-amber-600 font-semibold">
                                    No upcoming session found — create one first to auto-populate semesters.
                                </p>
                            </template>
                        </div>
                    </template>

                    <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                        <p class="text-xs font-bold text-red-800">Warning: This cannot be undone.</p>
                        <ul class="mt-1 space-y-0.5 text-xs text-red-700">
                            <li>• Student marks records are preserved as-is (linked by semester number)</li>
                            <li>• Non-final students advance to the next semester</li>
                            <li>• Final-semester students are graduated and moved to alumni</li>
                            <li>• All semesters are marked as completed</li>
                            <li>• Upcoming semesters are auto-created in the next session</li>
                            <li>• The session is locked permanently</li>
                        </ul>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('admin.academic-sessions.end', $selectedSession) }}" class="mt-5">
                    @csrf
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="confirm_end" value="1" class="h-4 w-4 rounded border-slate-300 text-red-700 focus:ring-red-200" x-ref="confirmEndCheckbox">
                        I understand this action is irreversible
                    </label>

                    <div class="mt-4 flex items-center justify-end gap-2">
                        <button type="button" @click="showEndModal = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-100">Cancel</button>
                        <button type="submit" :disabled="!$refs.confirmEndCheckbox?.checked" class="rounded-xl bg-red-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50">End Session</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
