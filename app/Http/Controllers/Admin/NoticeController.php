<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = Notice::with('author', 'attachments')
            ->withCount('attachments')
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:general,department,class,teachers,exam,news,event',
            'published_at'   => 'nullable|string|max:20',
            'attachments'    => 'nullable|array|max:10',
            'attachments.*'  => 'file|max:20480',
        ]);

        if (!empty($data['published_at'])) {
            $data['published_at'] = NepaliDateHelper::toAD($data['published_at']);
        }

        $data['created_by']   = auth()->id();
        $data['slug']         = Str::slug($data['title']) . '-' . uniqid();
        $data['is_published'] = true;
        unset($data['attachments']);

        $notice = Notice::create($data);

        // Handle multiple attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->attachments()->create([
                    'file_path' => $file->store('notices', 'public'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        PublicDataService::invalidate('*');

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice published.');
    }

    public function edit(Notice $notice)
    {
        $notice->load('attachments');
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:general,department,class,teachers,exam,news,event',
            'published_at'   => 'nullable|string|max:20',
            'attachments'    => 'nullable|array|max:10',
            'attachments.*'  => 'file|max:20480',
        ]);

        if (!empty($data['published_at'])) {
            $data['published_at'] = NepaliDateHelper::toAD($data['published_at']);
        }
        unset($data['attachments']);

        $notice->update($data);

        // Remove selected attachments
        if ($request->filled('remove_attachments')) {
            $ids = array_filter(explode(',', $request->remove_attachments));
            $toDelete = $notice->attachments()->whereIn('id', $ids)->get();
            foreach ($toDelete as $att) {
                if (Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
                $att->delete();
            }
        }

        // Add new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->attachments()->create([
                    'file_path' => $file->store('notices', 'public'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        PublicDataService::invalidate('*');

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        // Delete all attachment files
        foreach ($notice->attachments as $att) {
            if (Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
        }
        // Legacy single attachment
        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }
        $notice->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted.');
    }
}
