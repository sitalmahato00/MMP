<?php

namespace App\Modules\HOD\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Download::query();
        
        // Get the HOD's department
        $department = $user->hodDepartment;
        
        if ($department) {
            // Show downloads for this department OR uploaded by this user
            $query->where(function ($q) use ($department, $user) {
                $q->where('department_id', $department->id)
                  ->orWhere('uploaded_by', $user->id);
            });
        } else {
            // If no department, show only files uploaded by this user
            $query->where('uploaded_by', $user->id);
        }
        
        // Filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
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
            'is_public'   => 'required|boolean',
            'file'        => 'required|file|max:20480',
        ]);

        $file = $request->file('file');
        $data['file_path'] = $file->store('downloads', 'public');
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_type'] = $file->getClientOriginalExtension();
        $data['file_size'] = $file->getSize();
        $data['uploaded_by'] = auth()->id();
        
        // Get department ID - try multiple ways
        $user = auth()->user();
        $departmentId = null;
        
        // Try via hodDepartment relationship
        if ($user->hodDepartment) {
            $departmentId = $user->hodDepartment->id;
        }
        // Try via teacher relationship
        elseif ($user->teacher && $user->teacher->department_id) {
            $departmentId = $user->teacher->department_id;
        }
        
        $data['department_id'] = $departmentId;

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
            'is_public'   => 'required|boolean',
            'file'        => 'nullable|file|max:20480',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('downloads', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }
        $download->update($data);

        return redirect()->route('hod.downloads.index')->with('success', 'Resource updated successfully.');
    }

    public function destroy(Download $download)
    {
        $download->delete();
        return redirect()->route('hod.downloads.index')->with('success', 'Resource deleted successfully.');
    }

    public function file(Download $download)
    {
        abort_unless($download->file_path, 404);

        $disk = $download->storageDisk();
        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($download->file_path), 404);
        $filename = $download->file_name ?: basename($download->file_path);

        return \Illuminate\Support\Facades\Storage::disk($disk)->response($download->file_path, $filename, [
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }
}
