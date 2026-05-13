<?php

namespace App\Modules\HOD\Controllers;


use App\Http\Controllers\Controller;
use App\Modules\Department\Models\Department;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['roles', 'teacher.department']);
        $department = Department::where('hod_id', $user->id)->first();
        $preferences = $this->getUserPreferences($user);
        $notificationPreferences = app(NotificationPreferenceService::class)->notificationPreferences($user);
        $activeSessions = $this->getActiveSessions($user);

        return view('hod.settings.index', compact('user', 'department', 'preferences', 'notificationPreferences', 'activeSessions'));
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
            'nepali_numbers' => ['nullable', 'boolean'],
            'dashboard_layout' => ['required', 'in:compact,comfortable'],
            'default_page' => ['required', 'string'],
            'table_density' => ['required', 'in:normal,compact'],
            'pagination_size' => ['required', 'in:10,25,50'],
        ]);

        app(NotificationPreferenceService::class)->saveUserPreferences($user, [
            ...$validated,
            'nepali_numbers' => $request->boolean('nepali_numbers'),
        ]);

        return back()->with('success', 'Preferences updated successfully.');
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email_student_alerts' => ['nullable', 'boolean'],
            'email_attendance_alerts' => ['nullable', 'boolean'],
            'email_exam_alerts' => ['nullable', 'boolean'],
            'email_teacher_alerts' => ['nullable', 'boolean'],
            'email_department_updates' => ['nullable', 'boolean'],
            'inapp_notices' => ['nullable', 'boolean'],
            'inapp_comments' => ['nullable', 'boolean'],
            'inapp_department_updates' => ['nullable', 'boolean'],
            'inapp_updates' => ['nullable', 'boolean'],
            'sms_important_alerts' => ['nullable', 'boolean'],
        ]);

        app(NotificationPreferenceService::class)->saveNotificationPreferences($user, [
            'email_student_alerts' => $request->boolean('email_student_alerts'),
            'email_attendance_alerts' => $request->boolean('email_attendance_alerts'),
            'email_exam_alerts' => $request->boolean('email_exam_alerts'),
            'email_teacher_alerts' => $request->boolean('email_teacher_alerts'),
            'email_department_updates' => $request->boolean('email_department_updates'),
            'inapp_notices' => $request->boolean('inapp_notices'),
            'inapp_comments' => $request->boolean('inapp_comments'),
            'inapp_department_updates' => $request->boolean('inapp_department_updates'),
            'inapp_updates' => $request->boolean('inapp_updates'),
            'sms_important_alerts' => $request->boolean('sms_important_alerts'),
        ]);

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function updateTwoFactor(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'two_factor_enabled' => ['nullable', 'boolean'],
            'two_factor_method' => ['required_if:two_factor_enabled,1', 'in:email,phone'],
        ]);

        // Convert null to false for unchecked checkbox
        $twoFactorEnabled = $request->boolean('two_factor_enabled');

        // If enabling 2FA with phone method, ensure phone number exists
        if ($twoFactorEnabled && ($validated['two_factor_method'] ?? 'email') === 'phone' && !$user->phone) {
            return back()->withErrors(['two_factor_method' => 'Please add a phone number to your profile before enabling phone-based 2FA.']);
        }

        $user->update([
            'two_factor_enabled' => $twoFactorEnabled,
            'two_factor_method' => $twoFactorEnabled ? ($validated['two_factor_method'] ?? 'email') : 'email',
        ]);

        $message = $twoFactorEnabled 
            ? 'Two-factor authentication enabled successfully.' 
            : 'Two-factor authentication disabled successfully.';

        return back()->with('success', $message);
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
        app(NotificationPreferenceService::class)->clearStoredPreferences(Auth::user());
        session()->forget(['user_preferences', 'notification_preferences']);

        return back()->with('success', 'Preferences cleared successfully.');
    }

    private function getUserPreferences($user)
    {
        return app(NotificationPreferenceService::class)->userPreferences($user);
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
