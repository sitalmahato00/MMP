<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['roles', 'student.department', 'student.program', 'student.academicSession']);
        
        // Get user preferences (stored in JSON or separate table)
        $preferences = $this->getUserPreferences($user);
        
        // Get active sessions (mock data for now - implement with session tracking)
        $activeSessions = $this->getActiveSessions($user);
        
        return view('student.settings.index', compact('user', 'preferences', 'activeSessions'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark,auto'],
            'language' => ['required', 'in:en,ne'],
            'date_format' => ['required', 'in:bs,ad'],
            'nepali_numbers' => ['required', 'boolean'],
            'dashboard_layout' => ['required', 'in:compact,comfortable'],
            'default_page' => ['required', 'string'],
            'table_density' => ['required', 'in:normal,compact'],
            'pagination_size' => ['required', 'in:10,25,50'],
        ]);

        // Store preferences in user meta or separate table
        // For now, we'll use a JSON column or session
        session(['user_preferences' => $validated]);

        return back()->with('success', 'Preferences updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_assignment_alerts' => ['required', 'boolean'],
            'email_exam_alerts' => ['required', 'boolean'],
            'email_grade_alerts' => ['required', 'boolean'],
            'email_attendance_alerts' => ['required', 'boolean'],
            'email_notice_alerts' => ['required', 'boolean'],
            'inapp_notices' => ['required', 'boolean'],
            'inapp_assignments' => ['required', 'boolean'],
            'inapp_grades' => ['required', 'boolean'],
            'inapp_updates' => ['required', 'boolean'],
            'sms_important_alerts' => ['required', 'boolean'],
        ]);

        // Store notification preferences
        session(['notification_preferences' => $validated]);

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function logoutAllDevices(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($request->password);

        return back()->with('success', 'Logged out from all other devices successfully.');
    }

    public function resetDashboard()
    {
        session()->forget(['dashboard_widgets', 'dashboard_layout']);
        
        return back()->with('success', 'Dashboard reset successfully.');
    }

    public function clearPreferences()
    {
        session()->forget(['user_preferences', 'notification_preferences']);
        
        return back()->with('success', 'Preferences cleared successfully.');
    }

    private function getUserPreferences($user)
    {
        return session('user_preferences', [
            'theme' => 'light',
            'language' => 'en',
            'date_format' => 'bs',
            'nepali_numbers' => true,
            'dashboard_layout' => 'comfortable',
            'default_page' => 'dashboard',
            'table_density' => 'normal',
            'pagination_size' => '25',
        ]);
    }

    private function getActiveSessions($user)
    {
        // Mock data - implement actual session tracking
        return [
            [
                'id' => session()->getId(),
                'device' => 'Chrome on Windows',
                'ip' => request()->ip(),
                'last_active' => now(),
                'is_current' => true,
            ],
        ];
    }
}
