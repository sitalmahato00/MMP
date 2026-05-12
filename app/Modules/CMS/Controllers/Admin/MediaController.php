<?php

namespace App\Modules\CMS\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::when($request->search, fn($q) => $q->where('file_name', 'like', "%{$request->search}%")->orWhere('title', 'like', "%{$request->search}%"))
            ->when($request->type, fn($q) => $q->where('file_type', $request->type))
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
            'files'   => 'required|array|min:1|max:20',
            'files.*' => 'file|max:10240',
            'type'    => 'required|in:gallery,document',
        ]);

        $count = 0;
        foreach ($request->file('files') as $file) {
            $path = $file->store('media', 'public');
            Media::create([
                'title'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'file_type'  => $request->type,
                'mime_type'  => $file->getMimeType(),
                'size'       => $file->getSize(),
                'uploaded_by'=> auth()->id(),
            ]);
            $count++;
        }

        PublicDataService::invalidate('*');

        return redirect()->route('admin.media.index')
            ->with('success', $count . ' file(s) uploaded.');
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
        $medium->update(['file_type' => $request->type]);
        PublicDataService::invalidate('*');
        return redirect()->route('admin.media.index')->with('success', 'Media updated.');
    }

    public function destroy(Media $medium)
    {
        if ($medium->file_path && Storage::disk('public')->exists($medium->file_path)) {
            Storage::disk('public')->delete($medium->file_path);
        }
        $medium->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.media.index')->with('success', 'Media deleted.');
    }
}
