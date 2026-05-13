<?php

namespace App\Modules\Alumni\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Program;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\CMS\Models\Notice;
use App\Modules\Department\Models\Department;
use App\Modules\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $alumnus = $user->alumnus;
        
        if (!$alumnus) {
            abort(403, 'Alumni profile not found');
        }

        $alumnus->load(['projects', 'achievementRecords', 'employmentHistory', 'department', 'program']);

        $profileCompletion = $this->calculateProfileCompletion($alumnus);

        $cacheKey = "alumni_dashboard_{$alumnus->id}_v2";
        $data = Cache::remember($cacheKey, 300, function () use ($alumnus) {
            return [
                'projects_count' => $alumnus->projects()->count(),
                'achievements_count' => $alumnus->achievementRecords()->count(),
                'employment_count' => $alumnus->employmentHistory()->count(),
            ];
        });

        $recentNotices = Cache::remember('alumni_dashboard_notices', 300, function () {
            return Notice::published()
                ->with('author')
                ->latest()
                ->take(5)
                ->get();
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('alumni.dashboard', compact('alumnus', 'profileCompletion', 'recentNotices', 'data', 'greeting', 'lastUpdated'));
    }

    private function calculateProfileCompletion($alumnus): int
    {
        $fields = [
            'phone' => $alumnus->user->phone,
            'email' => $alumnus->user->email,
            'address' => $alumnus->user->address,
            'graduation_year' => $alumnus->graduation_year,
            'current_occupation' => $alumnus->current_occupation,
            'company_name' => $alumnus->company_name,
        ];

        $filled = collect($fields)->filter(fn($v) => !empty($v))->count();
        $total = count($fields);

        return $total > 0 ? round(($filled / $total) * 100) : 0;
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening'
        };
    }
}
