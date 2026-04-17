<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\{Notice};
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $alumnus = $user->alumnus;
        $recentNotices = Cache::remember('alumni_dashboard_notices', 300, function () {
            return Notice::published()->latest()->take(5)->get();
        });

        return view('alumni.dashboard', compact('alumnus', 'recentNotices'));
    }
}
