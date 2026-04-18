@extends('layouts.app')

@section('title', 'Edit Exam')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:px-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(249,115,22,0.16),_transparent_38%),radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.12),_transparent_30%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">
                    <i class="fas fa-pen-to-square"></i>
                    Update workflow
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Edit exam configuration</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Adjust the exam header, schedule window, program assignments, and workflow settings without breaking published results.
                    </p>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Edit safely</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">Preserve marks history</div>
                </div>
                <div class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-sky-600">Session aware</div>
                    <div class="mt-2 text-sm font-semibold text-sky-900">Keep result linkage</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-600">Publish ready</div>
                    <div class="mt-2 text-sm font-semibold text-emerald-900">Workflow state intact</div>
                </div>
            </div>
        </div>
    </section>

    @include('admin.exams._form', [
        'exam' => $exam,
        'action' => route('admin.exams.update', $exam),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection