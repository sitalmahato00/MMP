<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $notices = Notice::with('author')
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
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'type'         => 'required|in:general,department,class,teachers,exam',
            'published_at' => 'nullable|date',
            'attachment'   => 'nullable|file|max:20480', // 20MB limit
        ]);

        $data['created_by']   = auth()->id();
        $data['slug']         = Str::slug($data['title']) . '-' . uniqid();
        $data['is_published'] = true;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('notices', 'public');
        }

        Notice::create($data);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice published.');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'type'         => 'required|in:general,department,class,teachers,exam',
            'published_at' => 'nullable|date',
            'attachment'   => 'nullable|file|max:20480',
        ]);

        if ($request->hasFile('attachment')) {
            if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
                Storage::disk('public')->delete($notice->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('notices', 'public');
        }

        $notice->update($data);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }
        $notice->delete();
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted.');
    }
}
