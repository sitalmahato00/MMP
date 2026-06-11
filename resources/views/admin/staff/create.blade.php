@extends('layouts.app')
@section('title', 'Add Staff')

@section('content')
<x-form-layout title="Add Staff" subtitle="Create a profile that can later be published on the staff directory with selective contact visibility." back="{{ route('admin.staff.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.staff.index') }}" class="hover:text-slate-900">Staff</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add Staff</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    @include('admin.staff._form', [
        'action' => route('admin.staff.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Staff Profile',
        'backUrl' => route('admin.staff.index'),
        'staff' => null,
    ])
</x-form-layout>
@endsection
