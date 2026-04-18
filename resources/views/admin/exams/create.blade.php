@extends('layouts.app')

@section('title', 'Create Exam')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:px-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.16),_transparent_38%),radial-gradient(circle_at_bottom_left,_rgba(16,185,129,0.14),_transparent_30%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-sky-700">
                    <i class="fas fa-file-alt"></i>
                    New exam plan
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Create exam and result workflow</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Set the session, assign programs and semesters, define marks rules, and prepare the exam for scheduling and publishing.
                    </p>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Step 1</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">Core exam details</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-600">Step 2</div>
                    <div class="mt-2 text-sm font-semibold text-emerald-900">Programs and terms</div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-amber-600">Step 3</div>
                    <div class="mt-2 text-sm font-semibold text-amber-900">Publish pipeline</div>
                </div>
            </div>
        </div>
    </section>

    @include('admin.exams._form', [
        'exam' => null,
        'action' => route('admin.exams.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Exam',
    ])
</div>
@endsection