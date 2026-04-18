@extends('layouts.app')
@section('title', 'Add Program')

@section('content')
<div x-data="{
    step: 1,
    totalSteps: 3,
    name: '{{ old('name') }}',
    code: '{{ old('code') }}',
    next() { if (this.step < this.totalSteps) this.step++; },
    prev() { if (this.step > 1) this.step--; }
}" class="max-w-3xl space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Add Program</h1>
            <p class="mt-0.5 text-sm text-slate-500">Create a new academic program under a department.</p>
        </div>
        <a href="{{ route('admin.programs.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
            ← Back
        </a>
    </div>

    {{-- Stepper progress --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-0">
            @php $steps = [['n'=>1,'l'=>'Program Details'],['n'=>2,'l'=>'Curriculum & Info'],['n'=>3,'l'=>'Syllabus & Publish']]; @endphp
            @foreach($steps as $i => $s)
            <div class="flex flex-1 items-center {{ $i < count($steps)-1 ? '' : '' }}">
                <div class="flex flex-col items-center gap-1.5 relative z-10">
                    <div :class="step >= {{ $s['n'] }} ? 'bg-[#8B0000] text-white shadow-lg shadow-[#8B0000]/30' : 'bg-slate-100 text-slate-400'"
                         class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-black transition-all duration-300">
                        <template x-if="step > {{ $s['n'] }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="step <= {{ $s['n'] }}">
                            <span>{{ $s['n'] }}</span>
                        </template>
                    </div>
                    <span :class="step >= {{ $s['n'] }} ? 'text-[#8B0000]' : 'text-slate-400'"
                          class="text-[11px] font-bold whitespace-nowrap">{{ $s['l'] }}</span>
                </div>
                @if($i < count($steps)-1)
                <div class="flex-1 mx-2 -mt-4">
                    <div class="h-0.5 rounded-full bg-slate-100 relative overflow-hidden">
                        <div :class="step > {{ $s['n'] }} ? 'w-full' : 'w-0'" class="absolute inset-0 bg-[#8B0000] transition-all duration-500"></div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.programs.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- ── STEP 1: Program Details ── --}}
        <div x-show="step === 1" x-cloak class="space-y-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-base font-black text-slate-900 mb-5 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#8B0000] text-xs font-black text-white">1</span>
                    Program Details
                </h3>

                {{-- Validation errors --}}
                @if($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-100 p-4">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $err)
                        <li class="text-xs text-red-700 font-medium">• {{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Name --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" value="{{ old('name') }}" required
                               placeholder="e.g. Diploma in Information Technology"
                               class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                    </div>

                    {{-- Code --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" x-model="code" value="{{ old('code') }}" required
                               placeholder="e.g. DIT" maxlength="20"
                               class="w-full rounded-xl border {{ $errors->has('code') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-2.5 text-sm font-mono text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 uppercase"/>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" required
                                class="w-full rounded-xl border {{ $errors->has('department_id') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                            <option value="">— Select Department —</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Duration --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Duration (Years) <span class="text-red-500">*</span></label>
                        <select name="duration_years" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                            @foreach([1,2,3,4] as $yr)
                            <option value="{{ $yr }}" {{ old('duration_years', 3) == $yr ? 'selected' : '' }}>{{ $yr }} {{ $yr > 1 ? 'Years' : 'Year' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Semesters --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Total Semesters <span class="text-red-500">*</span></label>
                        <select name="total_semesters" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                            @foreach([2,3,4,5,6,7,8] as $sem)
                            <option value="{{ $sem }}" {{ old('total_semesters', 6) == $sem ? 'selected' : '' }}>{{ $sem }} Semesters</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-bold text-slate-700">Active Program</p>
                                <p class="text-xs text-slate-400">Enable enrollment and display in student portal</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center gap-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                       class="peer sr-only">
                                <div class="peer relative h-7 w-12 rounded-full bg-slate-200 transition-colors peer-checked:bg-[#8B0000] after:absolute after:top-1 after:left-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" @click="next()"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition shadow-sm">
                    Next: Curriculum →
                </button>
            </div>
        </div>

        {{-- ── STEP 2: Curriculum & Info ── --}}
        <div x-show="step === 2" x-cloak class="space-y-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-base font-black text-slate-900 mb-5 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#8B0000] text-xs font-black text-white">2</span>
                    Curriculum & Program Info
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Coordinator --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Coordinator <span class="text-slate-400 font-normal">(optional)</span></label>
                        <select name="coordinator_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                            <option value="">— None —</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('coordinator_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user?->name }} {{ $teacher->designation ? '('.$teacher->designation.')' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Affiliation Type --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Affiliation Type</label>
                        <select name="affiliation_type"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                            <option value="CTEVT" {{ old('affiliation_type', 'CTEVT') === 'CTEVT' ? 'selected' : '' }}>CTEVT</option>
                            <option value="TU" {{ old('affiliation_type') === 'TU' ? 'selected' : '' }}>Tribhuvan University (TU)</option>
                            <option value="PU" {{ old('affiliation_type') === 'PU' ? 'selected' : '' }}>Pokhara University (PU)</option>
                            <option value="KU" {{ old('affiliation_type') === 'KU' ? 'selected' : '' }}>Kathmandu University (KU)</option>
                            <option value="Other" {{ old('affiliation_type') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    {{-- CTEVT Code --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">CTEVT / Affiliation Code <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="text" name="ctevt_code" value="{{ old('ctevt_code') }}" maxlength="50"
                               placeholder="e.g. CTEVT-2022-DIT"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Description</label>
                        <textarea name="description" rows="4" placeholder="Provide a brief overview of this program, its objectives, and career outcomes…"
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 resize-none">{{ old('description') }}</textarea>
                    </div>

                    {{-- Eligibility --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Eligibility Criteria <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="eligibility" rows="3" placeholder="e.g. SEE passed or equivalent with minimum GPA 1.6…"
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 resize-none">{{ old('eligibility') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="prev()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    ← Back
                </button>
                <button type="button" @click="next()"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition shadow-sm">
                    Next: Syllabus →
                </button>
            </div>
        </div>

        {{-- ── STEP 3: Syllabus & Publish ── --}}
        <div x-show="step === 3" x-cloak class="space-y-5">
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-base font-black text-slate-900 mb-5 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#8B0000] text-xs font-black text-white">3</span>
                    Syllabus & Publish
                </h3>

                {{-- Syllabus upload --}}
                <div x-data="{ fileName: '' }" class="mb-6">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Syllabus / Curriculum (PDF) <span class="text-slate-400 font-normal">(optional)</span></label>
                    <label class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-8 cursor-pointer hover:border-[#8B0000]/40 hover:bg-[#8B0000]/5 transition group"
                           :class="fileName ? 'border-[#8B0000]/30 bg-[#8B0000]/5' : ''">
                        <div x-show="!fileName">
                            <div class="flex h-14 w-14 mx-auto items-center justify-center rounded-2xl bg-red-50 mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 text-center">Drop PDF here or click to upload</p>
                            <p class="mt-1 text-xs text-slate-400 text-center">Maximum 10MB</p>
                        </div>
                        <div x-show="fileName" x-cloak class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800" x-text="fileName"></p>
                                <p class="text-xs text-slate-400">PDF ready to upload</p>
                            </div>
                        </div>
                        <input type="file" name="syllabus" accept=".pdf"
                               @change="fileName = $event.target.files[0]?.name ?? ''"
                               class="sr-only"/>
                    </label>
                </div>

                {{-- Summary --}}
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Review Before Submitting</p>
                    <dl class="grid grid-cols-2 gap-y-2 gap-x-6 text-sm">
                        <div>
                            <dt class="text-xs text-slate-500">Program Name</dt>
                            <dd class="font-bold text-slate-900 truncate" x-text="name || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500">Code</dt>
                            <dd class="font-bold font-mono text-slate-900 uppercase" x-text="code || '—'"></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="prev()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    ← Back
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-6 py-3 text-sm font-bold text-white hover:bg-[#7a0000] transition shadow-lg shadow-[#8B0000]/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Create Program
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

<form method="POST" action="{{ route('admin.programs.store') }}" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Program Details">
        <x-form-row>
            <x-form-field label="Program Name" name="name" :required="true" span="full">
                <x-input name="name" :required="true" placeholder="e.g. Bachelor of Computer Application"/>
            </x-form-field>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    <option value="">— Select Department —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Duration (Years)" name="duration_years" :required="true">
                <x-input name="duration_years" type="number" :required="true" placeholder="e.g. 4" class="[appearance:textfield]"/>
            </x-form-field>
            <x-form-field label="Total Semesters" name="total_semesters" :required="true">
                <x-input name="total_semesters" type="number" :required="true" placeholder="e.g. 8" class="[appearance:textfield]"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Create Program</x-btn>
        <x-btn href="{{ route('admin.programs.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
