<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NepaliDateHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    private const NOTICE_TYPES = ['general', 'department', 'teachers', 'exam'];
    private const NEWS_EVENT_TYPES = ['news', 'event'];

    public function index(Request $request)
    {
        return $this->renderWorkspaceIndex($request, false);
    }

    public function newsEventsIndex(Request $request)
    {
        return $this->renderWorkspaceIndex($request, true);
    }

    public function show(Request $request, Notice $notice)
    {
        return $this->showWorkspaceItem($request, $notice, false);
    }

    public function showNewsEvent(Request $request, Notice $notice)
    {
        return $this->showWorkspaceItem($request, $notice, true);
    }

    public function create()
    {
        $workspace = $this->workspace(false);
        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.notices.create', compact('workspace', 'departments'));
    }

    public function createNewsEvent()
    {
        $workspace = $this->workspace(true);
        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.notices.create', compact('workspace', 'departments'));
    }

    public function store(Request $request)
    {
        return $this->storeWorkspaceItem($request, false);
    }

    public function storeNewsEvent(Request $request)
    {
        return $this->storeWorkspaceItem($request, true);
    }

    public function edit(Notice $notice)
    {
        return $this->editWorkspaceItem($notice, false);
    }

    public function editNewsEvent(Notice $notice)
    {
        return $this->editWorkspaceItem($notice, true);
    }

    public function update(Request $request, Notice $notice)
    {
        return $this->updateWorkspaceItem($request, $notice, false);
    }

    public function updateNewsEvent(Request $request, Notice $notice)
    {
        return $this->updateWorkspaceItem($request, $notice, true);
    }

    public function destroy(Notice $notice)
    {
        return $this->destroyWorkspaceItem($notice, false);
    }

    public function destroyNewsEvent(Notice $notice)
    {
        return $this->destroyWorkspaceItem($notice, true);
    }

    private function renderWorkspaceIndex(Request $request, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);
        $filters = $request->only(['search', 'type', 'department_id', 'status', 'date_from', 'date_to']);

        $notices = $this->workspaceQuery($filters, $isNewsEvents)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = $this->workspaceStats($isNewsEvents);
        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);
        $noticeDrawerPayload = $notices->getCollection()
            ->map(fn (Notice $notice) => $this->buildNoticePayload($notice, $workspace['route_prefix']))
            ->values();

        return view('admin.notices.index', compact(
            'notices',
            'stats',
            'departments',
            'filters',
            'noticeDrawerPayload',
            'workspace',
        ));
    }

    private function showWorkspaceItem(Request $request, Notice $notice, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);

        $this->ensureWorkspaceType($notice, $isNewsEvents);

        $notice->loadMissing(['author:id,name,avatar', 'department:id,name,code', 'attachments'])
            ->loadCount('attachments');

        $payload = $this->buildNoticePayload($notice, $workspace['route_prefix']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return view('admin.notices.show', compact('notice', 'payload', 'workspace'));
    }

    private function storeWorkspaceItem(Request $request, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);
        $data = $this->validatedNoticeData($request, $isNewsEvents);

        $notice = Notice::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'department_id' => $data['department_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'created_by' => auth()->id(),
            'slug' => Str::slug($data['title']) . '-' . uniqid(),
            'is_published' => true,
        ]);

        $this->storeAttachments($request, $notice);

        PublicDataService::invalidate('*');

        return redirect()->route($workspace['index_route'])
            ->with('success', $workspace['create_success']);
    }

    private function editWorkspaceItem(Notice $notice, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);

        $this->ensureOwnership($notice);
        $this->ensureWorkspaceType($notice, $isNewsEvents);

        $notice->load('attachments');
        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);

        return view('admin.notices.edit', compact('notice', 'workspace', 'departments'));
    }

    private function updateWorkspaceItem(Request $request, Notice $notice, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);

        $this->ensureOwnership($notice);
        $this->ensureWorkspaceType($notice, $isNewsEvents);

        $data = $this->validatedNoticeData($request, $isNewsEvents);

        $notice->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'],
            'department_id' => $data['department_id'] ?? null,
            'published_at' => $data['published_at'] ?? null,
        ]);

        $this->removeSelectedAttachments($request, $notice);
        $this->storeAttachments($request, $notice);

        PublicDataService::invalidate('*');

        return redirect()->route($workspace['index_route'])
            ->with('success', $workspace['update_success']);
    }

    private function destroyWorkspaceItem(Notice $notice, bool $isNewsEvents)
    {
        $workspace = $this->workspace($isNewsEvents);

        $this->ensureOwnership($notice);
        $this->ensureWorkspaceType($notice, $isNewsEvents);

        $notice->loadMissing('attachments');

        foreach ($notice->attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }

        $notice->delete();

        PublicDataService::invalidate('*');

        return redirect()->route($workspace['index_route'])
            ->with('success', $workspace['delete_success']);
    }

    private function workspaceQuery(array $filters, bool $isNewsEvents)
    {
        $allowedTypes = $this->allowedTypes($isNewsEvents);
        $dateFrom = adDate($filters['date_from'] ?? null)?->startOfDay();
        $dateTo = adDate($filters['date_to'] ?? null)?->endOfDay();

        return Notice::query()
            ->whereIn('type', $allowedTypes)
            ->with(['author:id,name,avatar', 'department:id,name,code', 'attachments'])
            ->withCount('attachments')
            ->when($filters['search'] ?? null, function ($q) use ($filters) {
                $search = trim((string) $filters['search']);

                $q->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%")
                        ->orWhereHas('author', fn ($authorQuery) => $authorQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array((string) ($filters['type'] ?? ''), $allowedTypes, true),
                fn ($q) => $q->where('type', (string) $filters['type'])
            )
            ->when($filters['department_id'] ?? null, fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when($filters['status'] ?? null, function ($q) use ($filters) {
                match ($filters['status']) {
                    'published' => $q->where('is_published', true)->where(fn ($s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now())),
                    'scheduled' => $q->where('is_published', true)->where('published_at', '>', now()),
                    'draft' => $q->where('is_published', false),
                    default => null,
                };
            })
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->where(function ($dateQuery) use ($dateFrom) {
                    $dateQuery
                        ->whereDate('published_at', '>=', $dateFrom)
                        ->orWhere(function ($fallbackQuery) use ($dateFrom) {
                            $fallbackQuery
                                ->whereNull('published_at')
                                ->whereDate('created_at', '>=', $dateFrom);
                        });
                });
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->where(function ($dateQuery) use ($dateTo) {
                    $dateQuery
                        ->whereDate('published_at', '<=', $dateTo)
                        ->orWhere(function ($fallbackQuery) use ($dateTo) {
                            $fallbackQuery
                                ->whereNull('published_at')
                                ->whereDate('created_at', '<=', $dateTo);
                        });
                });
            });
    }

    private function workspaceStats(bool $isNewsEvents): array
    {
        $allowedTypes = $this->allowedTypes($isNewsEvents);
        $query = Notice::query()->whereIn('type', $allowedTypes);

        return [
            'total' => (clone $query)->count(),
            'published' => (clone $query)->where('is_published', true)->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))->count(),
            'scheduled' => (clone $query)->where('is_published', true)->where('published_at', '>', now())->count(),
            'draft' => (clone $query)->where('is_published', false)->count(),
            'exam' => $isNewsEvents ? 0 : (clone $query)->where('type', 'exam')->count(),
            'news' => $isNewsEvents ? (clone $query)->where('type', 'news')->count() : 0,
            'event' => $isNewsEvents ? (clone $query)->where('type', 'event')->count() : 0,
            'attachments' => NoticeAttachment::query()
                ->whereHas('notice', fn ($noticeQuery) => $noticeQuery->whereIn('type', $allowedTypes))
                ->count(),
        ];
    }

    private function validatedNoticeData(Request $request, bool $isNewsEvents): array
    {
        $allowedTypes = implode(',', $this->allowedTypes($isNewsEvents));
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => "required|in:{$allowedTypes}",
            'department_id' => $isNewsEvents ? 'nullable' : 'nullable|required_if:type,department|exists:departments,id',
            'published_at' => 'nullable|string|max:20',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:20480',
        ]);

        if (! empty($data['published_at'])) {
            $data['published_at'] = NepaliDateHelper::toAD($data['published_at']);
        }

        $data['department_id'] = ! $isNewsEvents && ($data['type'] ?? null) === 'department'
            ? ($data['department_id'] ?? null)
            : null;

        unset($data['attachments']);

        return $data;
    }

    private function storeAttachments(Request $request, Notice $notice): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            $notice->attachments()->create([
                'file_path' => $file->store('notices', 'public'),
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function removeSelectedAttachments(Request $request, Notice $notice): void
    {
        if (! $request->filled('remove_attachments')) {
            return;
        }

        $ids = array_filter(explode(',', (string) $request->remove_attachments));
        $attachments = $notice->attachments()->whereIn('id', $ids)->get();

        foreach ($attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();
        }
    }

    private function ensureOwnership(Notice $notice): void
    {
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only manage notices you created.');
        }
    }

    private function ensureWorkspaceType(Notice $notice, bool $isNewsEvents): void
    {
        abort_unless(in_array($notice->type, $this->allowedTypes($isNewsEvents), true), 404);
    }

    private function allowedTypes(bool $isNewsEvents): array
    {
        return $isNewsEvents ? self::NEWS_EVENT_TYPES : self::NOTICE_TYPES;
    }

    private function workspace(bool $isNewsEvents): array
    {
        return [
            'is_news_events' => $isNewsEvents,
            'route_prefix' => $isNewsEvents ? 'admin.news-events' : 'admin.notices',
            'index_route' => $isNewsEvents ? 'admin.news-events.index' : 'admin.notices.index',
            'store_route' => $isNewsEvents ? 'admin.news-events.store' : 'admin.notices.store',
            'title' => $isNewsEvents ? 'News & Events' : 'Notice Board',
            'subtitle' => $isNewsEvents
                ? 'Manage published, scheduled, and draft news posts and event updates in a dedicated admin feed.'
                : 'Manage published, scheduled, and draft notices from the same admin workspace used across the rest of the portal.',
            'create_label' => $isNewsEvents ? 'Add News / Event' : 'Add Notice',
            'create_title' => $isNewsEvents ? 'Post News / Event' : 'Post Notice',
            'create_subtitle' => $isNewsEvents ? 'Publish a news update or event announcement to the public feed.' : 'Publish a new notice to the system.',
            'create_success' => $isNewsEvents ? 'News/event published.' : 'Notice published.',
            'update_success' => $isNewsEvents ? 'News/event updated.' : 'Notice updated.',
            'delete_success' => $isNewsEvents ? 'News/event deleted.' : 'Notice deleted.',
            'empty_title' => $isNewsEvents ? 'No news or events found' : 'No notices found',
            'empty_message' => $isNewsEvents
                ? 'Adjust your filters or publish a new post to populate this feed.'
                : 'Adjust your filters or create a new notice to populate the board.',
            'list_label' => $isNewsEvents ? 'posts' : 'notices',
            'singular_label' => $isNewsEvents ? 'news/event' : 'notice',
            'drawer_title' => $isNewsEvents ? 'News & Event Preview' : 'Notice Preview',
            'detail_heading' => $isNewsEvents ? 'Post Details' : 'Notice Details',
            'content_heading' => $isNewsEvents ? 'Post Body' : 'Notice Body',
            'show_description' => $isNewsEvents
                ? 'Review the full post content, publishing metadata, and all attached files from one detail page.'
                : 'Review the full notice content, publishing metadata, and all attached files from one detail page.',
            'submit_label' => $isNewsEvents ? 'Publish Post' : 'Publish Notice',
            'edit_button_label' => $isNewsEvents ? 'Edit Post' : 'Edit Notice',
            'delete_confirm_label' => $isNewsEvents ? 'Delete this post? This cannot be undone.' : 'Delete this notice? This cannot be undone.',
            'type_options' => $isNewsEvents
                ? ['news' => 'News', 'event' => 'Event']
                : [
                    'general' => 'Notice Board',
                    'exam' => 'Exam Schedules & Results',
                    'department' => 'Specific Department',
                    'teachers' => 'Teachers Only',
                ],
        ];
    }

    private function buildNoticePayload(Notice $notice, string $routePrefix): array
    {
        $notice->loadMissing(['author:id,name,avatar', 'department:id,name,code', 'attachments']);

        $publishedAt = $notice->published_at ?? $notice->created_at;
        $status = $this->resolveStatus($notice);
        $departmentName = $notice->department?->code
            ? $notice->department->code . ' - ' . $notice->department->name
            : $notice->department?->name;

        return [
            'id' => $notice->id,
            'title' => $notice->title,
            'content_html' => nl2br(e((string) $notice->content)),
            'type' => $notice->type,
            'type_label' => $this->typeLabel($notice->type),
            'status' => $status,
            'status_label' => ucfirst($status),
            'author_name' => $notice->author?->name ?? 'System',
            'author_avatar_url' => $notice->author?->avatar ? asset('storage/' . $notice->author->avatar) : null,
            'department_name' => $departmentName,
            'published_bs' => $publishedAt ? bsDateTime($publishedAt, 'Y, F d', 'h:i A') : null,
            'created_bs' => $notice->created_at ? bsDateTime($notice->created_at, 'Y, F d', 'h:i A') : null,
            'updated_bs' => $notice->updated_at ? bsDateTime($notice->updated_at, 'Y, F d', 'h:i A') : null,
            'attachments_count' => $notice->attachments_count ?? $notice->attachments->count(),
            'created_by' => $notice->created_by,
            'attachments' => $notice->attachments->map(fn (NoticeAttachment $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'url' => $attachment->url,
                'extension' => $attachment->file_type ? strtoupper((string) $attachment->file_type) : 'FILE',
                'meta' => trim(collect([
                    strtoupper((string) $attachment->file_type),
                    $this->formatFileSize($attachment->file_size),
                ])->filter()->implode(' | ')),
            ])->values(),
            'show_url' => route("{$routePrefix}.show", $notice),
            'edit_url' => route("{$routePrefix}.edit", $notice),
            'delete_url' => route("{$routePrefix}.destroy", $notice),
        ];
    }

    private function resolveStatus(Notice $notice): string
    {
        if (! $notice->is_published) {
            return 'draft';
        }

        if ($notice->published_at && $notice->published_at->isFuture()) {
            return 'scheduled';
        }

        return 'published';
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'general' => 'General',
            'exam' => 'Exam / Result',
            'department' => 'Department',
            'teachers' => 'Teachers',
            'news' => 'News',
            'event' => 'Event',
            default => Str::headline($type),
        };
    }

    private function formatFileSize(?int $bytes): ?string
    {
        if (! $bytes || $bytes < 1) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $unitIndex === 0 ? 0 : 1) . ' ' . $units[$unitIndex];
    }
}
