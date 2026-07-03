<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\SiteSetting;
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
        $programs    = \App\Models\Program::orderBy('name')->get(['id','name']);

        return view('admin.id-cards.student-index', compact('settings', 'defaultYear', 'programs'));
    }

    public function studentSearch(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $students = Student::with([
                'user:id,name,avatar,dob,address',
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
                'address'             => $s->user?->address ?? null,
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

        return view('admin.id-cards.student-bulk-print', compact(
            'students', 'settings', 'logoBase64', 'cardConfig', 'qrMap'
        ));
    }

    public function studentSinglePdf(Student $student, Request $request)
    {
        $request->validate([
            'valid_upto'   => 'nullable|string|max:30',
            'issue_date'   => 'nullable|string|max:30',
            'barcode_type' => 'nullable|in:both,barcode,qr,none',
            'template'     => 'nullable|in:red,blue,green',
            'card_type'    => 'nullable|in:regular,premium',
        ]);

        $student->load(['user', 'program', 'department', 'academicSession']);
        $student = $this->injectStudentPhoto($student);

        $cardConfig = $this->buildCardConfig($request);

        $settings   = $this->siteSettings();
        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $qrBase64   = $this->generateQrBase64($student->student_no ?? (string) $student->id);

        // A4 page with card centered — renders cleanly in browser print dialog
        $pdf = Pdf::loadView('admin.id-cards.student-card-pdf', compact(
            'student', 'settings', 'logoBase64', 'cardConfig', 'qrBase64'
        ))->setPaper('a4', 'portrait')
          ->setOptions(['marginTop' => 0, 'marginBottom' => 0, 'marginLeft' => 0, 'marginRight' => 0]);

        return $pdf->download('student-id-' . ($student->student_no ?: $student->id) . '.pdf');
    }



    // ── Reports ───────────────────────────────────────────────────────────

    public function reports(Request $request): \Illuminate\View\View
    {
        $programs    = \App\Models\Program::orderBy('name')->get(['id', 'name']);
        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);
        $sessions    = \App\Models\AcademicSession::orderByDesc('id')->limit(10)->get(['id', 'name']);

        // Build base query
        $query = Student::with(['user:id,name,avatar,phone,dob,address', 'program:id,name', 'department:id,name', 'academicSession:id,name'])
            ->where('status', 'active')
            ->when($request->program_id,          fn ($q) => $q->where('program_id',          $request->program_id))
            ->when($request->department_id,       fn ($q) => $q->where('department_id',       $request->department_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester',    $request->semester))
            ->when($request->search, function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                          ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
                });
            });

        $totalStudents = $query->count();

        // Group stats by program
        $byProgram = Student::where('status', 'active')
            ->when($request->program_id,          fn ($q) => $q->where('program_id',          $request->program_id))
            ->when($request->department_id,       fn ($q) => $q->where('department_id',       $request->department_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->selectRaw('program_id, COUNT(*) as total')
            ->groupBy('program_id')
            ->with('program:id,name')
            ->get();

        // Group stats by department
        $byDepartment = Student::where('status', 'active')
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->selectRaw('department_id, COUNT(*) as total')
            ->groupBy('department_id')
            ->with('department:id,name')
            ->get();

        // Group stats by semester
        $bySemester = Student::where('status', 'active')
            ->when($request->program_id,    fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->selectRaw('current_semester, COUNT(*) as total')
            ->groupBy('current_semester')
            ->orderBy('current_semester')
            ->get();

        // Paginated student list
        $students = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.id-cards.reports', compact(
            'students', 'programs', 'departments', 'sessions',
            'totalStudents', 'byProgram', 'byDepartment', 'bySemester'
        ));
    }

    public function reportPrint(Request $request): \Illuminate\View\View
    {
        $settings = $this->siteSettings();

        $students = Student::with(['user:id,name,phone,dob,address', 'program:id,name', 'department:id,name'])
            ->where('status', 'active')
            ->when($request->program_id,          fn ($q) => $q->where('program_id',          $request->program_id))
            ->when($request->department_id,       fn ($q) => $q->where('department_id',       $request->department_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester',    $request->semester))
            ->when($request->search, function ($q) use ($request) {
                $term = trim($request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                          ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('student_no')
            ->get();

        $logoBase64 = $this->toBase64($settings['site_logo'] ?? null);
        $printDate  = bsDate(now()->format('Y-m-d'));

        return view('admin.id-cards.report-print', compact('students', 'settings', 'logoBase64', 'printDate'));
    }

    public function reportExport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $students = Student::with(['user:id,name,phone,dob,address', 'program:id,name', 'department:id,name', 'academicSession:id,name'])
            ->where('status', 'active')
            ->when($request->program_id,          fn ($q) => $q->where('program_id',          $request->program_id))
            ->when($request->department_id,       fn ($q) => $q->where('department_id',       $request->department_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester',    $request->semester))
            ->orderBy('student_no')
            ->get();

        $filename = 'id-card-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, ['#', 'Name', 'Student ID', 'Registration No.', 'Program', 'Department', 'Semester', 'Academic Session', 'DOB (BS)', 'Address']);
            foreach ($students as $i => $s) {
                fputcsv($fp, [
                    $i + 1,
                    $s->user?->name ?? '—',
                    $s->student_no ?? '—',
                    $s->registration_number ?? '—',
                    $s->program?->name ?? '—',
                    $s->department?->name ?? '—',
                    $s->current_semester ? 'Semester ' . $s->current_semester : '—',
                    $s->academicSession?->name ?? '—',
                    $s->user?->dob ? bsDate($s->user->dob) : '—',
                    $s->user?->address ?? '—',
                ]);
            }
            fclose($fp);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    private function siteSettings(): array
    {
        return SiteSetting::whereIn('key', [
            'college_name', 'college_affiliation', 'site_logo',
            'contact_address', 'contact_phone', 'contact_email', 'principal_name', 'principal_signature',
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
            'card_type'    => $request->input('card_type', 'regular'),
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

        // Correct EXIF orientation for JPEG images so DomPDF renders upright
        if (in_array($mime, ['image/jpeg', 'image/jpg'], true) && function_exists('imagecreatefromjpeg')) {
            $corrected = $this->correctImageOrientation($path);
            if ($corrected !== null) {
                return 'data:image/jpeg;base64,' . base64_encode($corrected);
            }
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function correctImageOrientation(string $path): ?string
    {
        try {
            $orientation = 1;
            if (function_exists('exif_read_data')) {
                $exif        = @exif_read_data($path);
                $orientation = $exif['Orientation'] ?? 1;
            }

            if ($orientation === 1) {
                return null; // already upright — no re-encode needed
            }

            $img = @imagecreatefromjpeg($path);
            if (! $img) {
                return null;
            }

            $img = match ($orientation) {
                3       => imagerotate($img, 180, 0),
                6       => imagerotate($img, -90, 0),
                8       => imagerotate($img, 90, 0),
                default => $img,
            };

            ob_start();
            imagejpeg($img, null, 95);
            $data = ob_get_clean();
            imagedestroy($img);

            return $data ?: null;
        } catch (\Throwable) {
            return null;
        }
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


}
