<?php

namespace App\Modules\Cms\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Cms\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsApiController extends BaseController
{
    public function notices(Request $request): JsonResponse
    {
        $notices = Notice::query()
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->is_published !== null, fn ($q) => $q->where('is_published', $request->is_published))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return $this->paginated($notices);
    }

    public function noticeShow(Notice $notice): JsonResponse
    {
        $notice->load(['author:id,name', 'department:id,name', 'program:id,name', 'attachments']);
        return $this->success($notice);
    }

    public function storeNotice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:notices,slug'],
            'content'       => ['nullable', 'string'],
            'type'          => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'program_id'    => ['nullable', 'exists:programs,id'],
            'semester'      => ['nullable', 'integer'],
            'is_published'  => ['boolean'],
            'published_at'  => ['nullable', 'date'],
        ]);

        $data['created_by'] = $request->user()->id;
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']) . '-' . uniqid();
        }
        if (empty($data['content'])) {
            $data['content'] = '';
        }
        $notice = Notice::create($data);

        return $this->created($notice);
    }

    public function updateNotice(Request $request, Notice $notice): JsonResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'unique:notices,slug,' . $notice->id],
            'content'       => ['nullable', 'string'],
            'type'          => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'program_id'    => ['nullable', 'exists:programs,id'],
            'semester'      => ['nullable', 'integer'],
            'is_published'  => ['boolean'],
            'published_at'  => ['nullable', 'date'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']) . '-' . $notice->id;
        }
        $notice->update($data);
        return $this->success($notice);
    }

    public function destroyNotice(Notice $notice): JsonResponse
    {
        $notice->delete();
        return $this->success(['message' => 'Notice deleted.']);
    }
}
