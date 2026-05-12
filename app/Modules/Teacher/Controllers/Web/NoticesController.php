<?php

namespace App\Modules\Teacher\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Notice;
use App\Models\Program;
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

        $programIds = Program::where('department_id', $teacher->department_id)->pluck('id')->all();

        $query = Notice::query()
            ->where('is_published', true)
            ->visibleToDepartmentContext($teacher->department_id, $programIds)
            ->forNoticeBoard()
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

        $programIds = Program::where('department_id', $teacher->department_id)->pluck('id')->all();
        $notice = Notice::with(['author', 'attachments', 'department', 'program'])
            ->visibleToDepartmentContext($teacher->department_id, $programIds)
            ->forNoticeBoard()
            ->where('is_published', true)
            ->findOrFail($notice->id);

        return view('teacher.notices.show', compact('notice'));
    }

    public function newsEvents(Request $request)
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        $programIds = Program::where('department_id', $teacher->department_id)->pluck('id')->all();

        $items = Notice::with(['author', 'attachments', 'department', 'program'])
            ->visibleToDepartmentContext($teacher->department_id, $programIds)
            ->forNewsEvents()
            ->where('is_published', true)
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

        return view('teacher.news-events.index', compact('items'));
    }

    public function showNewsEvent(Notice $notice)
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        $programIds = Program::where('department_id', $teacher->department_id)->pluck('id')->all();

        $newsEvent = Notice::with(['author', 'attachments', 'department', 'program'])
            ->visibleToDepartmentContext($teacher->department_id, $programIds)
            ->forNewsEvents()
            ->where('is_published', true)
            ->findOrFail($notice->id);

        return view('teacher.news-events.show', ['notice' => $newsEvent]);
    }
}
