<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Get filters
        $subjectId = $request->get('subject_id');
        $search = $request->get('search');

        // Get downloads with proper validation logic using scope
        $downloadsQuery = Download::with(['subject', 'uploadedBy'])
            ->visibleToStudent($student);

        if ($subjectId) {
            $downloadsQuery->where('subject_id', $subjectId);
        }

        if ($search) {
            $downloadsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $downloads = $downloadsQuery->latest()->paginate(20);

        // Get subjects for filter (student's current semester subjects)
        $subjects = Subject::where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->orderBy('name')
            ->get();

        // Calculate statistics with same validation logic
        $totalDownloads = Download::visibleToStudent($student)->count();

        $subjectCount = Download::visibleToStudent($student)
            ->whereNotNull('subject_id')
            ->distinct('subject_id')
            ->count('subject_id');

        return view('student.downloads.index', compact(
            'student',
            'downloads',
            'subjects',
            'totalDownloads',
            'subjectCount'
        ));
    }

    public function file($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Validate download access with same logic using scope
        $download = Download::visibleToStudent($student)->findOrFail($id);

        if (!Storage::disk('public')->exists($download->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($download->file_path, $download->title);
    }
}
