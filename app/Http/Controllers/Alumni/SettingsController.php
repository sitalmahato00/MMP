<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
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
        $user = Auth::user()->load(['roles', 'alumnus.department', 'alumnus.program']);
        $preferences = $this->getUserPreferences($user);
        $notificationPreferences = app(NotificationPreferenceService::class)->notificationPreferences($user);
        $activeSessions = $this->getActiveSessions();

        return view('alumni.settings.index', compact('user', 'preferences', 'notificationPreferences', 'activeSessions'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $alumnus = $user->alumnus;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'current_job' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'work_location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:1000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'address' => $validated['address'] ?? null,
            'avatar' => $validated['avatar'] ?? $user->avatar,
        ]);

        $skills = filled($validated['skills'] ?? null)
            ? array_values(array_filter(array_map('trim', explode(',', $validated['skills']))))
            : [];

        $alumnus?->update([
            'current_job' => $validated['current_job'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'work_location' => $validated['work_location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'skills' => $skills,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'github_url' => $validated['github_url'] ?? null,
            'portfolio_url' => $validated['portfolio_url'] ?? null,
        ]);

        if ($alumnus) {
            $alumnus->forceFill([
                'profile_completion' => $alumnus->fresh()?->calculateProfileCompletion() ?? $alumnus->calculateProfileCompletion(),
            ])->save();
        }

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
            'email_notice_alerts' => ['nullable', 'boolean'],
            'email_event_alerts' => ['nullable', 'boolean'],
            'email_career_alerts' => ['nullable', 'boolean'],
            'inapp_notices' => ['nullable', 'boolean'],
            'inapp_events' => ['nullable', 'boolean'],
            'inapp_updates' => ['nullable', 'boolean'],
            'sms_important_alerts' => ['nullable', 'boolean'],
        ]);

        app(NotificationPreferenceService::class)->saveNotificationPreferences($user, [
            'email_notice_alerts' => $request->boolean('email_notice_alerts'),
            'email_event_alerts' => $request->boolean('email_event_alerts'),
            'email_career_alerts' => $request->boolean('email_career_alerts'),
            'inapp_notices' => $request->boolean('inapp_notices'),
            'inapp_events' => $request->boolean('inapp_events'),
            'inapp_updates' => $request->boolean('inapp_updates'),
            'sms_important_alerts' => $request->boolean('sms_important_alerts'),
        ]);

        return back()->with('success', 'Notification preferences updated successfully.');
    }

    public function updateTwoFactor(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'two_factor_enabled' => ['required', 'boolean'],
            'two_factor_method' => ['required_if:two_factor_enabled,true', 'in:email,phone'],
        ]);

        // If enabling 2FA with phone method, ensure phone number exists
        if ($validated['two_factor_enabled'] && $validated['two_factor_method'] === 'phone' && !$user->phone) {
            return back()->withErrors(['two_factor_method' => 'Please add a phone number to your profile before enabling phone-based 2FA.']);
        }

        $user->update([
            'two_factor_enabled' => $validated['two_factor_enabled'],
            'two_factor_method' => $validated['two_factor_enabled'] ? $validated['two_factor_method'] : 'email',
        ]);

        $message = $validated['two_factor_enabled'] 
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

    private function getUserPreferences($user): array
    {
        return app(NotificationPreferenceService::class)->userPreferences($user);
    }

    private function getActiveSessions(): array
    {
        return [[
            'id' => session()->getId(),
            'device' => 'Chrome on Windows',
            'ip' => request()->ip(),
            'last_active' => now(),
            'is_current' => true,
        ]];
    }
}
