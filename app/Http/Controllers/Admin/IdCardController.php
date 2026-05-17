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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardController extends Controller
{
    // ── Students ──────────────────────────────────────────────────────────

    public function studentIndex(): \Illuminate\View\View
    {
        $settings    = $this->siteSettings();
        $defaultYear = now()->addYear()->format('Y');

        return view('admin.id-cards.student-index', compact('settings', 'defaultYear'));
    }

    public function studentSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $students = Student::with([
                'user:id,name,avatar',
                'program:id,name',
                'department:id,name',
                'academicSession:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('student_no', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
            })
            ->limit(8)
            ->get()
            ->map(fn ($s) => [
                'id'                  => $s->id,
                'name'                => $s->user?->name ?? '—',
                'student_no'          => $s->student_no ?? '—',
                'registration_number' => $s->registration_number,
                'program'             => $s->program?->name ?? '—',
                'department'          => $s->department?->name ?? '—',
                'current_semester'    => $s->current_semester,
                'section'             => $s->section,
                'blood_group'         => $s->blood_group,
                'batch'               => $s->batch,
                'dob'                 => $s->user?->dob ? bsDate($s->user->dob) : null,
                'photo_url'           => $s->user?->avatar_url ?? '',
                'academic_session'    => $s->academicSession?->name ?? '—',
            ]);

        return response()->json($students);
    }

    public function studentBulkList(Request $request): \Illuminate\View\View
    {
        $query = Student::query()
            ->with(['user:id,name,email,avatar', 'program:id,name', 'department:id,name', 'academicSession:id,name'])
            ->where('status', 'active')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->department_id,       fn ($q) => $q->where('department_id',       $request->department_id))
            ->when($request->program_id,          fn ($q) => $q->where('program_id',          $request->program_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester',     $request->semester));

        $students    = $query->latest('id')->paginate(30)->withQueryString();
        $programs    = Program::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $sessions    = AcademicSession::orderByDesc('id')->limit(10)->get(['id', 'name']);

        return view('admin.id-cards.student-bulk-list', compact(
            'students', 'programs', 'departments', 'sessions'
        ));
    }

    public function studentBulkPdf(Request $request)
    {
        $request->validate([
            'ids'          => 'required|array|min:1|max:100',
            'ids.*'        => 'integer|exists:students,id',
            'valid_upto'   => 'nullable|string|max:30',
            'issue_date'   => 'nullable|string|max:30',
            'barcode_type' => 'nullable|in:both,barcode,qr,none',
            'template'     => 'nullable|in:red,blue,green',
        ]);

        $cardConfig = $this->buildCardConfig($request);

        $students = Student::with(['user', 'program', 'department', 'academicSession'])
            ->whereIn('id', $request->ids)
            ->get()
            ->map(fn ($s) => $this->injectStudentPhoto($s));

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);

        $qrMap = [];
        foreach ($students as $s) {
            $qrMap[$s->id] = $this->generateQrBase64($s->student_no ?? (string) $s->id);
        }

        $pdf = Pdf::loadView('admin.id-cards.student-pdf', compact(
            'students', 'settings', 'logoBase64', 'cardConfig', 'qrMap'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('student-id-cards-' . now()->format('Ymd-His') . '.pdf');
    }

    public function studentSinglePdf(Student $student)
    {
        $student->load(['user', 'program', 'department', 'academicSession']);
        $student = $this->injectStudentPhoto($student);

        $cardConfig = [
            'valid_upto'    => '',
            'issue_date'    => bsDate(now()),
            'barcode_type'  => 'both',
            'template'      => 'red',
            'header_color'  => '#8B0000',
        ];

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $students   = collect([$student]);
        $qrMap      = [$student->id => $this->generateQrBase64($student->student_no ?? (string) $student->id)];

        $pdf = Pdf::loadView('admin.id-cards.student-pdf', compact(
            'students', 'settings', 'logoBase64', 'cardConfig', 'qrMap'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('student-id-' . $student->student_no . '.pdf');
    }

    // ── Staff ─────────────────────────────────────────────────────────────

    public function staffIndex(): \Illuminate\View\View
    {
        $settings    = $this->siteSettings();
        $defaultYear = now()->addYear()->format('Y');

        return view('admin.id-cards.staff-index', compact('settings', 'defaultYear'));
    }

    public function staffSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $staff = Staff::query()
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('staff_code', 'like', "%{$term}%")
                  ->orWhere('designation', 'like', "%{$term}%");
            })
            ->limit(8)
            ->get()
            ->map(fn ($m) => [
                'id'              => $m->id,
                'name'            => $m->name,
                'staff_code'      => $m->staff_code ?? '—',
                'designation'     => $m->designation ?? '—',
                'department'      => $m->department ?? '—',
                'email'           => $m->email,
                'phone'           => $m->phone,
                'employment_type' => $m->employment_type,
                'join_date'       => $m->join_date ? bsDate($m->join_date) : null,
                'photo_url'       => $m->photo_url,
            ]);

        return response()->json($staff);
    }

    public function staffBulkList(Request $request): \Illuminate\View\View
    {
        $query = Staff::query()
            ->where('is_active', true)
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('staff_code', 'like', "%{$term}%");
            })
            ->when($request->department,  fn ($q) => $q->where('department',        $request->department))
            ->when($request->designation, fn ($q) => $q->where('designation',       $request->designation));

        $staff = $query->orderBy('name')->paginate(30)->withQueryString();

        $departments = Staff::whereNotNull('department')->where('department', '!=', '')
            ->distinct()->orderBy('department')->pluck('department');
        $designations = Staff::whereNotNull('designation')->where('designation', '!=', '')
            ->distinct()->orderBy('designation')->pluck('designation');

        return view('admin.id-cards.staff-bulk-list', compact('staff', 'departments', 'designations'));
    }

    public function staffBulkPdf(Request $request)
    {
        $request->validate([
            'ids'          => 'required|array|min:1|max:100',
            'ids.*'        => 'integer|exists:staff,id',
            'valid_upto'   => 'nullable|string|max:30',
            'issue_date'   => 'nullable|string|max:30',
            'barcode_type' => 'nullable|in:both,barcode,qr,none',
            'template'     => 'nullable|in:red,blue,green',
        ]);

        $cardConfig = $this->buildCardConfig($request, '#1e3a5f');

        $staffList = Staff::whereIn('id', $request->ids)
            ->get()
            ->map(fn ($s) => $this->injectStaffPhoto($s));

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);

        $qrMap = [];
        foreach ($staffList as $m) {
            $qrMap[$m->id] = $this->generateQrBase64($m->staff_code ?? (string) $m->id);
        }

        $pdf = Pdf::loadView('admin.id-cards.staff-pdf', compact(
            'staffList', 'settings', 'logoBase64', 'cardConfig', 'qrMap'
        ))->setPaper('A4', 'portrait');

        return $pdf->download('staff-id-cards-' . now()->format('Ymd-His') . '.pdf');
    }

    public function staffSinglePdf(Staff $staff)
    {
        $staff = $this->injectStaffPhoto($staff);

        $cardConfig = [
            'valid_upto'   => '',
            'issue_date'   => bsDate(now()),
            'barcode_type' => 'both',
            'template'     => 'blue',
            'header_color' => '#1e3a5f',
        ];

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $staffList  = collect([$staff]);
        $qrMap      = [$staff->id => $this->generateQrBase64($staff->staff_code ?? (string) $staff->id)];

        $pdf = Pdf::loadView('admin.id-cards.staff-pdf', compact(
            'staffList', 'settings', 'logoBase64', 'cardConfig', 'qrMap'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('staff-id-' . ($staff->staff_code ?: $staff->id) . '.pdf');
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    private function siteSettings(): array
    {
        return SiteSetting::whereIn('key', [
            'college_name', 'college_affiliation', 'site_logo',
            'contact_address', 'contact_phone', 'contact_email', 'principal_name',
        ])->pluck('value', 'key')->toArray();
    }

    private function buildCardConfig(Request $request, string $defaultColor = '#8B0000'): array
    {
        $template = $request->input('template', 'red');
        $colorMap = ['red' => '#8B0000', 'blue' => '#1e3a5f', 'green' => '#14532d'];

        return [
            'valid_upto'   => $request->input('valid_upto', ''),
            'issue_date'   => $request->input('issue_date', bsDate(now())),
            'barcode_type' => $request->input('barcode_type', 'both'),
            'template'     => $template,
            'header_color' => $colorMap[$template] ?? $defaultColor,
        ];
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

    private function generateQrBase64(string $data): ?string
    {
        try {
            $url      = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($data);
            $contents = @file_get_contents($url);

            if ($contents !== false) {
                return 'data:image/png;base64,' . base64_encode($contents);
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function generateBarcodeHtml(string $data): string
    {
        $bars  = '';
        $len   = max(strlen($data), 1);

        for ($i = 0; $i < 60; $i++) {
            $charVal = ord($data[$i % $len]) + $i * 7;
            $isBlack = $charVal % 3 !== 2;
            $width   = ($charVal % 5 === 0) ? 3 : (($charVal % 4 === 0) ? 1 : 2);
            $bg      = $isBlack ? '#000000' : '#ffffff';
            $bars   .= "<td style=\"background:{$bg};width:{$width}px;padding:0;\"></td>";
        }

        return '<table style="border-collapse:collapse;height:28px;"><tr>' . $bars . '</tr></table>';
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
