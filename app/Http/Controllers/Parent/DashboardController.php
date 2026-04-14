<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = $user->parentProfile;
        $children = $parent?->children()->with(['user', 'department', 'program'])->get() ?? collect();
        $session = AcademicSession::current();

        $recentNotices = Notice::published()->latest()->take(5)->get();

        return view('parent.dashboard', compact('parent', 'children', 'session', 'recentNotices'));
    }
}
