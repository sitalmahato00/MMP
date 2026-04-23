<?php

namespace App\Http\Controllers\HOD;

use App\Models\Notice;
use App\Models\Program;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * HOD notice management (department-scoped).
 * 
 * HODs can manage notices for their department only.
 */
class NoticeController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get program IDs for this department
        $programIds = Program::where('department_id', $deptId)->pluck('id')->toArray();

        // Get notices - ALL notices + department-specific notices (both general and exam types)
        $query = Notice::where(function ($q) use ($deptId, $programIds) {
                // Show ALL notices OR department/program-specific notices
                $q->whereNull('department_id')  // All general notices
                  ->orWhere('department_id', $deptId)  // Department-specific notices
                  ->orWhereIn('program_id', $programIds);  // Program-specific notices for this department
            })
            ->whereIn('type', ['general', 'exam', 'department', 'program', 'academic', 'event'])  // Include both general and exam types
            ->with([
                'author:id,name',
                'department:id,name',
                'program:id,name'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
            })
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'published') {
                    $q->where('is_published', true);
                } elseif ($request->status === 'draft') {
                    $q->where('is_published', false);
                }
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id));

        $notices = (clone $query)
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalNotices = (clone $query)->count();
        $publishedNotices = (clone $query)->where('is_published', true)->count();
        $draftNotices = (clone $query)->where('is_published', false)->count();
        $departmentNotices = (clone $query)->where('type', 'department')->where('department_id', $deptId)->count();

        // Programs for filter
        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.notices.index', compact(
            'notices', 'department', 'programs',
            'totalNotices', 'publishedNotices', 'draftNotices', 'departmentNotices'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.notices.create', compact('department', 'programs'));
    }

    // ── Store ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,department,program',
            'program_id' => 'nullable|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:8',
            'attachment' => 'nullable|file|max:10240', // 10MB
            'is_published' => 'nullable|boolean',
        ]);

        // If program-specific, verify program belongs to department
        if ($data['type'] === 'program' && $data['program_id']) {
            $program = Program::where('id', $data['program_id'])
                ->where('department_id', $deptId)
                ->firstOrFail();
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('notices', 'public');
        }

        $notice = Notice::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . time(),
            'content' => $data['content'],
            'type' => $data['type'],
            'department_id' => $data['type'] === 'department' ? $deptId : null,
            'program_id' => $data['program_id'] ?? null,
            'semester' => $data['semester'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'created_by' => auth()->id(),
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
        ]);

        // Clear public caches so changes appear immediately
        PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Request $request, Notice $notice)
    {
        // Verify access to notice
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        if ($notice->type === 'department' && $notice->department_id !== $deptId) {
            abort(403, 'Unauthorized access to notice.');
        }

        if ($notice->type === 'program' && $notice->program && $notice->program->department_id !== $deptId) {
            abort(403, 'Unauthorized access to notice.');
        }

        $notice->load(['author:id,name', 'department:id,name', 'program:id,name']);

        return view('hod.notices.show', compact('notice', 'department'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Notice $notice)
    {
        // Verify access to notice (only notices created by HOD)
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Check if notice belongs to this department and was created by current user
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
        }

        // For department notices, verify department match
        if ($notice->type === 'department' && $notice->department_id !== $deptId) {
            abort(403, 'You can only edit notices from your department.');
        }

        // For program notices, verify program belongs to department
        if ($notice->type === 'program' && $notice->program && $notice->program->department_id !== $deptId) {
            abort(403, 'You can only edit notices from your department.');
        }

        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.notices.edit', compact('notice', 'department', 'programs'));
    }

    // ── Update ─────────────────────────────────────────────────────────────
    public function update(Request $request, Notice $notice)
    {
        // Verify access to notice
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Check if notice was created by current user
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
        }

        // For department notices, verify department match
        if ($notice->type === 'department' && $notice->department_id !== $deptId) {
            abort(403, 'You can only edit notices from your department.');
        }

        // For program notices, verify program belongs to department
        if ($notice->type === 'program' && $notice->program && $notice->program->department_id !== $deptId) {
            abort(403, 'You can only edit notices from your department.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:department,program',
            'program_id' => 'nullable|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:8',
            'attachment' => 'nullable|file|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        // If program-specific, verify program belongs to department
        if ($data['type'] === 'program' && $data['program_id']) {
            $program = Program::where('id', $data['program_id'])
                ->where('department_id', $deptId)
                ->firstOrFail();
        }

        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
                Storage::disk('public')->delete($notice->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('notices', 'public');
        } else {
            unset($data['attachment']);
        }

        $notice->update([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . time(),
            'content' => $data['content'],
            'type' => $data['type'],
            'department_id' => $data['type'] === 'department' ? $deptId : null,
            'program_id' => $data['program_id'] ?? null,
            'semester' => $data['semester'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) && !$notice->published_at ? now() : $notice->published_at,
        ] + (isset($data['attachment']) ? ['attachment' => $data['attachment']] : []));

        // Clear public caches so changes appear immediately
        PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, Notice $notice)
    {
        // Verify access to notice
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Check if notice was created by current user
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only delete notices you created.');
        }

        // For department notices, verify department match
        if ($notice->type === 'department' && $notice->department_id !== $deptId) {
            abort(403, 'You can only delete notices from your department.');
        }

        // For program notices, verify program belongs to department
        if ($notice->type === 'program' && $notice->program && $notice->program->department_id !== $deptId) {
            abort(403, 'You can only delete notices from your department.');
        }

        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }

        $notice->delete();

        // Clear public caches so changes appear immediately
        PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }
}