<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::when($request->search, fn($q) => $q->where('file_name', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(24);
            
        return view('admin.media.index', compact('media'));
    }

    public function create()
    {
        return view('admin.media.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB mix max
            'type' => 'required|in:gallery,document',
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        Media::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
            'type'      => $request->type,
            'uploaded_by'=> auth()->id(),
        ]);

        return redirect()->route('admin.media.index')->with('success', 'Media uploaded.');
    }

    public function show(Media $medium)
    {
        return view('admin.media.show', compact('medium'));
    }

    public function edit(Media $medium)
    {
        return view('admin.media.edit', compact('medium'));
    }

    public function update(Request $request, Media $medium)
    {
        $request->validate(['type' => 'required|in:gallery,document']);
        $medium->update(['type' => $request->type]);
        return redirect()->route('admin.media.index')->with('success', 'Media updated.');
    }

    public function destroy(Media $medium)
    {
        if ($medium->file_path && Storage::disk('public')->exists($medium->file_path)) {
            Storage::disk('public')->delete($medium->file_path);
        }
        $medium->delete();
        return redirect()->route('admin.media.index')->with('success', 'Media deleted.');
    }
}
