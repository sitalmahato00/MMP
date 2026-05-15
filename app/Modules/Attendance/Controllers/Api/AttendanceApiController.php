<?php

namespace App\Modules\Attendance\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Student\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceApiController extends BaseController
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    /**
     * GET /api/v1/attendance
     * Get attendance records with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $records = Attendance::query()
            ->with([
                'student.user:id,name',
                'subject:id,name,code',
                'attendanceSession:id,date',
            ])
            ->when($request->student_id,  fn ($q) => $q->where('student_id', $request->student_id))
            ->when($request->subject_id,  fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->date,        fn ($q) => $q->whereDate('date', $request->date))
            ->when($request->date_from,   fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->date_to,     fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->when($request->status,      fn ($q) => $q->where('status', $request->status))
            ->latest('date')
            ->paginate(min((int) ($request->per_page ?? 30), 200))
            ->withQueryString();

        return $this->success($records);
    }

    /**
     * POST /api/v1/attendance
     * Mark attendance (bulk).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id'              => ['required', 'integer', 'exists:subjects,id'],
            'date'                    => ['required', 'date'],
            'records'                 => ['required', 'array', 'min:1'],
            'records.*.student_id'    => ['required', 'integer', 'exists:students,id'],
            'records.*.status'        => ['required', Rule::in(['present','absent','late','excused'])],
            'records.*.remark'        => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data['records'] as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'subject_id' => $data['subject_id'],
                    'date'       => $data['date'],
                ],
                [
                    'status' => $record['status'],
                    'remark' => $record['remark'] ?? null,
                ]
            );
        }

        return $this->success(null, 'Attendance marked successfully.');
    }

    /**
     * GET /api/v1/attendance/summary/{student}
     * Attendance summary for a single student.
     */
    public function studentSummary(Student $student, Request $request): JsonResponse
    {
        $summary = $this->attendanceService->getSummary(
            $student,
            $request->subject_id,
            $request->date_from,
            $request->date_to
        );

        return $this->success($summary);
    }
}
