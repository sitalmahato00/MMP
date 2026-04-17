@extends('layouts.app')
@section('title', $user->name)

@section('content')
<x-page-header :title="$user->name" subtitle="Account detail and activity overview."
               back="{{ route('admin.users.index') }}">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.users.edit', $user) }}" variant="secondary" size="sm">Edit</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center">
            <x-avatar :src="$user->avatar_url" :name="$user->name" size="xl" class="mb-3"/>
            <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="text-sm text-gray-400">{{ $user->email }}</p>
            <div class="mt-3 flex flex-wrap gap-2 justify-center">
                @foreach($user->roles as $role)
                    @php $roleColor = ['principal'=>'red','hod'=>'blue','teacher'=>'green','student'=>'purple','parent'=>'yellow','alumni'=>'gray'][$role->name] ?? 'gray'; @endphp
                    <x-badge :color="$roleColor">{{ $role->name }}</x-badge>
                @endforeach
            </div>
            <div class="mt-3">
                <x-badge :color="$user->is_active ? 'green' : 'red'" :dot="true">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </x-badge>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="lg:col-span-2 space-y-4">
        <x-card>
            <x-section-header title="Account Information"/>
            <dl class="divide-y divide-gray-50">
                <x-info-row label="User ID">{{ $user->id }}</x-info-row>
                <x-info-row label="Full Name">{{ $user->name }}</x-info-row>
                <x-info-row label="Email">{{ $user->email }}</x-info-row>
                <x-info-row label="Phone">{{ $user->phone ?? '—' }}</x-info-row>
                <x-info-row label="Gender">{{ ucfirst($user->gender) ?? '—' }}</x-info-row>
                <x-info-row label="Date of Birth">{{ $user->dob ? bsDate($user->dob, 'd F Y') : '—' }}</x-info-row>
                <x-info-row label="Address">{{ $user->address ?? '—' }}</x-info-row>
                <x-info-row label="Status">
                    <x-badge :color="$user->is_active ? 'green' : 'red'" :dot="true">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </x-badge>
                </x-info-row>
                <x-info-row label="Member Since">{{ bsDate($user->created_at, 'd F Y') }}</x-info-row>
            </dl>
        </x-card>
    </div>
</div>
@endsection
