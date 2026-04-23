<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Services\PublicDataService;
use Illuminate\Http\Request;

class NoticesController extends Controller
{
    protected $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
    }

    public function index(Request $request)
    {
        $student = auth()->user()->student?->loadMissing('program.department');
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Get filters
        $type = $request->get('type', 'internal');
        $search = $request->get('search');
        $status = $request->get('status');

        $internalBaseQuery = Notice::query()
            ->visibleToStudent($student);

        $departmentNotices = (clone $internalBaseQuery)
            ->where('is_published', true)
            ->where(function ($q) use ($student) {
                $q->where('department_id', $student->department_id)
                    ->orWhere(function ($programQuery) use ($student) {
                        $programQuery->where('program_id', $student->program_id);
                    });
            })
            ->count();

        $publishedNotices = (clone $internalBaseQuery)
            ->where('is_published', true)
            ->count();

        $ctevtGeneralNotices = $this->publicDataService->getCtevtGeneralNotices(10);
        $ctevtResultNotices = $this->publicDataService->getCtevtResultNotices(10);
        $ctevtNotices = count($ctevtGeneralNotices['items'] ?? [])
            + count($ctevtResultNotices['items'] ?? []);
        $totalNotices = $publishedNotices + $ctevtNotices;

        if ($type === 'ctevt') {
            $notices = collect(array_merge(
                $this->mapCtevtNoticesForStudent($ctevtGeneralNotices['items'] ?? [], 'CTEVT General'),
                $this->mapCtevtNoticesForStudent($ctevtResultNotices['items'] ?? [], 'CTEVT Result')
            ));
            
            if ($search) {
                $notices = $notices->filter(function ($notice) use ($search) {
                    return str_contains(strtolower($notice['title'] ?? ''), strtolower($search))
                        || str_contains(strtolower($notice['content'] ?? ''), strtolower($search));
                });
            }

            $notices = $notices->take(20)->values();
        } else {
            $noticesQuery = Notice::with(['attachments', 'author', 'department', 'program'])
                ->visibleToStudent($student)
                ->where('is_published', true);

            if ($search) {
                $noticesQuery->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if ($status) {
                if ($status === 'published') {
                    $noticesQuery->where('is_published', true);
                } elseif ($status === 'draft') {
                    $noticesQuery->where('is_published', false);
                }
            }

            $notices = $noticesQuery->latest('published_at')->paginate(20);
        }

        return view('student.notices.index', compact(
            'student',
            'notices',
            'type',
            'totalNotices',
            'departmentNotices',
            'publishedNotices',
            'ctevtNotices'
        ));
    }

    public function show($id)
    {
        $student = auth()->user()->student?->loadMissing('program.department');
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $notice = Notice::with(['attachments', 'author', 'department', 'program'])
            ->visibleToStudent($student)
            ->where('is_published', true)
            ->findOrFail($id);

        return view('student.notices.show', compact('student', 'notice'));
    }

    private function mapCtevtNoticesForStudent(array $items, string $source): array
    {
        return collect($items)
            ->map(function (array $notice) use ($source) {
                $primaryFile = collect($notice['files'] ?? [])->first();

                return [
                    'title' => $notice['title'] ?? 'CTEVT Notice',
                    'content' => trim((string) ($notice['publisher'] ?? '')),
                    'published_date' => $notice['updated_date'] ?? null,
                    'file_url' => $primaryFile['url'] ?? ($notice['url'] ?? null),
                    'source_label' => $source,
                ];
            })
            ->all();
    }
}
