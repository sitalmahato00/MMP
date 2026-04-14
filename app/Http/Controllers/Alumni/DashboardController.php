<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\{Notice};

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $alumnus = $user->alumnus;
        $recentNotices = Notice::published()->latest()->take(5)->get();

        return view('alumni.dashboard', compact('alumnus', 'recentNotices'));
    }
}
