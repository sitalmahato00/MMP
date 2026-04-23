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

        // Get notices - ALL published notices + department-specific notices (both general and exam types)
        $query = Notice::query()
            ->where('is_published', true)  // Only show published notices to teachers
            ->where(function ($q) use ($teacher) {
                // Show ALL published notices OR department-specific notices
                $q->whereNull('department_id')  // All general notices
                  ->orWhere('department_id', $teacher->department_id);  // Department-specific notices
            })
            ->whereIn('type', ['general', 'exam', 'department', 'program', 'academic', 'event'])  // Include both general and exam types
            ->with(['author', 'attachments'])
            ->when($request->search, fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->when($request->type, fn ($q) => $q->where('type', $request->type));

        $notices = $query->latest('created_at')->paginate(20)->withQueryString();

        // Stats
        $totalNotices = (clone $query)->count();

        return view('teacher.notices.index', compact('notices', 'totalNotices', 'session'));
    }

    public function show(Notice $notice)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify access - only published notices
        if (!$notice->is_published) {
            abort(404, 'Notice not found');
        }

        // Verify department access
        if ($notice->department_id && $notice->department_id !== $teacher->department_id) {
            abort(403, 'Unauthorized');
        }

        $notice->load(['author', 'attachments']);

        return view('teacher.notices.show', compact('notice'));
    }
}
