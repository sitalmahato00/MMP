@extends('layouts.app')
@section('title', 'Application - ' . $application->full_name)

@section('content')
<x-page-header :title="$application->full_name" subtitle="Application submitted {{ bsDate($application->created_at, 'Y, F d') }} · Updated {{ bsDate($application->updated_at, 'Y, F d') }}">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.applications.index') }}" variant="secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to List
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Personal Information --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Personal Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Full Name</dt>
                        <dd class="text-sm text-gray-900 mt-0.5 font-medium">{{ $application->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Email</dt>
                        <dd class="text-sm mt-0.5"><a href="mailto:{{ $application->email }}" class="text-[#8B0000] hover:underline">{{ $application->email }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Phone</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Date of Birth</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->dob ? bsDate($application->dob) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Gender</dt>
                        <dd class="text-sm text-gray-900 mt-0.5 capitalize">{{ $application->gender ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Address</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->address ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Guardian Information --}}
        @if($application->guardian_name || $application->guardian_phone)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Guardian Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Guardian Name</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->guardian_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Guardian Phone</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->guardian_phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif

        {{-- Academic Information --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Academic Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">Preferred Department</dt>
                        <dd class="text-sm text-gray-900 mt-0.5 font-medium">{{ $application->department?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-gray-400 uppercase">SEE GPA</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->gpa ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold text-gray-400 uppercase">Previous School</dt>
                        <dd class="text-sm text-gray-900 mt-0.5">{{ $application->previous_school ?? '—' }}</dd>
                    </div>
                </dl>
                @if($application->message)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-xs font-bold text-gray-400 uppercase mb-1">Message</dt>
                        <dd class="text-sm text-gray-700 leading-relaxed">{{ $application->message }}</dd>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar: Status & Notes --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Status & Notes</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.applications.update-status', $application) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                        <select name="status" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000]">
                            @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'contacted' => 'Contacted', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)
                                <option value="{{ $value }}" {{ $application->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Admin Notes</label>
                        <textarea name="admin_notes" rows="4" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000]" placeholder="Internal notes about this application…">{{ $application->admin_notes }}</textarea>
                    </div>
                    <x-btn type="submit" class="w-full justify-center">Update Status</x-btn>
                </form>
            </div>
        </div>

        {{-- Delete --}}
        <div class="bg-white rounded-xl border border-red-100 shadow-sm">
            <div class="p-6">
                <form action="{{ route('admin.applications.destroy', $application) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this application?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-center text-sm text-red-600 hover:text-red-800 font-semibold py-2 rounded-lg hover:bg-red-50 transition-colors">
                        Delete Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
