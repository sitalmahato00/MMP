<?php

namespace App\Modules\Alumni\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\Program;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\Department\Models\Department;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $alumnus = auth()->user()->alumnus;
        $alumnus?->load(['department', 'program']);
        return view('alumni.profile', compact('alumnus'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $alumnus = $user->alumnus;

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
            'bio' => 'nullable|string|max:2000',
            'skills' => 'nullable|string|max:1000',
            'linkedin_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'phone' => $validated['phone'] ?? $user->phone,
            'address' => $validated['address'] ?? $user->address,
            'avatar' => $user->avatar,
        ]);

        $skills = !empty($validated['skills'])
            ? array_map('trim', explode(',', $validated['skills']))
            : [];

        $alumnus?->update([
            'bio' => $validated['bio'] ?? null,
            'skills' => $skills,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
            'profile_completion' => $alumnus->calculateProfileCompletion(),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
