<?php

namespace App\Modules\Teacher\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        $teacher->load(['user', 'department']);

        return view('teacher.profile.show', compact('teacher', 'user'));
    }

    public function edit()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        $teacher->load(['user', 'department']);

        return view('teacher.profile.edit', compact('teacher', 'user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
        ]);

        // Update user
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Update teacher
        $teacher->update([
            'qualification' => $data['qualification'] ?? $teacher->qualification,
            'specialization' => $data['specialization'] ?? $teacher->specialization,
        ]);

        return redirect()->route('teacher.profile.show')->with('success', 'Profile updated successfully.');
    }

    public function changePassword()
    {
        return view('teacher.profile.change-password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('teacher.profile.show')->with('success', 'Password changed successfully.');
    }
}
