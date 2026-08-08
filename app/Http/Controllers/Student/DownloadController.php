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

        // Semester selector — allow viewing downloads for any past semester
        $currentSemester  = $student->current_semester;
        $selectedSemester = (int) $request->get('semester', $currentSemester);
        $selectedSemester = max(1, min($selectedSemester, $currentSemester));
        $semesterOptions  = range(1, $currentSemester);

        $subjectId = $request->get('subject_id');
        $search    = $request->get('search');

        // Build a temporary student-like context for the selected semester
        // Clone student and override semester for the scope
        $studentForSemester = clone $student;
        $studentForSemester->current_semester = $selectedSemester;

        $downloadsQuery = Download::with(['subject', 'uploadedBy'])
            ->visibleToStudent($studentForSemester);

        if ($subjectId) {
            $downloadsQuery->where('subject_id', $subjectId);
        }

        if ($search) {
            $downloadsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $downloads = $downloadsQuery->latest()->paginate(20);

        // Subjects for the selected semester (for filter dropdown)
        $subjects = Subject::where('program_id', $student->program_id)
            ->where('semester', $selectedSemester)
            ->orderBy('name')
            ->get();

        $totalDownloads = Download::visibleToStudent($studentForSemester)->count();

        $subjectCount = Download::visibleToStudent($studentForSemester)
            ->whereNotNull('subject_id')
            ->distinct('subject_id')
            ->count('subject_id');

        return view('student.downloads.index', compact(
            'student', 'downloads', 'subjects', 'totalDownloads', 'subjectCount',
            'selectedSemester', 'currentSemester', 'semesterOptions'
        ));
    }

    public function file($id)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Allow downloading files from any past semester
        $studentForAny = clone $student;
        // Check access across all semesters 1..current
        $accessible = false;
        for ($sem = 1; $sem <= $student->current_semester; $sem++) {
            $studentForAny->current_semester = $sem;
            if (Download::visibleToStudent($studentForAny)->where('id', $id)->exists()) {
                $accessible = true;
                break;
            }
        }

        if (!$accessible) {
            abort(403, 'Access denied');
        }

        $download = Download::findOrFail($id);

        if (!Storage::disk('public')->exists($download->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($download->file_path, $download->title);
    }
}
