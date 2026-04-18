<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        $children = $parent?->children()
            ->with(['user', 'department', 'program', 'marks.subject', 'marks.exam'])
            ->get() ?? collect();

        $childrenResults = $children->map(function ($student) {
            $published = $student->marks->where('status', 'published');
            $byExam = $published->groupBy(fn($m) => $m->exam?->name ?? 'Unknown');
            $avg = $published->count() > 0
                ? round($published->avg(fn($m) => ($m->theory ?? 0) + ($m->practical ?? 0)), 1)
                : null;

            return [
                'student' => $student,
                'marksByExam' => $byExam,
                'avgMarks' => $avg,
                'totalRecords' => $published->count(),
            ];
        });

        return view('parent.results', compact('childrenResults'));
    }
}
