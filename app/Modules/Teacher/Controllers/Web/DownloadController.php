<?php

namespace App\Modules\Teacher\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Download, AcademicSession, Subject};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();
        
        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        $query = Download::where('uploaded_by', $user->id)
            ->with(['subject', 'program', 'uploader']);
        
        // Filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }
        
        $downloads = $query->latest()->paginate(20)->withQueryString();
        
        // Get teacher's subjects for filter
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();
        
        // Get unique categories
        $categories = Download::where('uploaded_by', $user->id)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
        
        return view('teacher.downloads.index', compact('downloads', 'subjects', 'categories', 'session'));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();
        
        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }
        
        // Get teacher's subjects
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();
        
        return view('teacher.downloads.create', compact('subjects', 'session'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:50',
            'subject_id'  => 'required|exists:subjects,id',
            'visibility'  => 'required|in:public,students,private',
            'file'        => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar|max:20480',
        ]);

        // Verify teacher teaches this subject
        $subject = Subject::findOrFail($data['subject_id']);
        if (!$teacher->subjects()->where('subject_id', $subject->id)->exists()) {
            abort(403, 'You are not authorized to upload resources for this subject');
        }

        $file = $request->file('file');
        $data['file_path'] = $file->store('downloads', 'public');
        $data['file_name'] = $file->getClientOriginalName();
        $data['file_type'] = $file->getClientOriginalExtension();
        $data['file_size'] = $file->getSize();
        $data['uploaded_by'] = $user->id;
        $data['department_id'] = $teacher->department_id;
        $data['program_id'] = $subject->program_id;
        $data['semester'] = $subject->semester;
        $data['is_public'] = $data['visibility'] === 'public';

        Download::create($data);

        return redirect()->route('teacher.downloads.index')->with('success', 'Resource uploaded successfully.');
    }

    public function edit(Download $download)
    {
        // Verify ownership
        if ($download->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();
        
        // Get teacher's subjects
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();
        
        return view('teacher.downloads.edit', compact('download', 'subjects', 'session'));
    }

    public function update(Request $request, Download $download)
    {
        // Verify ownership
        if ($download->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        $user = auth()->user();
        $teacher = $user->teacher;
        
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:50',
            'subject_id'  => 'required|exists:subjects,id',
            'visibility'  => 'required|in:public,students,private',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar|max:20480',
        ]);

        // Verify teacher teaches this subject
        $subject = Subject::findOrFail($data['subject_id']);
        if (!$teacher->subjects()->where('subject_id', $subject->id)->exists()) {
            abort(403, 'You are not authorized to upload resources for this subject');
        }

        if ($request->hasFile('file')) {
            // Delete old file
            if ($download->file_path) {
                Storage::disk('public')->delete($download->file_path);
            }
            
            $file = $request->file('file');
            $data['file_path'] = $file->store('downloads', 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }
        
        $data['program_id'] = $subject->program_id;
        $data['semester'] = $subject->semester;
        $data['is_public'] = $data['visibility'] === 'public';
        
        $download->update($data);

        return redirect()->route('teacher.downloads.index')->with('success', 'Resource updated successfully.');
    }

    public function destroy(Download $download)
    {
        // Verify ownership
        if ($download->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        
        // Delete file
        if ($download->file_path) {
            Storage::disk('public')->delete($download->file_path);
        }
        
        $download->delete();
        
        return redirect()->route('teacher.downloads.index')->with('success', 'Resource deleted successfully.');
    }

    public function file(Download $download)
    {
        abort_unless($download->file_path, 404);

        $disk = $download->storageDisk();
        abort_unless(Storage::disk($disk)->exists($download->file_path), 404);
        
        $filename = $download->file_name ?: basename($download->file_path);

        return Storage::disk($disk)->response($download->file_path, $filename, [
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }
}
