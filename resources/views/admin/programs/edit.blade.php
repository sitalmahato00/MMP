@extends('layouts.app')
@section('title', 'Edit Program — ' . $program->name)

@section('content')
@php
    $gradients = ['from-[#8B0000] to-rose-700','from-violet-600 to-purple-700','from-blue-600 to-indigo-700','from-emerald-600 to-teal-700','from-amber-500 to-orange-600','from-cyan-600 to-sky-700'];
    $icons = ['📘','🎓','🔬','💻','🏛️','🧪','📐','🌐','⚙️','🎨'];
    $grad = $gradients[$program->id % count($gradients)];
    $icon = $icons[$program->id % count($icons)];
@endphp

<div class="max-w-3xl space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Edit Program</h1>
            <p class="mt-0.5 text-sm text-slate-500">{{ $program->name }} &bull; {{ $program->code }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.programs.show', $program) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                ← Back to Profile
            </a>
        </div>
    </div>

    {{-- Program mini-hero --}}
    <div class="flex items-center gap-4 rounded-2xl bg-gradient-to-br {{ $grad }} px-6 py-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 text-3xl">{{ $icon }}</div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-white truncate">{{ $program->name }}</p>
            <p class="text-sm text-white/70">{{ $program->department?->name }}</p>
        </div>
        <span class="rounded-full {{ $program->is_active ? 'bg-emerald-400/30 text-white' : 'bg-white/15 text-white/60' }} px-3 py-1 text-xs font-bold">
            {{ $program->is_active ? '● Active' : '● Inactive' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.programs.update', $program) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-100 p-4">
            <ul class="space-y-1">
                @foreach($errors->all() as $err)
                <li class="text-xs text-red-700 font-medium">• {{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── SECTION 1: Core Details ── --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black text-slate-900 mb-5">Core Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $program->name) }}" required
                           class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $program->code) }}" required maxlength="20"
                           class="w-full rounded-xl border {{ $errors->has('code') ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-4 py-2.5 text-sm font-mono uppercase text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Department <span class="text-red-500">*</span></label>
                    <select name="department_id" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $program->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Duration (Years) <span class="text-red-500">*</span></label>
                    <select name="duration_years" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        @foreach([1,2,3,4] as $yr)
                        <option value="{{ $yr }}" {{ old('duration_years', $program->duration_years) == $yr ? 'selected' : '' }}>{{ $yr }} {{ $yr > 1 ? 'Years' : 'Year' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Total Semesters <span class="text-red-500">*</span></label>
                    <select name="total_semesters" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        @foreach([2,3,4,5,6,7,8] as $sem)
                        <option value="{{ $sem }}" {{ old('total_semesters', $program->total_semesters) == $sem ? 'selected' : '' }}>{{ $sem }} Semesters</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-bold text-slate-700">Active Program</p>
                            <p class="text-xs text-slate-400">Enable enrollment in student portal</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $program->is_active) ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="peer h-7 w-12 rounded-full bg-slate-200 transition-colors peer-checked:bg-[#8B0000] after:absolute after:top-1 after:left-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform peer-checked:after:translate-x-5 relative"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: Curriculum Info ── --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black text-slate-900 mb-5">Curriculum & Affiliation</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Coordinator</label>
                    <select name="coordinator_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="">— None —</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('coordinator_id', $program->coordinator_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->user?->name }} {{ $teacher->designation ? '('.$teacher->designation.')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Affiliation Type</label>
                    <select name="affiliation_type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        @foreach(['CTEVT','TU','PU','KU','Other'] as $aff)
                        <option value="{{ $aff }}" {{ old('affiliation_type', $program->affiliation_type) === $aff ? 'selected' : '' }}>{{ $aff }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">CTEVT / Affiliation Code</label>
                    <input type="text" name="ctevt_code" value="{{ old('ctevt_code', $program->ctevt_code) }}" maxlength="50"
                           placeholder="e.g. CTEVT-2022-DIT"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"/>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Program Description</label>
                    <textarea name="description" rows="4" placeholder="Brief overview of this program…"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 resize-none">{{ old('description', $program->description) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Eligibility Criteria</label>
                    <textarea name="eligibility" rows="3" placeholder="e.g. SEE passed with minimum GPA 1.6…"
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 resize-none">{{ old('eligibility', $program->eligibility) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── SECTION 3: Syllabus ── --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-black text-slate-900 mb-5">Syllabus / Curriculum PDF</h3>

            @if($program->syllabus)
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ basename($program->syllabus) }}</p>
                    <p class="text-xs text-slate-500">Current syllabus</p>
                </div>
                <a href="{{ asset('storage/'.$program->syllabus) }}" target="_blank"
                   class="shrink-0 rounded-lg bg-[#8B0000]/10 px-3 py-1.5 text-xs font-bold text-[#8B0000] hover:bg-[#8B0000]/20 transition">View</a>
            </div>
            @endif

            <div x-data="{ fileName: '' }">
                <label class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-6 cursor-pointer hover:border-[#8B0000]/40 hover:bg-[#8B0000]/5 transition"
                       :class="fileName ? 'border-[#8B0000]/30 bg-[#8B0000]/5' : ''">
                    <div x-show="!fileName" class="text-center">
                        <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <p class="text-sm font-bold text-slate-600">{{ $program->syllabus ? 'Replace with new PDF' : 'Upload PDF' }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">Max 10MB</p>
                    </div>
                    <div x-show="fileName" x-cloak class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span class="text-sm font-bold text-slate-800" x-text="fileName"></span>
                    </div>
                    <input type="file" name="syllabus" accept=".pdf"
                           @change="fileName = $event.target.files[0]?.name ?? ''"
                           class="sr-only"/>
                </label>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('admin.programs.show', $program) }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition shadow-lg shadow-[#8B0000]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

<form method="POST" action="{{ route('admin.programs.update', $program) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Program Details">
        <x-form-row>
            <x-form-field label="Program Name" name="name" :required="true" span="full">
                <x-input name="name" :value="$program->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $program->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Duration (Years)" name="duration_years" :required="true">
                <x-input name="duration_years" type="number" :value="$program->duration_years" :required="true" class="[appearance:textfield]"/>
            </x-form-field>
            <x-form-field label="Total Semesters" name="total_semesters" :required="true">
                <x-input name="total_semesters" type="number" :value="$program->total_semesters" :required="true" class="[appearance:textfield]"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.programs.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
