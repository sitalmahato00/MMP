<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get notices - both general and department-specific
        $query = Notice::query()
            ->where(function ($q) use ($teacher) {
                $q->whereNull('department_id')
                  ->orWhere('department_id', $teacher->department_id);
            })
            ->with(['author', 'attachments'])
            ->when($request->search, fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->when($request->type, fn ($q) => $q->where('type', $request->type));

        $notices = $query->latest('created_at')->paginate(20);

        // Stats
        $totalNotices = (clone $query)->count();
        $publishedNotices = (clone $query)->where('status', 'published')->count();
        $draftNotices = (clone $query)->where('status', 'draft')->count();

        return view('teacher.notices.index', compact('notices', 'totalNotices', 'publishedNotices', 'draftNotices', 'session'));
    }

    public function show(Notice $notice)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify access
        if ($notice->department_id && $notice->department_id !== $teacher->department_id) {
            abort(403, 'Unauthorized');
        }

        $notice->load(['author', 'attachments']);

        return view('teacher.notices.show', compact('notice'));
    }
}
