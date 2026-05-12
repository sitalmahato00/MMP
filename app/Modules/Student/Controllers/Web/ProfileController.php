<?php

namespace App\Modules\Student\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        return view('student.profile.show', compact('student'));
    }

    public function edit()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        return view('student.profile.edit', compact('student'));
    }

    public function update(Request $request)
    {
        $student = auth()->user()->student;
        $user = auth()->user();
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update user data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update student data
        $studentData = [
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($student->profile_photo && Storage::disk('public')->exists($student->profile_photo)) {
                Storage::disk('public')->delete($student->profile_photo);
            }

            $file = $request->file('profile_photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_photos', $filename, 'public');
            $studentData['profile_photo'] = $path;
        }

        $student->update($studentData);

        return redirect()->route('student.profile.show')
            ->with('success', 'Profile updated successfully');
    }

    public function changePassword()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        return view('student.profile.change-password', compact('student'));
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('student.profile.show')
            ->with('success', 'Password updated successfully');
    }
}