<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $parent = $user->parentProfile;
        $parentId = $parent?->id ?? 'guest';

        $children = Cache::remember("parent_dashboard_children:{$parentId}", 300, function () use ($parent) {
            return $parent?->children()->with(['user', 'department', 'program'])->get() ?? collect();
        });
        $session = AcademicSession::current();

        $recentNotices = Cache::remember('parent_dashboard_notices', 300, function () {
            return Notice::published()->latest()->take(5)->get();
        });

        return view('parent.dashboard', compact('parent', 'children', 'session', 'recentNotices'));
    }
}
