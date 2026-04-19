<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NepaliDateHelper;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffDocument;
use App\Services\PublicDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query()->with(['documents'])->withCount('documents');

        if ($search = trim((string) $request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->string('department')->toString());
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->string('designation')->toString());
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->string('employment_status')->toString());
        }

        $this->applyJoinedYearFilter($query, $request->string('joined_year')->toString());

        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $staff = $query->orderBy('order')->orderBy('name')->paginate(12)->withQueryString();

        $totalStaff = Staff::count();
        $activeStaff = Staff::where('employment_status', 'active')->count();
        $resignedStaff = Staff::where('employment_status', 'resigned')->count();
        $addedThisYear = Staff::whereYear('created_at', now()->year)->count();

        $topDepartment = Staff::query()
            ->select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->groupBy('department')
            ->orderByDesc('total')
            ->first();

        $departments = Staff::query()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $designations = Staff::query()
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation');

        $joinedYears = $this->uniqueBsYears(Staff::query());

        return view('admin.staff.index', compact(
            'staff', 'departments', 'designations', 'joinedYears',
            'totalStaff', 'activeStaff', 'resignedStaff', 'addedThisYear', 'topDepartment'
        ));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateStaff($request);
        $validated = $this->normalizeStaffDates($validated);

        DB::transaction(function () use ($request, $validated) {
            $payload = $this->buildPayload($request, $validated);
            Staff::create($payload);
        });

        $this->flushPublicStaffCaches();

        return redirect()->route('admin.staff.index')->with('success', 'Staff member added.');
    }

    public function show(Staff $staff)
    {
        $staff->load(['documents', 'attendanceRecords']);

        $currentMonth = now()->startOfMonth();
        $monthAttendance = $staff->attendanceRecords()
            ->whereDate('attendance_date', '>=', $currentMonth)
            ->latest('attendance_date')
            ->get();

        $attendanceSummary = [
            'present' => $monthAttendance->where('status', 'present')->count(),
            'late' => $monthAttendance->where('status', 'late')->count(),
            'leave' => $monthAttendance->where('status', 'leave')->count(),
            'absent' => $monthAttendance->where('status', 'absent')->count(),
        ];

        $publicDocs = $staff->documents->where('is_public', true)->values();

        return view('admin.staff.show', compact('staff', 'attendanceSummary', 'monthAttendance', 'publicDocs'));
    }

    public function edit(Staff $staff)
    {
        return view('admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $this->validateStaff($request, $staff);
        $validated = $this->normalizeStaffDates($validated);

        DB::transaction(function () use ($request, $validated, $staff) {
            $payload = $this->buildPayload($request, $validated, $staff);
            $staff->update($payload);
        });

        $this->flushPublicStaffCaches();

        return redirect()->route('admin.staff.show', $staff)->with('success', 'Staff updated.');
    }

    public function destroy(Staff $staff)
    {
        DB::transaction(function () use ($staff) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }

            foreach ($staff->documents as $document) {
                if (Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }

            $staff->attendanceRecords()->delete();
            $staff->delete();
        });

        $this->flushPublicStaffCaches();

        return redirect()->route('admin.staff.index')->with('success', 'Staff removed.');
    }

    public function documents(Staff $staff)
    {
        $staff->load(['documents']);

        return view('admin.staff.documents', compact('staff'));
    }

    public function storeDocument(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'issued_at' => ['nullable', 'string', 'max:12', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'notes' => 'nullable|string|max:2000',
            'is_public' => 'nullable|boolean',
            'file' => 'required|file|max:10240',
        ]);

        $validated['issued_at'] = $this->convertBsDate($validated['issued_at'] ?? null, 'issued_at');

        $path = $request->file('file')->store("staff/{$staff->id}/documents", 'public');

        $staff->documents()->create([
            'document_type' => $validated['document_type'],
            'label' => $validated['label'],
            'file_path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'issued_at' => $validated['issued_at'],
            'is_public' => $request->boolean('is_public'),
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->flushPublicStaffCaches();

        return back()->with('success', 'Document uploaded.');
    }

    public function destroyDocument(Staff $staff, StaffDocument $document)
    {
        abort_unless($document->staff_id === $staff->id, 404);

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $this->flushPublicStaffCaches();

        return back()->with('success', 'Document deleted.');
    }

    public function updateStatus(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'employment_status' => ['required', Rule::in(['active', 'leave', 'resigned'])],
        ]);

        $staff->update([
            'employment_status' => $validated['employment_status'],
            'is_active' => $validated['employment_status'] !== 'resigned',
            'end_date' => $validated['employment_status'] === 'resigned' && ! $staff->end_date ? now()->toDateString() : $staff->end_date,
        ]);

        $this->flushPublicStaffCaches();

        return back()->with('success', 'Staff status updated.');
    }

    public function toggleFeatured(Staff $staff)
    {
        $staff->update(['featured' => ! $staff->featured]);

        $this->flushPublicStaffCaches();

        return back()->with('success', $staff->featured ? 'Staff marked as featured.' : 'Featured removed.');
    }

    public function togglePublic(Staff $staff)
    {
        $staff->update(['public_visible' => ! $staff->public_visible]);

        $this->flushPublicStaffCaches();

        return back()->with('success', $staff->public_visible ? 'Staff made public.' : 'Staff hidden from public directory.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getRealPath(), 'r');
        $header = null;
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (! $header) {
                $header = array_map(fn ($column) => trim((string) $column), $row);
                continue;
            }

            $data = array_combine($header, $row) ?: [];

            if (blank($data['name'] ?? null)) {
                continue;
            }

            $payload = $this->normalizeImportRow($data);

            $lookup = array_filter([
                'staff_code' => $payload['staff_code'] ?? null,
                'email' => $payload['email'] ?? null,
            ], fn ($value) => filled($value));

            if ($lookup === []) {
                continue;
            }

            Staff::updateOrCreate($lookup, $payload);
            $imported++;
        }

        fclose($handle);

        $this->flushPublicStaffCaches();

        return back()->with('success', "{$imported} staff records imported.");
    }

    public function exportCsv(Request $request)
    {
        $staff = $this->filteredQuery($request)->get();

        $filename = 'staff-export-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($staff) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'staff_code', 'name', 'designation', 'department', 'email', 'phone', 'address', 'dob', 'gender',
                'employment_type', 'employment_status', 'join_date', 'end_date', 'salary_amount', 'assigned_roles',
                'responsibilities', 'featured', 'public_visible', 'show_email_public', 'show_phone_public', 'bio',
            ]);

            foreach ($staff as $member) {
                fputcsv($output, [
                    $member->staff_code,
                    $member->name,
                    $member->designation,
                    $member->department,
                    $member->email,
                    $member->phone,
                    $member->address,
                    bsDate($member->dob),
                    $member->gender,
                    $member->employment_type,
                    $member->employment_status,
                    bsDate($member->join_date),
                    bsDate($member->end_date),
                    $member->salary_amount,
                    implode(' | ', $member->assigned_roles ?? []),
                    implode(' | ', $member->responsibilities ?? []),
                    $member->featured ? '1' : '0',
                    $member->public_visible ? '1' : '0',
                    $member->show_email_public ? '1' : '0',
                    $member->show_phone_public ? '1' : '0',
                    $member->bio,
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $staff = $this->filteredQuery($request)->get();
        $pdf = Pdf::loadView('admin.staff.export-pdf', compact('staff'));

        return $pdf->download('staff-export-' . now()->format('Y-m-d-His') . '.pdf');
    }

    private function validateStaff(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'staff_code' => ['required', 'string', 'max:50', Rule::unique('staff', 'staff_code')->ignore($staff?->id)],
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('staff', 'email')->ignore($staff?->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'dob' => ['nullable', 'string', 'max:12', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'employment_type' => 'nullable|in:full_time,part_time,contract,temporary',
            'employment_status' => 'required|in:active,leave,resigned',
            'join_date' => ['nullable', 'string', 'max:12', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'end_date' => ['nullable', 'string', 'max:12', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'salary_amount' => 'nullable|numeric|min:0',
            'working_schedule_label' => 'nullable|string|max:120',
            'working_schedule_days' => 'nullable|string|max:255',
            'working_schedule_start' => 'nullable|date_format:H:i',
            'working_schedule_end' => 'nullable|date_format:H:i',
            'assigned_roles' => 'nullable|string|max:1000',
            'responsibilities' => 'nullable|string|max:2000',
            'bio' => 'nullable|string|max:4000',
            'photo' => 'nullable|image|max:2048',
            'public_visible' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'show_email_public' => 'nullable|boolean',
            'show_phone_public' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
    }

    private function buildPayload(Request $request, array $validated, ?Staff $staff = null): array
    {
        $payload = [
            'staff_code' => $validated['staff_code'],
            'name' => $validated['name'],
            'designation' => $validated['designation'],
            'department' => $validated['department'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'employment_status' => $validated['employment_status'],
            'join_date' => $validated['join_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'salary_amount' => $validated['salary_amount'] ?? null,
            'working_schedule' => $this->buildWorkingSchedule($validated),
            'assigned_roles' => $this->normalizeDelimitedValues($validated['assigned_roles'] ?? ''),
            'responsibilities' => $this->normalizeDelimitedValues($validated['responsibilities'] ?? ''),
            'bio' => $validated['bio'] ?? null,
            'public_visible' => $request->boolean('public_visible'),
            'featured' => $request->boolean('featured'),
            'show_email_public' => $request->boolean('show_email_public'),
            'show_phone_public' => $request->boolean('show_phone_public'),
            'order' => (int) ($validated['order'] ?? $staff?->order ?? 0),
            'is_active' => $validated['employment_status'] !== 'resigned',
        ];

        if ($request->hasFile('photo')) {
            if ($staff?->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }

            $payload['photo'] = $request->file('photo')->store('staff', 'public');
        }

        return $payload;
    }

    private function buildWorkingSchedule(array $validated): ?array
    {
        $hasSchedule = filled($validated['working_schedule_label'] ?? null)
            || filled($validated['working_schedule_days'] ?? null)
            || filled($validated['working_schedule_start'] ?? null)
            || filled($validated['working_schedule_end'] ?? null);

        if (! $hasSchedule) {
            return null;
        }

        return [
            'label' => $validated['working_schedule_label'] ?? null,
            'days' => $this->normalizeDelimitedValues($validated['working_schedule_days'] ?? ''),
            'start' => $validated['working_schedule_start'] ?? null,
            'end' => $validated['working_schedule_end'] ?? null,
        ];
    }

    private function normalizeDelimitedValues(?string $value): array
    {
        return array_values(array_filter(array_map(
            static fn ($item) => trim((string) $item),
            explode(',', (string) $value)
        )));
    }

    private function normalizeStaffDates(array $validated): array
    {
        $validated['dob'] = $this->convertBsDate($validated['dob'] ?? null, 'dob');
        $validated['join_date'] = $this->convertBsDate($validated['join_date'] ?? null, 'join_date');
        $validated['end_date'] = $this->convertBsDate($validated['end_date'] ?? null, 'end_date');

        if ($validated['join_date'] && $validated['end_date'] && $validated['end_date']->lt($validated['join_date'])) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be after or equal to the join date.',
            ]);
        }

        return $validated;
    }

    private function convertBsDate(?string $value, string $field): ?Carbon
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === null || $value === '') {
            return null;
        }

        $converted = NepaliDateHelper::toAD($value);

        if (! $converted) {
            throw ValidationException::withMessages([
                $field => 'Enter a valid BS date in YYYY-MM-DD format.',
            ]);
        }

        return $converted;
    }

    private function normalizeImportRow(array $data): array
    {
        $employmentStatus = strtolower(trim((string) ($data['employment_status'] ?? 'active')));

        return [
            'staff_code' => trim((string) ($data['staff_code'] ?? '')) ?: null,
            'name' => trim((string) ($data['name'] ?? '')),
            'designation' => trim((string) ($data['designation'] ?? '')) ?: null,
            'department' => trim((string) ($data['department'] ?? '')) ?: null,
            'email' => trim((string) ($data['email'] ?? '')) ?: null,
            'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
            'address' => trim((string) ($data['address'] ?? '')) ?: null,
            'dob' => $this->normalizeImportedDate($data['dob'] ?? null),
            'gender' => trim((string) ($data['gender'] ?? '')) ?: null,
            'employment_type' => trim((string) ($data['employment_type'] ?? '')) ?: null,
            'employment_status' => in_array($employmentStatus, ['active', 'leave', 'resigned'], true) ? $employmentStatus : 'active',
            'join_date' => $this->normalizeImportedDate($data['join_date'] ?? null),
            'end_date' => $this->normalizeImportedDate($data['end_date'] ?? null),
            'salary_amount' => trim((string) ($data['salary_amount'] ?? '')) ?: null,
            'working_schedule' => $this->buildWorkingSchedule([
                'working_schedule_label' => $data['working_schedule_label'] ?? null,
                'working_schedule_days' => $data['working_schedule_days'] ?? null,
                'working_schedule_start' => $data['working_schedule_start'] ?? null,
                'working_schedule_end' => $data['working_schedule_end'] ?? null,
            ]),
            'assigned_roles' => $this->normalizeDelimitedValues($data['assigned_roles'] ?? ''),
            'responsibilities' => $this->normalizeDelimitedValues($data['responsibilities'] ?? ''),
            'bio' => trim((string) ($data['bio'] ?? '')) ?: null,
            'public_visible' => filter_var($data['public_visible'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'featured' => filter_var($data['featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'show_email_public' => filter_var($data['show_email_public'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'show_phone_public' => filter_var($data['show_phone_public'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'order' => (int) ($data['order'] ?? 0),
            'is_active' => $employmentStatus !== 'resigned',
        ];
    }

    private function filteredQuery(Request $request, bool $publicOnly = false)
    {
        $query = Staff::query()->with(['documents']);

        if ($publicOnly) {
            $query->publicVisible();
        }

        if ($search = trim((string) $request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->string('department')->toString());
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->string('designation')->toString());
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->string('employment_status')->toString());
        }

        $this->applyJoinedYearFilter($query, $request->string('joined_year')->toString());

        if ($request->filled('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        return $query->orderBy('order')->orderBy('name');
    }

    private function flushPublicStaffCaches(): void
    {
        app(PublicDataService::class)->bustStaffCaches();
    }

    private function applyJoinedYearFilter($query, ?string $joinedYear): void
    {
        $joinedYear = trim((string) $joinedYear);

        if ($joinedYear === '') {
            return;
        }

        $range = $this->bsYearRange($joinedYear);

        if (! $range) {
            return;
        }

        [$startDate, $endDate] = $range;

        $query->whereDate('join_date', '>=', $startDate->toDateString())
            ->whereDate('join_date', '<', $endDate->toDateString());
    }

    private function bsYearRange(?string $year): ?array
    {
        $year = trim((string) $year);

        if (! preg_match('/^\d{4}$/', $year)) {
            return null;
        }

        $startDate = NepaliDateHelper::toAD("{$year}-01-01");
        $endDate = NepaliDateHelper::toAD(((int) $year + 1) . '-01-01');

        if (! $startDate || ! $endDate) {
            return null;
        }

        return [$startDate, $endDate];
    }

    private function uniqueBsYears($query)
    {
        return $query->whereNotNull('join_date')
            ->get(['join_date'])
            ->map(fn ($member) => bsDate($member->join_date, 'Y'))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function normalizeImportedDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $bsDate = NepaliDateHelper::toAD($value);

        if ($bsDate) {
            return $bsDate->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
