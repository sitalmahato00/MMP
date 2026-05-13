<?php

namespace App\Modules\HOD\Controllers;


/**
 * HOD notice management (department-scoped).
 * 
 * HODs can manage notices for their department only.
 */
use App\Modules\Academic\Models\Program;
use App\Modules\CMS\Models\Notice;
use App\Modules\Department\Models\Department;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticeController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get program IDs for this department
        $programIds = Program::where('department_id', $deptId)->pluck('id')->toArray();

        $query = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNoticeBoard()
            ->with([
                'author:id,name',
                'department:id,name',
                'program:id,name'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($searchQuery) use ($term) {
                    $searchQuery->where('title', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%");
                });
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

    public function newsEvents(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $items = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNewsEvents()
            ->where('is_published', true)
            ->with(['author:id,name', 'department:id,name', 'program:id,name', 'attachments'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->search);
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when(in_array($request->string('type')->toString(), ['news', 'event'], true), function ($q) use ($request) {
                $q->where('type', $request->string('type')->toString());
            })
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('hod.news-events.index', compact('items', 'department'));
    }

    public function showNewsEvent(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $newsEvent = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNewsEvents()
            ->where('is_published', true)
            ->with(['author:id,name', 'department:id,name', 'program:id,name', 'attachments'])
            ->findOrFail($notice->id);

        return view('hod.news-events.show', compact('newsEvent', 'department'));
    }

    public function createNewsEvent(Request $request)
    {
        $department = $this->currentDepartment($request);
        $programs = Program::where('department_id', $department->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.news-events.create', compact('department', 'programs'));
    }

    public function storeNewsEvent(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:news,event',
            'program_id' => 'nullable|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:8',
            'attachment' => 'nullable|file|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        if ($data['program_id'] ?? null) {
            Program::where('id', $data['program_id'])
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
            'department_id' => $deptId,
            'program_id' => $data['program_id'] ?? null,
            'semester' => $data['semester'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'created_by' => auth()->id(),
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
        ]);
        app(\App\Services\PortalNotificationService::class)->dispatchNoticePublished($notice);

        PublicDataService::invalidate('*');

        return redirect()->route('hod.news-events.index')
            ->with('success', 'News/event created successfully.');
    }

    public function editNewsEvent(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit items you created.');
        }

        if (! in_array($notice->type, ['news', 'event'], true) || $notice->department_id !== $deptId) {
            abort(403, 'You can only edit news/events from your department.');
        }

        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.news-events.edit', compact('notice', 'department', 'programs'));
    }

    public function updateNewsEvent(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit items you created.');
        }

        if (! in_array($notice->type, ['news', 'event'], true) || $notice->department_id !== $deptId) {
            abort(403, 'You can only edit news/events from your department.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:news,event',
            'program_id' => 'nullable|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:8',
            'attachment' => 'nullable|file|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        if ($data['program_id'] ?? null) {
            Program::where('id', $data['program_id'])
                ->where('department_id', $deptId)
                ->firstOrFail();
        }

        if ($request->hasFile('attachment')) {
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
            'department_id' => $deptId,
            'program_id' => $data['program_id'] ?? null,
            'semester' => $data['semester'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) && ! $notice->published_at ? now() : $notice->published_at,
        ] + (isset($data['attachment']) ? ['attachment' => $data['attachment']] : []));
        app(\App\Services\PortalNotificationService::class)->dispatchNoticePublished($notice);

        PublicDataService::invalidate('*');

        return redirect()->route('hod.news-events.index')
            ->with('success', 'News/event updated successfully.');
    }

    public function destroyNewsEvent(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only delete items you created.');
        }

        if (! in_array($notice->type, ['news', 'event'], true) || $notice->department_id !== $deptId) {
            abort(403, 'You can only delete news/events from your department.');
        }

        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }

        $notice->delete();
        PublicDataService::invalidate('*');

        return redirect()->route('hod.news-events.index')
            ->with('success', 'News/event deleted successfully.');
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
        app(\App\Services\PortalNotificationService::class)->dispatchNoticePublished($notice);

        // Clear public caches so changes appear immediately
        PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $notice = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNoticeBoard()
            ->with(['author:id,name', 'department:id,name', 'program:id,name'])
            ->findOrFail($notice->id);

        return view('hod.notices.show', compact('notice', 'department'));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $notice = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNoticeBoard()
            ->with('program:id,department_id,name')
            ->findOrFail($notice->id);

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
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
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $notice = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNoticeBoard()
            ->with('program:id,department_id,name')
            ->findOrFail($notice->id);

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
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
        app(\App\Services\PortalNotificationService::class)->dispatchNoticePublished($notice);

        // Clear public caches so changes appear immediately
        PublicDataService::invalidate('*');

        return redirect()
            ->route('hod.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, Notice $notice)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;
        $programIds = Program::where('department_id', $deptId)->pluck('id')->all();

        $notice = Notice::query()
            ->visibleToDepartmentContext($deptId, $programIds)
            ->forNoticeBoard()
            ->with('program:id,department_id,name')
            ->findOrFail($notice->id);

        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only delete notices you created.');
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
