<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::published()
            ->forNoticeBoard()
            ->with(['author', 'department', 'program', 'attachments']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notices = $query->latest('published_at')->paginate(15);

        // Stats
        $stats = [
            'total' => Notice::published()->forNoticeBoard()->count(),
            'general' => Notice::published()->where('type', 'general')->count(),
            'exam' => Notice::published()->where('type', 'exam')->count(),
            'academic' => Notice::published()->where('type', 'academic')->count(),
        ];

        return view('parent.notices', compact('notices', 'stats'));
    }

    public function show(Notice $notice)
    {
        if (!$notice->is_published) {
            abort(404);
        }

        $notice->load(['author', 'department', 'program', 'attachments']);

        return view('parent.notices-show', compact('notice'));
    }
}
