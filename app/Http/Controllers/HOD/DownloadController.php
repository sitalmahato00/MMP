<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $departmentId = $user->hodDepartment->id ?? null;
        $query = Download::query();
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        } else {
            $query->whereNull('department_id');
        }
        // Filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('audience')) {
            if ($request->audience === 'public') {
                $query->where('is_public', true);
            } else {
                $query->where('audience', $request->audience);
            }
        }
        $downloads = $query->latest()->paginate(20);
        return view('hod.downloads.index', compact('downloads'));
    }

    public function create()
    {
        return view('hod.downloads.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:50',
            'audience'    => 'required|string|max:20',
            'file'        => 'required|file|max:20480',
        ]);

        $file = $request->file('file');
        $data['file_path'] = $file->store('downloads', 'public');
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_type'] = $file->getClientOriginalExtension();
        $data['file_size'] = $file->getSize();
        $data['is_public'] = $data['audience'] === 'public';
        $data['uploaded_by'] = auth()->id();

        Download::create($data);

        return redirect()->route('hod.downloads.index')->with('success', 'Resource uploaded successfully.');
    }

    public function edit(Download $download)
    {
        return view('hod.downloads.edit', compact('download'));
    }

    public function update(Request $request, Download $download)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:50',
            'audience'    => 'required|string|max:20',
            'file'        => 'nullable|file|max:20480',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('downloads', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }
        $data['is_public'] = $data['audience'] === 'public';
        $download->update($data);

        return redirect()->route('hod.downloads.index')->with('success', 'Resource updated successfully.');
    }

    public function destroy(Download $download)
    {
        $download->delete();
        return redirect()->route('hod.downloads.index')->with('success', 'Resource deleted successfully.');
    }
}
