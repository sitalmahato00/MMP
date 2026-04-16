<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index()
    {
        $downloads = Download::latest()->paginate(20);
        return view('admin.downloads.index', compact('downloads'));
    }

    public function resources()
    {
        $downloads = Download::where('category', 'resources')
            ->orWhereIn('category', ['syllabus', 'form', 'report', 'publication', 'other'])
            ->latest()->paginate(20);
        return view('admin.downloads.index', compact('downloads'));
    }

    public function create()
    {
        return view('admin.downloads.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:50',
            'file'        => 'required|file|max:20480', // 20MB max
            'is_public'   => 'boolean',
        ]);

        $file = $request->file('file');
        
        Download::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'category'    => $data['category'] ?? null,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $file->store('downloads', 'public'),
            'file_type'   => $file->getClientOriginalExtension(),
            'file_size'   => $file->getSize(),
            'is_public'   => $request->has('is_public'),
            'uploaded_by' => auth()->id(),
        ]);

        PublicDataService::invalidate('*');

        return redirect()->route('admin.downloads.index')->with('success', 'Download added.');
    }

    public function show(Download $download)
    {
        return view('admin.downloads.show', compact('download'));
    }

    public function edit(Download $download)
    {
        return view('admin.downloads.edit', compact('download'));
    }

    public function update(Request $request, Download $download)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:50',
            'file'        => 'nullable|file|max:20480',
            'is_public'   => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($download->file_path && Storage::disk('public')->exists($download->file_path)) {
                Storage::disk('public')->delete($download->file_path);
            }
            $file = $request->file('file');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_path'] = $file->store('downloads', 'public');
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $data['is_public'] = $request->has('is_public');
        $download->update($data);

        PublicDataService::invalidate('*');

        return redirect()->route('admin.downloads.index')->with('success', 'Download updated.');
    }

    public function destroy(Download $download)
    {
        if ($download->file_path && Storage::disk('public')->exists($download->file_path)) {
            Storage::disk('public')->delete($download->file_path);
        }
        $download->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.downloads.index')->with('success', 'Download deleted.');
    }
}
