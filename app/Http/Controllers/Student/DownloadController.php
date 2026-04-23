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

        // Get downloads for student's program and semester
        $downloadsQuery = Download::with(['subject', 'uploadedBy'])
            ->where('program_id', $student->program_id)
            ->where(function($q) use ($student) {
                $q->whereNull('semester')
                  ->orWhere('semester', $student->semester);
            });

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

        // Get subjects for filter
        $subjects = Subject::where('program_id', $student->program_id)
            ->where('semester', $student->semester)
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $totalDownloads = Download::where('program_id', $student->program_id)
            ->where(function($q) use ($student) {
                $q->whereNull('semester')
                  ->orWhere('semester', $student->semester);
            })
            ->count();

        $subjectCount = Download::where('program_id', $student->program_id)
            ->where(function($q) use ($student) {
                $q->whereNull('semester')
                  ->orWhere('semester', $student->semester);
            })
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

        $download = Download::where('program_id', $student->program_id)
            ->where(function($q) use ($student) {
                $q->whereNull('semester')
                  ->orWhere('semester', $student->semester);
            })
            ->findOrFail($id);

        if (!Storage::disk('public')->exists($download->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($download->file_path, $download->title);
    }
}
