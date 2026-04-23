<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        return view('student.settings.index', compact('student'));
    }

    public function updateProfile(Request $request)
    {
        $student = auth()->user()->student;
        $user = auth()->user();
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profile updated successfully');
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

        return back()->with('success', 'Password updated successfully');
    }

    public function updatePreferences(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $request->validate([
            'language' => 'nullable|string|in:en,ne',
            'timezone' => 'nullable|string',
            'date_format' => 'nullable|string|in:bs,ad',
        ]);

        // Store preferences in user preferences (you might want to create a preferences table)
        $preferences = [
            'language' => $request->language ?? 'en',
            'timezone' => $request->timezone ?? 'Asia/Kathmandu',
            'date_format' => $request->date_format ?? 'bs',
        ];

        $student->update(['preferences' => json_encode($preferences)]);

        return back()->with('success', 'Preferences updated successfully');
    }

    public function updateNotifications(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $notifications = [
            'email_notices' => $request->boolean('email_notices'),
            'email_assignments' => $request->boolean('email_assignments'),
            'email_marks' => $request->boolean('email_marks'),
            'email_attendance' => $request->boolean('email_attendance'),
        ];

        $student->update(['notification_preferences' => json_encode($notifications)]);

        return back()->with('success', 'Notification preferences updated successfully');
    }

    public function logoutAllDevices(Request $request)
    {
        $user = auth()->user();
        
        // Invalidate all sessions except current
        $user->tokens()->delete(); // If using Sanctum
        
        return back()->with('success', 'Logged out from all devices successfully');
    }

    public function resetDashboard(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Reset dashboard preferences
        $student->update(['dashboard_preferences' => null]);

        return back()->with('success', 'Dashboard reset successfully');
    }

    public function clearPreferences(Request $request)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Clear all preferences
        $student->update([
            'preferences' => null,
            'notification_preferences' => null,
            'dashboard_preferences' => null,
        ]);

        return back()->with('success', 'All preferences cleared successfully');
    }
}