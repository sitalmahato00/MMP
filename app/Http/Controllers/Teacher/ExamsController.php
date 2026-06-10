<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's assigned subjects for current session
        $teacherSubjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with(['program'])
            ->get();

        if ($teacherSubjects->isEmpty()) {
            // No subjects assigned, show empty state
            $exams = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('teacher.exams.index', [
                'exams' => $exams,
                'totalExams' => 0,
                'upcomingExams' => 0,
                'ongoingExams' => 0,
                'completedExams' => 0,
                'session' => $session
            ]);
        }

        // Get program-semester combinations that teacher teaches
        $teacherProgramSemesters = $teacherSubjects->map(function ($subject) {
            return [
                'program_id' => $subject->program_id,
                'semester' => $subject->semester
            ];
        })->unique()->values();

        // Get exams that match teacher's program-semester combinations
        $query = Exam::whereHas('programs', function ($q) use ($teacherProgramSemesters) {
                foreach ($teacherProgramSemesters as $ps) {
                    $q->orWhere(function ($subQ) use ($ps) {
                        $subQ->where('programs.id', $ps['program_id'])
                             ->where('exam_program.semester', $ps['semester']);
                    });
                }
            })
            ->with(['academicSession:id,name', 'programs'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('name', 'like', "%{$term}%");
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->category, fn ($q) => $q->where('category', $request->category));

        $exams = (clone $query)
            ->latest('created_at')
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        // Add marks status for each exam
        $exams->getCollection()->transform(function ($exam) use ($teacher, $teacherSubjects) {
            // Get teacher's subjects for this exam
            $relevantSubjects = $teacherSubjects->filter(function ($subject) use ($exam) {
                return $exam->programs->contains(function ($program) use ($subject) {
                    return $program->id == $subject->program_id && $program->pivot->semester == $subject->semester;
                });
            });

            if ($relevantSubjects->isEmpty()) {
                $exam->teacher_marks_status = 'not_applicable';
                $exam->teacher_marks_count = 0;
                $exam->teacher_total_subjects = 0;
                return $exam;
            }

            // Count marks filled by teacher for this exam
            $marksCount = Mark::where('exam_id', $exam->id)
                ->whereIn('subject_id', $relevantSubjects->pluck('id'))
                ->where('status', '!=', 'draft')
                ->distinct('subject_id')
                ->count();

            $totalSubjects = $relevantSubjects->count();
            
            $exam->teacher_marks_count = $marksCount;
            $exam->teacher_total_subjects = $totalSubjects;
            
            if ($marksCount == 0) {
                $exam->teacher_marks_status = 'not_filled';
            } elseif ($marksCount < $totalSubjects) {
                $exam->teacher_marks_status = 'partially_filled';
            } else {
                $exam->teacher_marks_status = 'completed';
            }

            return $exam;
        });

        // Stats
        $totalExams = (clone $query)->count();
        $upcomingExams = (clone $query)->where('status', 'upcoming')->count();
        $ongoingExams = (clone $query)->where('status', 'ongoing')->count();
        $completedExams = (clone $query)->where('status', 'completed')->count();

        return view('teacher.exams.index', compact(
            'exams', 'session',
            'totalExams', 'upcomingExams', 'ongoingExams', 'completedExams'
        ));
    }

    // ── Fill Marks ─────────────────────────────────────────────────────────
    public function fillMarks(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $examId = $request->exam_id;
        
        if (!$examId) {
            return redirect()->route('teacher.exams.index')
                ->with('error', 'Please select an exam to fill marks.');
        }

        // Get teacher's assigned subjects for current session
        $teacherSubjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with(['program'])
            ->get();

        $exam = Exam::with(['academicSession:id,name', 'programs'])
            ->findOrFail($examId);

        // Check if teacher teaches any subjects for this exam's programs/semesters
        $examProgramSemesters = $exam->programs->map(function ($program) {
            return [
                'program_id' => $program->id,
                'semester' => $program->pivot->semester
            ];
        });

        $teacherRelevantSubjects = $teacherSubjects->filter(function ($subject) use ($examProgramSemesters) {
            return $examProgramSemesters->contains(function ($ps) use ($subject) {
                return $ps['program_id'] == $subject->program_id && $ps['semester'] == $subject->semester;
            });
        });

        if ($teacherRelevantSubjects->isEmpty()) {
            abort(403, 'You are not assigned to any subjects for this exam.');
        }

        // Get only exam programs/semesters that this teacher is assigned to
        $programs = $exam->programs->filter(function ($program) use ($teacherRelevantSubjects) {
            return $teacherRelevantSubjects->contains(function ($subject) use ($program) {
                return $subject->program_id == $program->id
                    && $subject->semester == $program->pivot->semester;
            });
        })->values();

        if ($programs->isEmpty()) {
            abort(403, 'You are not assigned to any programs or semesters for this exam.');
        }

        $programId = null;
        $semester = null;

        $requestedProgramId = $request->program_id;
        $requestedSemester = $request->semester;

        $selectedProgram = null;

        if ($requestedProgramId && $requestedSemester) {
            $selectedProgram = $programs->first(fn($program) => $program->id == $requestedProgramId && (int) $program->pivot->semester === (int) $requestedSemester);
        }

        if (!$selectedProgram && $requestedProgramId) {
            $selectedProgram = $programs->first(fn($program) => $program->id == $requestedProgramId);
        }

        if (!$selectedProgram) {
            $selectedProgram = $programs->first();
        }

        $programId = $selectedProgram->id;
        $semester = (int) $selectedProgram->pivot->semester;
        $subjectId = $request->subject_id;

        $semesters = $programs
            ->filter(fn($program) => $program->id == $programId)
            ->pluck('pivot.semester')
            ->unique()
            ->sort()
            ->values();

        if (!$programId || !$subjectId) {
            // Show selection form - only teacher's subjects for the selected program/semester
            $subjects = $teacherRelevantSubjects->filter(function ($subject) use ($programId, $semester) {
                return $subject->program_id == $programId && $subject->semester == $semester;
            });

            return view('teacher.exams.fill-marks-select', compact(
                'exam', 'programs', 'subjects', 'programId', 'semester', 'semesters'
            ));
        }

        // Verify teacher is assigned to this subject
        $subject = $teacherRelevantSubjects->firstWhere('id', $subjectId);
        if (!$subject) {
            abort(403, 'You are not assigned to this subject.');
        }

        // Get students for this program/semester
        $students = Student::where('program_id', $programId)
            ->where('current_semester', $semester)
            ->where('status', 'active')
            ->with(['user:id,name,email'])
            ->orderBy('roll_number')
            ->get();

        // Get existing marks
        $existingMarks = Mark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('program_id', $programId)
            ->get()
            ->keyBy('student_id');

        return view('teacher.exams.fill-marks', compact(
            'exam', 'subject', 'students', 'existingMarks', 'programId', 'semester'
        ));
    }

    // ── Save Marks ─────────────────────────────────────────────────────────
    public function saveMarks(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.is_absent' => 'nullable|boolean',
            'marks.*.assessment_attendance_percent' => 'nullable|numeric|min:0|max:100',
            'marks.*.assessment_obtained_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.external_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.external_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.remarks' => 'nullable|string|max:500',
        ]);

        // Verify teacher is assigned to this subject
        $teacherSubject = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->where('subjects.id', $validated['subject_id'])
            ->where('program_id', $validated['program_id'])
            ->where('semester', $validated['semester'])
            ->first();

        if (!$teacherSubject) {
            abort(403, 'You are not assigned to this subject.');
        }

        $exam = Exam::findOrFail($validated['exam_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        foreach ($validated['marks'] as $markData) {
            $isAbsent = $markData['is_absent'] ?? false;

            $data = [
                'exam_id' => $exam->id,
                'student_id' => $markData['student_id'],
                'subject_id' => $subject->id,
                'program_id' => $validated['program_id'],
                'semester' => $validated['semester'],
                'is_absent' => $isAbsent,
                'status' => 'submitted',
                'remarks' => $markData['remarks'] ?? null,
            ];

            if ($exam->category === 'monthly_assessment') {
                // Assessment exam - use exam's assessment marks
                $data['assessment_full_marks'] = $exam->assessment_full_marks ?? 100;
                $data['assessment_pass_marks'] = $exam->assessment_pass_marks ?? 40;
                $data['assessment_attendance_percent'] = $isAbsent ? null : ($markData['assessment_attendance_percent'] ?? null);
                $data['assessment_obtained_marks'] = $isAbsent ? null : ($markData['assessment_obtained_marks'] ?? null);
            } else {
                // CTEVT exam - store the marks obtained
                $data['internal_theory_marks'] = $isAbsent ? null : ($markData['internal_theory_marks'] ?? null);
                $data['external_theory_marks'] = $isAbsent ? null : ($markData['external_theory_marks'] ?? null);
                $data['internal_practical_marks'] = $isAbsent ? null : ($markData['internal_practical_marks'] ?? null);
                $data['external_practical_marks'] = $isAbsent ? null : ($markData['external_practical_marks'] ?? null);
            }

            // Check if marks already exist and are published
            $existingMark = Mark::where([
                'exam_id' => $exam->id,
                'student_id' => $markData['student_id'],
                'subject_id' => $subject->id,
            ])->first();

            // Teachers can only update their own marks if not published
            if ($existingMark && $existingMark->status === 'published') {
                continue; // Skip this mark if it's published
            }

            Mark::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $markData['student_id'],
                    'subject_id' => $subject->id,
                ],
                $data
            );
        }

        return redirect()->route('teacher.exams.index')
            ->with('success', 'Marks saved successfully.');
    }
}
