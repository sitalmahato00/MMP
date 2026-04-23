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
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Get filters
        $type = $request->get('type', 'internal');
        $search = $request->get('search');
        $status = $request->get('status');

        // Calculate statistics for KPI cards
        $departmentNotices = Notice::where(function($q) use ($student) {
            // General notices (visible to all)
            $q->where(function($subQ) {
                $subQ->whereNull('department_id')
                     ->whereIn('type', ['general', 'news', 'event', 'exam']);
            })
            // OR department-specific notices
            ->orWhere(function($subQ) use ($student) {
                $subQ->where('department_id', $student->program->department_id)
                     ->whereIn('type', ['department', 'general', 'news', 'event', 'exam']);
            })
            // OR program-specific notices
            ->orWhere(function($subQ) use ($student) {
                $subQ->where('program_id', $student->program_id)
                     ->where('type', 'program');
            });
        })->count();
        
        $publishedNotices = Notice::where(function($q) use ($student) {
            // General notices (visible to all)
            $q->where(function($subQ) {
                $subQ->whereNull('department_id')
                     ->whereIn('type', ['general', 'news', 'event', 'exam']);
            })
            // OR department-specific notices
            ->orWhere(function($subQ) use ($student) {
                $subQ->where('department_id', $student->program->department_id)
                     ->whereIn('type', ['department', 'general', 'news', 'event', 'exam']);
            })
            // OR program-specific notices
            ->orWhere(function($subQ) use ($student) {
                $subQ->where('program_id', $student->program_id)
                     ->where('type', 'program');
            });
        })->where('is_published', true)->count();
        
        $ctevtNotices = count($this->publicDataService->getNotices());
        $totalNotices = $departmentNotices + $ctevtNotices;

        if ($type === 'ctevt') {
            // Get CTEVT notices
            $notices = $this->publicDataService->getNotices();
            
            if ($search) {
                $notices = collect($notices)->filter(function($notice) use ($search) {
                    return stripos($notice['title'], $search) !== false ||
                           stripos($notice['content'], $search) !== false;
                });
            }

            $notices = $notices->take(20); // Limit to 20 for pagination-like behavior
        } else {
            // Get internal notices - include general notices and department-specific notices
            $noticesQuery = Notice::with(['attachments', 'author', 'department', 'program'])
                ->where(function($q) use ($student) {
                    // General notices (visible to all)
                    $q->where(function($subQ) {
                        $subQ->whereNull('department_id')
                             ->whereIn('type', ['general', 'news', 'event', 'exam']);
                    })
                    // OR department-specific notices
                    ->orWhere(function($subQ) use ($student) {
                        $subQ->where('department_id', $student->program->department_id)
                             ->whereIn('type', ['department', 'general', 'news', 'event', 'exam']);
                    })
                    // OR program-specific notices
                    ->orWhere(function($subQ) use ($student) {
                        $subQ->where('program_id', $student->program_id)
                             ->where('type', 'program');
                    });
                })
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
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $notice = Notice::with(['attachments', 'author'])
            ->where('department_id', $student->program->department_id)
            ->where('is_published', true)
            ->findOrFail($id);

        return view('student.notices.show', compact('student', 'notice'));
    }
}