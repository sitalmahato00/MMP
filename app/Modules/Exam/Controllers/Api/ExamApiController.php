<?php

namespace App\Modules\Exam\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\Mark;
use App\Modules\Exam\Services\MarksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamApiController extends BaseController
{
    public function __construct(private readonly MarksService $marksService) {}

    /**
     * GET /api/v1/exams
     */
    public function index(Request $request): JsonResponse
    {
        $exams = Exam::query()
            ->with(['academicSession:id,name'])
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->status,              fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return $this->success($exams);
    }

    /**
     * POST /api/v1/exams
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'type'                => ['required', 'string', 'max:100'],
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $exam = Exam::create(array_merge($data, ['status' => 'draft']));
        return $this->created($exam->load('academicSession:id,name'));
    }

    /**
     * GET /api/v1/exams/{exam}
     */
    public function show(Exam $exam): JsonResponse
    {
        return $this->success($exam->load('academicSession:id,name'));
    }

    /**
     * PUT /api/v1/exams/{exam}
     */
    public function update(Request $request, Exam $exam): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['sometimes', 'string', 'max:255'],
            'type'       => ['sometimes', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'status'     => ['nullable', Rule::in(['draft', 'published', 'completed'])],
        ]);

        $exam->update($data);
        return $this->success($exam->fresh('academicSession:id,name'));
    }

    /**
     * DELETE /api/v1/exams/{exam}
     */
    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();
        return $this->noContent();
    }

    /**
     * POST /api/v1/exams/{exam}/publish
     */
    public function publish(Exam $exam): JsonResponse
    {
        if ($exam->status === 'published') {
            return $this->error('Exam is already published.', 422);
        }

        $exam->update(['status' => 'published', 'is_published' => true]);
        return $this->success($exam, 'Exam published successfully.');
    }

    /**
     * GET /api/v1/exams/{exam}/marks
     */
    public function marks(Exam $exam): JsonResponse
    {
        $marks = Mark::query()
            ->where('exam_id', $exam->id)
            ->with(['student.user:id,name', 'subject:id,name,code'])
            ->get();

        return $this->success($marks);
    }

    /**
     * POST /api/v1/exams/{exam}/marks
     * Bulk upsert marks for an exam.
     */
    public function storeMarks(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'marks'                   => ['required', 'array'],
            'marks.*.student_id'      => ['required', 'integer', 'exists:students,id'],
            'marks.*.subject_id'      => ['required', 'integer', 'exists:subjects,id'],
            'marks.*.obtained_marks'  => ['required', 'numeric', 'min:0'],
            'marks.*.full_marks'      => ['required', 'numeric', 'min:1'],
            'marks.*.remarks'         => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($request->marks as $markData) {
            Mark::updateOrCreate(
                [
                    'exam_id'    => $exam->id,
                    'student_id' => $markData['student_id'],
                    'subject_id' => $markData['subject_id'],
                ],
                [
                    'obtained_marks' => $markData['obtained_marks'],
                    'full_marks'     => $markData['full_marks'],
                    'remarks'        => $markData['remarks'] ?? null,
                ]
            );
        }

        return $this->success(null, 'Marks saved successfully.');
    }
}
