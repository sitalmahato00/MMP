<?php

namespace App\Modules\Parent\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Program;
use App\Modules\CMS\Models\Notice;
use App\Modules\Department\Models\Department;
use Illuminate\Http\Request;

class NewsEventsController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::published()
            ->forNewsEvents()
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

        $items = $query->latest('published_at')->paginate(15);

        // Stats
        $stats = [
            'total' => Notice::published()->forNewsEvents()->count(),
            'news' => Notice::published()->where('type', 'news')->count(),
            'events' => Notice::published()->where('type', 'event')->count(),
        ];

        return view('parent.news-events', compact('items', 'stats'));
    }

    public function show(Notice $notice)
    {
        if (!$notice->is_published || !in_array($notice->type, ['news', 'event'])) {
            abort(404);
        }

        $notice->load(['author', 'department', 'program', 'attachments']);

        return view('parent.news-events-show', compact('notice'));
    }
}
