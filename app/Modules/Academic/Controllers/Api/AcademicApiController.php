<?php

namespace App\Modules\Academic\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicApiController extends BaseController
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/dashboard/stats
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = [
            'total_students'   => Student::count(),
            'active_students'  => Student::where('status', 'active')->count(),
            'total_teachers'   => Teacher::count(),
            'active_teachers'  => Teacher::where('status', 'active')->count(),
            'total_departments' => Department::count(),
            'current_session'  => AcademicSession::current(),
        ];

        return $this->success($stats);
    }

    // ─── Sessions ─────────────────────────────────────────────────────────────

    public function sessions(): JsonResponse
    {
        return $this->success(AcademicSession::orderByDesc('id')->limit(20)->get());
    }

    public function currentSession(): JsonResponse
    {
        $session = AcademicSession::current();
        if (!$session) return $this->notFound('No active session found.');
        return $this->success($session);
    }

    public function storeSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100', 'unique:academic_sessions,name'],
            'name_bs'    => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
        ]);

        if (!empty($data['is_current'])) {
            AcademicSession::query()->update(['is_current' => false]);
        }

        $session = AcademicSession::create($data);
        return $this->created($session);
    }

    // ─── Programs ─────────────────────────────────────────────────────────────

    public function programs(Request $request): JsonResponse
    {
        $programs = Program::query()
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->orderBy('name')
            ->get();

        return $this->success($programs);
    }

    // ─── Departments ──────────────────────────────────────────────────────────

    public function departments(): JsonResponse
    {
        return $this->success(Department::orderBy('name')->get(['id', 'name', 'code']));
    }

    // ─── Subjects ─────────────────────────────────────────────────────────────

    public function subjects(Request $request): JsonResponse
    {
        $subjects = Subject::query()
            ->when($request->program_id,  fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester,    fn ($q) => $q->where('semester', $request->semester))
            ->orderBy('name')
            ->get();

        return $this->success($subjects);
    }
}
