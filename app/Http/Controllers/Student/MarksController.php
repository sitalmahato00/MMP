<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use Illuminate\Http\Request;

class MarksController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Get filters
        $examType = $request->get('exam_type');
        $category = $request->get('category');
        $semester = $request->get('semester');

        // Get marks with exam and subject details
        $marksQuery = Mark::with(['exam', 'subject'])
            ->where('student_id', $student->id)
            ->whereHas('exam', function($q) {
                $q->where('status', 'published');
            });

        // Apply filters
        if ($examType) {
            $marksQuery->whereHas('exam', function($q) use ($examType) {
                $q->where('type', $examType);
            });
        }

        if ($category) {
            $marksQuery->whereHas('exam', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        if ($semester) {
            $marksQuery->where('semester', $semester);
        }

        $marks = $marksQuery->latest()->paginate(20);

        // Calculate statistics
        $allMarks = Mark::where('student_id', $student->id)
            ->whereHas('exam', function($q) {
                $q->where('status', 'published');
            })
            ->get();

        $totalExams = $allMarks->pluck('exam_id')->unique()->count();
        $averageMarks = $allMarks->avg('obtained_marks') ?? 0;
        $totalSubjects = $allMarks->pluck('subject_id')->unique()->count();
        
        // Calculate pass percentage
        $passedSubjects = $allMarks->filter(function($mark) {
            return $mark->obtained_marks >= ($mark->pass_marks ?? 32);
        })->count();
        $passPercentage = $allMarks->count() > 0 ? round(($passedSubjects / $allMarks->count()) * 100, 1) : 0;

        // Get performance chart data (last 6 exams)
        $recentExams = Exam::whereHas('marks', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->where('status', 'published')
            ->latest('start_date')
            ->take(6)
            ->get();

        $chartData = [
            'labels' => [],
            'data' => []
        ];

        foreach ($recentExams->reverse() as $exam) {
            $examMarks = Mark::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();
            
            $avgPercentage = $examMarks->avg(function($mark) {
                return $mark->total_marks > 0 ? ($mark->obtained_marks / $mark->total_marks) * 100 : 0;
            });

            $chartData['labels'][] = $exam->name;
            $chartData['data'][] = round($avgPercentage, 1);
        }

        return view('student.marks.index', compact(
            'student',
            'marks',
            'totalExams',
            'averageMarks',
            'totalSubjects',
            'passPercentage',
            'chartData'
        ));
    }

    public function show($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $exam = Exam::with(['programs', 'academicSession'])->findOrFail($id);
        
        // Get marks for this exam
        $marks = Mark::with(['subject', 'exam'])
            ->where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->get();

        if ($marks->isEmpty()) {
            abort(404, 'No marks found for this exam');
        }

        // Calculate totals
        $totalObtained = $marks->sum('obtained_marks');
        $totalMarks = $marks->sum('total_marks');
        $percentage = $totalMarks > 0 ? round(($totalObtained / $totalMarks) * 100, 2) : 0;

        return view('student.marks.show', compact('student', 'exam', 'marks', 'totalObtained', 'totalMarks', 'percentage'));
    }
}
