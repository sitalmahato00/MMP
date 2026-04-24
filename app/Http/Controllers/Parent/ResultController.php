<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $parent = $user->parentProfile;
        
        if (!$parent) {
            abort(403, 'Parent profile not found');
        }
        
        $children = $parent->children()
            ->with(['user', 'department', 'program'])
            ->get();

        // Get all published exams for each child
        $childrenResults = [];
        
        foreach ($children as $child) {
            // Get published exams for this student
            $exams = Exam::where('is_published', true)
                ->where('department_id', $child->department_id)
                ->whereHas('programs', function($q) use ($child) {
                    $q->where('programs.id', $child->program_id)
                      ->where('exam_program.semester', $child->current_semester);
                })
                ->with(['academicSession', 'department'])
                ->orderBy('published_at', 'desc')
                ->get();

            $examResults = [];
            
            foreach ($exams as $exam) {
                $marks = $child->marks()
                    ->where('exam_id', $exam->id)
                    ->where('status', 'published')
                    ->with('subject')
                    ->get();
                
                if ($marks->count() > 0) {
                    $totalObtained = $marks->sum('total_marks');
                    $totalFull = $marks->sum(function($mark) use ($exam) {
                        if ($exam->category === 'monthly_assessment') {
                            return $mark->assessment_full_marks ?? 0;
                        }
                        $scheme = $this->getMarkingScheme($exam, $mark->subject_id);
                        return ($scheme['full_marks_internal_theory'] ?? 0) +
                               ($scheme['full_marks_external_theory'] ?? 0) +
                               ($scheme['full_marks_internal_practical'] ?? 0) +
                               ($scheme['full_marks_external_practical'] ?? 0);
                    });
                    
                    $percentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;
                    $passed = $marks->every(fn($m) => $m->is_passed);
                    
                    $examResults[] = [
                        'exam' => $exam,
                        'marks_count' => $marks->count(),
                        'total_obtained' => $totalObtained,
                        'total_full' => $totalFull,
                        'percentage' => $percentage,
                        'passed' => $passed,
                    ];
                }
            }
            
            $childrenResults[] = [
                'child' => $child,
                'exam_results' => $examResults,
                'total_exams' => count($examResults),
            ];
        }

        return view('parent.results', compact('childrenResults'));
    }

    public function show(Student $student, Exam $exam)
    {
        $user = Auth::user();
        $parent = $user->parentProfile;
        
        if (!$parent) {
            abort(403, 'Parent profile not found');
        }
        
        // Verify this student belongs to this parent
        if (!$parent->children()->where('students.id', $student->id)->exists()) {
            abort(403, 'Unauthorized access');
        }
        
        // Verify exam is published
        if (!$exam->is_published) {
            abort(404, 'Exam results not published');
        }
        
        // Get marks for this student and exam
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
        $exam->load(['academicSession', 'department']);
        
        return view('parent.results-show', compact(
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
