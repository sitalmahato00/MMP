@php
    $selectedPrograms = collect(old('program_ids', $selectedProgramIds ?? []))->map(fn ($value) => (int) $value)->all();
    $selectedSessionId = old('academic_session_id', $exam?->academic_session_id ?? $currentSession?->id);
    $selectedDepartmentId = old('department_id', $exam?->department_id ?? '');
    $selectedSemester = old('semester', $exam?->programs->first()?->pivot?->semester ?? 1);
    $selectedType = old('type', $exam?->type ?? 'regular');
    $selectedStatus = old('status', $exam?->status ?? 'upcoming');
    $selectedMarksOpen = old('marks_open', $exam?->marks_open ?? false);
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Exam Setup</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Core exam details</h2>
                        <p class="mt-1 text-sm text-slate-500">Create the exam header first, then assign subjects and manage schedules in the detail page.</p>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">BS dates enabled</span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Exam Name</span>
                        <input name="name" value="{{ old('name', $exam?->name) }}" placeholder="e.g. First Internal Exam"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                        @error('name')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Exam Type</span>
                        <select name="type" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @foreach($typeOptions as $key => $label)
                                <option value="{{ $key }}" @selected($selectedType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Academic Session / Year</span>
                        <select name="academic_session_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            <option value="">Select session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" @selected((string) $selectedSessionId === (string) $session->id)>
                                    {{ $session->name }}{{ $session->name_bs ? ' · ' . $session->name_bs : '' }}{{ $session->id === $currentSession?->id ? ' · Current' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_session_id')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</span>
                        <select name="department_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            <option value="">Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>
                                    {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Semester</span>
                        <select name="semester" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @foreach($semesterOptions as $semester)
                                <option value="{{ $semester }}" @selected((string) $selectedSemester === (string) $semester)>Semester {{ $semester }}</option>
                            @endforeach
                        </select>
                        @error('semester')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2 md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Programs Included</span>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($programs as $program)
                                @php
                                    $checked = in_array($program->id, $selectedPrograms, true);
                                @endphp
                                <label class="group flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition {{ $checked ? 'border-[#8B0000] bg-rose-50/70 shadow-sm' : 'border-slate-200 bg-slate-50/70 hover:border-slate-300 hover:bg-white' }}">
                                    <input type="checkbox" name="program_ids[]" value="{{ $program->id }}" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-rose-200" @checked($checked)>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900">{{ $program->name }}</p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ $program->code ?? 'No code' }} · {{ $program->department?->code ?? $program->department?->name ?? 'No department' }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('program_ids')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Start Date (BS)</span>
                        <x-bs-date-picker name="start_date" :value="old('start_date', $exam?->start_date ? bsDate($exam->start_date) : '')" placeholder="YYYY-MM-DD"/>
                        @error('start_date')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">End Date (BS)</span>
                        <x-bs-date-picker name="end_date" :value="old('end_date', $exam?->end_date ? bsDate($exam->end_date) : '')" placeholder="YYYY-MM-DD"/>
                        @error('end_date')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Lifecycle Status</span>
                        <select name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="md:col-span-2 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <input type="checkbox" name="marks_open" value="1" @checked($selectedMarksOpen) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-rose-200">
                        <div>
                            <p class="font-semibold text-slate-900">Open mark entry immediately</p>
                            <p class="mt-0.5 text-sm text-slate-500">Teachers can start submitting marks as soon as the schedules are finalized.</p>
                        </div>
                    </label>
                </div>
            </article>
        </div>

        <aside class="space-y-6">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Workflow</p>
                <h3 class="mt-2 text-xl font-black text-slate-950">How this exam moves</h3>
                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-900">1. Create exam header</p>
                        <p class="mt-1 text-sm text-slate-500">Name the exam, attach the academic session, and select the semester matrix.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-900">2. Assign programs</p>
                        <p class="mt-1 text-sm text-slate-500">Choose one or more programs so subjects and result sheets can be generated.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-sm font-bold text-slate-900">3. Enter, verify, publish</p>
                        <p class="mt-1 text-sm text-slate-500">Teachers enter marks, HOD verifies, and the principal publishes the result sheet.</p>
                    </div>
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-gradient-to-br from-[#8B0000] to-rose-700 p-6 text-white shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-white/70">Pro tip</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight">Keep schedules tight, publish late only after verification.</h3>
                <p class="mt-2 text-sm leading-6 text-white/75">The UI is built for the CTEVT flow where multiple semesters can move in parallel without losing result visibility.</p>
            </article>
        </aside>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#8B0000] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
            Cancel
        </a>
    </div>
</form>
