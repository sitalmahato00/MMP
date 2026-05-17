<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\Staff;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardController extends Controller
{
    // ── Students ──────────────────────────────────────────────────────────

    public function studentIndex(Request $request)
    {
        $query = Student::query()
            ->with([
                'user:id,name,email,avatar',
                'program:id,name',
                'department:id,name',
                'academicSession:id,name',
            ])
            ->where('status', 'active')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->department_id,        fn ($q) => $q->where('department_id',        $request->department_id))
            ->when($request->program_id,           fn ($q) => $q->where('program_id',           $request->program_id))
            ->when($request->academic_session_id,  fn ($q) => $q->where('academic_session_id',  $request->academic_session_id))
            ->when($request->semester,             fn ($q) => $q->where('current_semester',      $request->semester));

        $students = $query->latest('id')->paginate(30)->withQueryString();

        $programs    = Program::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $sessions    = AcademicSession::orderByDesc('id')->limit(10)->get(['id', 'name']);

        return view('admin.id-cards.student-index', compact(
            'students', 'programs', 'departments', 'sessions'
        ));
    }

    public function studentBulkPdf(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:students,id',
        ]);

        $students = Student::with(['user', 'program', 'department', 'academicSession'])
            ->whereIn('id', $request->ids)
            ->get()
            ->map(fn ($s) => $this->injectStudentPhoto($s));

        $settings    = $this->siteSettings();
        $logoBase64  = $this->toBase64($settings['site_logo'] ?? null);

        $pdf = Pdf::loadView('admin.id-cards.student-pdf', compact('students', 'settings', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('student-id-cards-' . now()->format('Ymd-His') . '.pdf');
    }

    public function studentSinglePdf(Student $student)
    {
        $student->load(['user', 'program', 'department', 'academicSession']);
        $student = $this->injectStudentPhoto($student);

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $students   = collect([$student]);

        $pdf = Pdf::loadView('admin.id-cards.student-pdf', compact('students', 'settings', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('student-id-' . $student->student_no . '.pdf');
    }

    // ── Staff ─────────────────────────────────────────────────────────────

    public function staffIndex(Request $request)
    {
        $query = Staff::query()
            ->where('is_active', true)
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('staff_code', 'like', "%{$term}%")
                    ->orWhere('designation', 'like', "%{$term}%");
            })
            ->when($request->department,  fn ($q) => $q->where('department',         $request->department))
            ->when($request->designation, fn ($q) => $q->where('designation',        $request->designation))
            ->when($request->status,      fn ($q) => $q->where('employment_status',  $request->status));

        $staff = $query->orderBy('name')->paginate(30)->withQueryString();

        $departments = Staff::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()->orderBy('department')->pluck('department');

        $designations = Staff::whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()->orderBy('designation')->pluck('designation');

        return view('admin.id-cards.staff-index', compact('staff', 'departments', 'designations'));
    }

    public function staffBulkPdf(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:staff,id',
        ]);

        $staffList = Staff::whereIn('id', $request->ids)
            ->get()
            ->map(fn ($s) => $this->injectStaffPhoto($s));

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);

        $pdf = Pdf::loadView('admin.id-cards.staff-pdf', compact('staffList', 'settings', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('staff-id-cards-' . now()->format('Ymd-His') . '.pdf');
    }

    public function staffSinglePdf(Staff $staff)
    {
        $staff = $this->injectStaffPhoto($staff);

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $staffList  = collect([$staff]);

        $pdf = Pdf::loadView('admin.id-cards.staff-pdf', compact('staffList', 'settings', 'logoBase64'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('staff-id-' . ($staff->staff_code ?: $staff->id) . '.pdf');
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    private function siteSettings(): array
    {
        return SiteSetting::whereIn('key', [
            'college_name', 'college_affiliation', 'site_logo', 'contact_address',
        ])->pluck('value', 'key')->toArray();
    }

    private function toBase64(?string $storagePath): ?string
    {
        if (! $storagePath) {
            return null;
        }

        $path = Storage::disk('public')->path($storagePath);

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function injectStudentPhoto(Student $student): Student
    {
        $student->photo_b64 = $this->toBase64($student->user?->avatar);

        return $student;
    }

    private function injectStaffPhoto(Staff $staff): Staff
    {
        $path = $staff->photo ?: $staff->user?->avatar;
        $staff->photo_b64 = $this->toBase64($path);

        return $staff;
    }
}
