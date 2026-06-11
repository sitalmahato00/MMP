@extends('layouts.app')
@section('title', 'Edit Staff')

@section('content')
<x-form-layout title="Edit Staff" subtitle="Update identity, employment, and public visibility settings for {{ $staff->name }}." back="{{ route('admin.staff.show', $staff) }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.staff.index') }}" class="hover:text-slate-900">Staff</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Staff</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    @include('admin.staff._form', [
        'action' => route('admin.staff.update', $staff),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
        'backUrl' => route('admin.staff.show', $staff),
        'staff' => $staff,
    ])
</x-form-layout>
@endsection
