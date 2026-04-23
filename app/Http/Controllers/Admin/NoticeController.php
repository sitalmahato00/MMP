<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\Department;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'type', 'department_id', 'status', 'date_from', 'date_to']);
        $dateFrom = adDate($filters['date_from'] ?? null)?->startOfDay();
        $dateTo = adDate($filters['date_to'] ?? null)?->endOfDay();

        $notices = Notice::with(['author:id,name,avatar', 'department:id,name,code', 'attachments'])
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
            ->when($filters['type'] ?? null, fn ($q) => $q->where('type', $filters['type']))
            ->when($filters['department_id'] ?? null, fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when($filters['status'] ?? null, function ($q) use ($filters) {
                match ($filters['status']) {
                    'published' => $q->where('is_published', true)->where(fn ($s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now())),
                    'scheduled' => $q->where('is_published', true)->where('published_at', '>', now()),
                    'draft'     => $q->where('is_published', false),
                    default     => null,
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
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $allQuery = Notice::query();
        $stats = [
            'total'       => (clone $allQuery)->count(),
            'published'   => (clone $allQuery)->where('is_published', true)->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))->count(),
            'scheduled'   => (clone $allQuery)->where('is_published', true)->where('published_at', '>', now())->count(),
            'draft'       => (clone $allQuery)->where('is_published', false)->count(),
            'exam'        => (clone $allQuery)->where('type', 'exam')->count(),
            'attachments' => NoticeAttachment::count(),
        ];

        $departments = Department::orderBy('name')->get(['id', 'name', 'code']);
        $noticeDrawerPayload = $notices->getCollection()
            ->map(fn (Notice $notice) => $this->buildNoticePayload($notice))
            ->values();

        return view('admin.notices.index', compact('notices', 'stats', 'departments', 'filters', 'noticeDrawerPayload'));
    }

    public function show(Request $request, Notice $notice)
    {
        $notice->loadMissing(['author:id,name,avatar', 'department:id,name,code', 'attachments'])->loadCount('attachments');

        $payload = $this->buildNoticePayload($notice);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return view('admin.notices.show', compact('notice', 'payload'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:general,department,class,teachers,exam,news,event',
            'published_at'   => 'nullable|string|max:20',
            'attachments'    => 'nullable|array|max:10',
            'attachments.*'  => 'file|max:20480',
        ]);

        if (!empty($data['published_at'])) {
            $data['published_at'] = NepaliDateHelper::toAD($data['published_at']);
        }

        $data['created_by']   = auth()->id();
        $data['slug']         = Str::slug($data['title']) . '-' . uniqid();
        $data['is_published'] = true;
        unset($data['attachments']);

        $notice = Notice::create($data);

        // Handle multiple attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->attachments()->create([
                    'file_path' => $file->store('notices', 'public'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        PublicDataService::invalidate('*');

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice published.');
    }

    public function edit(Notice $notice)
    {
        // Check if user can edit this notice (only their own notices)
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
        }

        $notice->load('attachments');
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        // Check if user can update this notice (only their own notices)
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only edit notices you created.');
        }

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'type'           => 'required|in:general,department,class,teachers,exam,news,event',
            'published_at'   => 'nullable|string|max:20',
            'attachments'    => 'nullable|array|max:10',
            'attachments.*'  => 'file|max:20480',
        ]);

        if (!empty($data['published_at'])) {
            $data['published_at'] = NepaliDateHelper::toAD($data['published_at']);
        }
        unset($data['attachments']);

        $notice->update($data);

        // Remove selected attachments
        if ($request->filled('remove_attachments')) {
            $ids = array_filter(explode(',', $request->remove_attachments));
            $toDelete = $notice->attachments()->whereIn('id', $ids)->get();
            foreach ($toDelete as $att) {
                if (Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
                $att->delete();
            }
        }

        // Add new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->attachments()->create([
                    'file_path' => $file->store('notices', 'public'),
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        PublicDataService::invalidate('*');

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice)
    {
        // Check if user can delete this notice (only their own notices)
        if ($notice->created_by !== auth()->id()) {
            abort(403, 'You can only delete notices you created.');
        }

        // Delete all attachment files
        foreach ($notice->attachments as $att) {
            if (Storage::disk('public')->exists($att->file_path)) {
                Storage::disk('public')->delete($att->file_path);
            }
        }
        // Legacy single attachment
        if ($notice->attachment && Storage::disk('public')->exists($notice->attachment)) {
            Storage::disk('public')->delete($notice->attachment);
        }
        $notice->delete();
        PublicDataService::invalidate('*');
        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted.');
    }

    private function buildNoticePayload(Notice $notice): array
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
            'created_by' => $notice->created_by, // Add this for ownership checking
            'attachments' => $notice->attachments->map(fn (NoticeAttachment $attachment) => [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'url' => $attachment->url,
                'extension' => $attachment->file_type ? strtoupper((string) $attachment->file_type) : 'FILE',
                'meta' => trim(collect([
                    strtoupper((string) $attachment->file_type),
                    $this->formatFileSize($attachment->file_size),
                ])->filter()->implode(' · ')),
            ])->values(),
            'show_url' => route('admin.notices.show', $notice),
            'edit_url' => route('admin.notices.edit', $notice),
            'delete_url' => route('admin.notices.destroy', $notice),
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
            'class' => 'Class / Section',
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
