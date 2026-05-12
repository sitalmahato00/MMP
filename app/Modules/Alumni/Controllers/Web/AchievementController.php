<?php

namespace App\Modules\Alumni\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AlumniAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    public function index()
    {
        $alumnus = auth()->user()->alumnus;
        $achievements = $alumnus?->achievementRecords()->latest('year')->get() ?? collect();
        return view('alumni.achievements', compact('alumnus', 'achievements'));
    }

    public function store(Request $request)
    {
        $alumnus = auth()->user()->alumnus;
        abort_unless($alumnus, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'year' => 'nullable|string|max:4',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'year' => $validated['year'] ?? null,
        ];

        if ($request->hasFile('certificate')) {
            $data['certificate_path'] = $request->file('certificate')->store('alumni/certificates', 'public');
        }

        $alumnus->achievementRecords()->create($data);

        return back()->with('success', 'Achievement added.');
    }

    public function destroy(AlumniAchievement $achievement)
    {
        $alumnus = auth()->user()->alumnus;
        abort_unless($alumnus && $achievement->alumni_id === $alumnus->id, 403);

        if ($achievement->certificate_path) {
            Storage::disk('public')->delete($achievement->certificate_path);
        }

        $achievement->delete();
        return back()->with('success', 'Achievement removed.');
    }
}
