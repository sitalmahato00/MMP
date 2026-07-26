@extends('layouts.app')
@section('title', $staff->name)

@section('content')
@php
    $workingSchedule = $staff->working_schedule ?? [];
    $statusPillClass = $staff->employment_status === 'active' ? 'bg-emerald-500/20' : ($staff->employment_status === 'leave' ? 'bg-amber-500/20' : 'bg-rose-500/20');
@endphp

<div class="space-y-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        <div class="grid gap-0 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] p-8 text-white lg:p-10">
                <div class="flex items-start gap-5">
                    <div class="h-24 w-24 overflow-hidden rounded-3xl border border-white/20 bg-white/10 shadow-lg">
                        <img src="{{ $staff->photo_url }}" alt="{{ $staff->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/65">Administrative Staff</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $staff->name }}</h1>
                        <p class="mt-1 text-lg text-white/80">{{ $staff->designation }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{{ $staff->staff_code }}</span>
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{{ $staff->department ?: 'General Administration' }}</span>
                            <span class="rounded-full {{ $statusPillClass }} px-3 py-1 text-xs font-semibold text-white">{{ ucfirst($staff->employment_status ?? 'active') }}</span>
                            @if($staff->featured)
                                <span class="rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-white">Featured</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Public</div>
                        <div class="mt-2 text-lg font-semibold">{{ $staff->public_visible ? 'Visible' : 'Hidden' }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Documents</div>
                        <div class="mt-2 text-lg font-semibold">{{ $staff->documents->count() }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Public Docs</div>
                        <div class="mt-2 text-lg font-semibold">{{ $publicDocs->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="p-8 lg:p-10">
                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('admin.staff.edit', $staff) }}" class="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#6f0000]">Edit Profile</a>
                    <a href="{{ route('admin.staff.documents', $staff) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Manage Documents</a>
                    <form method="POST" action="{{ route('admin.staff.toggle-public', $staff) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{{ $staff->public_visible ? 'Hide from Public' : 'Make Public' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.staff.toggle-featured', $staff) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">{{ $staff->featured ? 'Remove Featured' : 'Feature Profile' }}</button>
                    </form>
                </div>

                <div class="mt-8 rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Status Controls</p>
                    <form method="POST" action="{{ route('admin.staff.status.update', $staff) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @csrf
                        @method('PATCH')
                        <select name="employment_status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                            @foreach(['active' => 'Active', 'leave' => 'On Leave', 'resigned' => 'Resigned'] as $value => $label)
                                <option value="{{ $value }}" @selected($staff->employment_status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex justify-center rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#8B0000]">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <h2 class="text-lg font-semibold text-slate-900">Profile Details</h2>
                <dl class="mt-5 grid gap-5 md:grid-cols-2">
                    @foreach([
                        ['label' => 'Staff Code', 'value' => $staff->staff_code],
                        ['label' => 'Name', 'value' => $staff->name],
                        ['label' => 'Designation', 'value' => $staff->designation],
                        ['label' => 'Department', 'value' => $staff->department],
                        ['label' => 'Address', 'value' => $staff->address],
                        ['label' => 'Date of Birth', 'value' => $staff->dob ? bsDate($staff->dob, 'Y F d') : null],
                        ['label' => 'Gender', 'value' => $staff->gender],
                        ['label' => 'Join Date', 'value' => $staff->join_date ? bsDate($staff->join_date, 'Y F d') : null],
                        ['label' => 'End Date', 'value' => $staff->end_date ? bsDate($staff->end_date, 'Y F d') : null],
                        ['label' => 'Salary Amount', 'value' => $staff->salary_amount ? number_format((float) $staff->salary_amount, 2) : null],
                    ] as $item)
                        @if(filled($item['value']))
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <dt class="text-xs uppercase tracking-[0.25em] text-slate-500">{{ $item['label'] }}</dt>
                                <dd class="mt-2 text-sm font-medium text-slate-900">{{ $item['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <h2 class="text-lg font-semibold text-slate-900">Working Schedule</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Label</div>
                        <div class="mt-2 text-sm font-medium text-slate-900">{{ data_get($workingSchedule, 'label') ?: 'Not set' }}</div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Hours</div>
                        <div class="mt-2 text-sm font-medium text-slate-900">{{ data_get($workingSchedule, 'start') ?: '—' }} to {{ data_get($workingSchedule, 'end') ?: '—' }}</div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Working Days</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse(data_get($workingSchedule, 'days', []) as $day)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $day }}</span>
                        @empty
                            <span class="text-sm text-slate-500">No schedule has been defined.</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Assigned Roles</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse($staff->assigned_roles ?? [] as $role)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $role }}</span>
                        @empty
                            <span class="text-sm text-slate-500">No assigned roles recorded.</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Responsibilities</div>
                    <div class="mt-2 text-sm leading-6 text-slate-700">{{ $staff->responsibilities ? implode(', ', $staff->responsibilities) : 'No responsibilities recorded.' }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <h2 class="text-lg font-semibold text-slate-900">Visibility</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>Public visibility</span>
                        <span class="font-semibold {{ $staff->public_visible ? 'text-emerald-600' : 'text-slate-500' }}">{{ $staff->public_visible ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>Featured profile</span>
                        <span class="font-semibold {{ $staff->featured ? 'text-amber-600' : 'text-slate-500' }}">{{ $staff->featured ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>Email public</span>
                        <span class="font-semibold {{ $staff->show_email_public ? 'text-emerald-600' : 'text-slate-500' }}">{{ $staff->show_email_public ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                        <span>Phone public</span>
                        <span class="font-semibold {{ $staff->show_phone_public ? 'text-emerald-600' : 'text-slate-500' }}">{{ $staff->show_phone_public ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
                <h2 class="text-lg font-semibold text-slate-900">Public Documents</h2>
                <div class="mt-4 space-y-3">
                    @forelse($publicDocs as $document)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $document->label }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</div>
                                </div>
                                <a href="{{ asset('storage/' . ltrim($document->file_path, '/')) }}" target="_blank" class="text-sm font-semibold text-[#8B0000]">Open</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No public documents are attached to this profile.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">This Month Attendance</h2>
                <p class="text-sm text-slate-500">Recent attendance activity for the current month.</p>
            </div>
            <a href="{{ route('admin.staff.documents', $staff) }}" class="text-sm font-semibold text-[#8B0000]">Manage documents</a>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Check In</th>
                        <th class="px-4 py-3">Check Out</th>
                        <th class="px-4 py-3">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($monthAttendance as $record)
                        <tr>
                            <td class="px-4 py-3 text-slate-700">{{ $record->attendance_date ? bsDate($record->attendance_date, 'Y F d') : '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ ucfirst($record->status) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $record->check_in ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $record->check_out ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $record->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">No attendance records found for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection