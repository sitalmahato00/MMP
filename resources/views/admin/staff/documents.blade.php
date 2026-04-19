@extends('layouts.app')
@section('title', $staff->name . ' Documents')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:flex-row lg:items-end lg:justify-between lg:p-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Staff Documents</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $staff->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Upload records, certificates, and attachments that support the staff profile.</p>
        </div>
        <a href="{{ route('admin.staff.show', $staff) }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Back to Profile</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 class="text-lg font-semibold text-slate-900">Upload Document</h2>
            <form method="POST" action="{{ route('admin.staff.documents.store', $staff) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Document Type</label>
                    <input type="text" name="document_type" value="{{ old('document_type') }}" placeholder="certificate, contract, nid" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    @error('document_type')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Label</label>
                    <input type="text" name="label" value="{{ old('label') }}" placeholder="Signed Employment Contract" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    @error('label')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">File</label>
                    <input type="file" name="file" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#8B0000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                    @error('file')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">Issued At</label>
                        <x-bs-date-picker name="issued_at" :value="old('issued_at')" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
                        @error('issued_at')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">Public</label>
                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            <input type="checkbox" name="is_public" value="1" checked class="h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                            Visible on public profile
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#6f0000]">Upload Document</button>
            </form>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Existing Documents</h2>
                    <p class="text-sm text-slate-500">{{ $documents->total() }} total records</p>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                            <th class="px-4 py-3">Label</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Issued</th>
                            <th class="px-4 py-3">Visibility</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($documents as $document)
                            @php($fileUrl = asset('storage/' . ltrim($document->file_path, '/')))
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-slate-900">{{ $document->label }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ number_format(max((int) $document->file_size, 0) / 1024, 1) }} KB</div>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $document->issued_at ? bsDate($document->issued_at, 'Y F d') : '—' }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $document->is_public ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $document->is_public ? 'Public' : 'Private' }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ $fileUrl }}" target="_blank" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Open</a>
                                        <form method="POST" action="{{ route('admin.staff.documents.destroy', [$staff, $document]) }}" onsubmit="return confirm('Delete this document?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">No documents uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($documents->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">
                    {{ $documents->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection