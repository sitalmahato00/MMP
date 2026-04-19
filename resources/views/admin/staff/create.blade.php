@extends('layouts.app')
@section('title', 'Add Staff')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:flex-row lg:items-end lg:justify-between lg:p-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Administrative Staff</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Add staff member</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Create a profile that can later be published on the staff directory with selective contact visibility.</p>
        </div>
        <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Back to Staff</a>
    </div>

    @include('admin.staff._form', [
        'action' => route('admin.staff.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Staff Profile',
        'backUrl' => route('admin.staff.index'),
        'staff' => null,
    ])
</div>
@endsection
