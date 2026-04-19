@extends('layouts.app')
@section('title', 'Edit Staff')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:flex-row lg:items-end lg:justify-between lg:p-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Administrative Staff</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Edit staff profile</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">Update identity, employment, and public visibility settings for {{ $staff->name }}.</p>
        </div>
        <a href="{{ route('admin.staff.show', $staff) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Back to Profile</a>
    </div>

    @include('admin.staff._form', [
        'action' => route('admin.staff.update', $staff),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
        'backUrl' => route('admin.staff.show', $staff),
        'staff' => $staff,
    ])
</div>
@endsection
