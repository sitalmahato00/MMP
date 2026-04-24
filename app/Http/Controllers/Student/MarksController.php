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

        $exam = Exam::with(['programs', 'academicSession', 'department'])->findOrFail($id);
        
        // Verify exam is published
        if (!$exam->is_published) {
            abort(404, 'Exam results not published');
        }
        
        // Get marks for this exam
        $marks = $student->marks()
            ->where('exam_id', $exam->id)
            ->where('status', 'published')
            ->with(['subject', 'teacher.user'])
            ->get();

        if ($marks->isEmpty()) {
            abort(404, 'No results found');
        }

        // Calculate totals
        $totalObtained = 0;
        $totalFull = 0;
        $allPassed = true;
        
        $marksData = $marks->map(function($mark) use ($exam, &$totalObtained, &$totalFull, &$allPassed) {
            $scheme = $this->getMarkingScheme($exam, $mark->subject_id);
            
            if ($exam->category === 'monthly_assessment') {
                $full = $mark->assessment_full_marks ?? 0;
                $obtained = $mark->assessment_obtained_marks ?? 0;
            } else {
                $full = ($scheme['full_marks_internal_theory'] ?? 0) +
                       ($scheme['full_marks_external_theory'] ?? 0) +
                       ($scheme['full_marks_internal_practical'] ?? 0) +
                       ($scheme['full_marks_external_practical'] ?? 0);
                $obtained = $mark->total_marks;
            }
            
            $totalObtained += $obtained;
            $totalFull += $full;
            
            if (!$mark->is_passed) {
                $allPassed = false;
            }
            
            return [
                'mark' => $mark,
                'scheme' => $scheme,
                'full_marks' => $full,
                'obtained_marks' => $obtained,
            ];
        });
        
        $percentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;
        
        $student->load(['user', 'department', 'program', 'academicSession']);
        
        return view('student.marks.show', compact(
            'student',
            'exam',
            'marksData',
            'totalObtained',
            'totalFull',
            'percentage',
            'allPassed'
        ));
    }
    
    private function getMarkingScheme(Exam $exam, int $subjectId): array
    {
        $scheme = \DB::table('exam_subject_marking_schemes')
            ->where('exam_id', $exam->id)
            ->where('subject_id', $subjectId)
            ->first();
        
        if ($scheme) {
            return [
                'full_marks_internal_theory' => $scheme->full_marks_internal_theory,
                'pass_marks_internal_theory' => $scheme->pass_marks_internal_theory,
                'full_marks_external_theory' => $scheme->full_marks_external_theory,
                'pass_marks_external_theory' => $scheme->pass_marks_external_theory,
                'full_marks_internal_practical' => $scheme->full_marks_internal_practical,
                'pass_marks_internal_practical' => $scheme->pass_marks_internal_practical,
                'full_marks_external_practical' => $scheme->full_marks_external_practical,
                'pass_marks_external_practical' => $scheme->pass_marks_external_practical,
            ];
        }
        
        $subject = \App\Models\Subject::find($subjectId);
        if ($subject) {
            return [
                'full_marks_internal_theory' => $subject->full_marks_internal_theory ?? 0,
                'pass_marks_internal_theory' => $subject->pass_marks_internal_theory ?? 0,
                'full_marks_external_theory' => $subject->full_marks_external_theory ?? 0,
                'pass_marks_external_theory' => $subject->pass_marks_external_theory ?? 0,
                'full_marks_internal_practical' => $subject->full_marks_internal_practical ?? 0,
                'pass_marks_internal_practical' => $subject->pass_marks_internal_practical ?? 0,
                'full_marks_external_practical' => $subject->full_marks_external_practical ?? 0,
                'pass_marks_external_practical' => $subject->pass_marks_external_practical ?? 0,
            ];
        }
        
        return [
            'full_marks_internal_theory' => 0,
            'pass_marks_internal_theory' => 0,
            'full_marks_external_theory' => 0,
            'pass_marks_external_theory' => 0,
            'full_marks_internal_practical' => 0,
            'pass_marks_internal_practical' => 0,
            'full_marks_external_practical' => 0,
            'pass_marks_external_practical' => 0,
        ];
    }
}
