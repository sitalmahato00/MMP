<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to phone number
     * 
     * @param SendOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $phone = $request->phone;

        // Check rate limiting
        if ($this->otpService->isRateLimited($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another OTP',
            ], 429);
        }

        // Check if user exists with this phone
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this phone number',
            ], 404);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        // Send OTP
        $result = $this->otpService->sendOtp($phone);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Verify OTP and issue token
     * 
     * @param VerifyOtpRequest $request
     * @return JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $phone = $request->phone;
        $otp = $request->otp;

        // Verify OTP
        $result = $this->otpService->verifyOtp($phone, $otp);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Find user
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        // Issue Sanctum token
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        Log::info("User logged in via OTP: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserResponse($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Login with email/password or email/OTP
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $email = $request->email;
        $password = $request->password;
        $otp = $request->otp;

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::warning("Failed login attempt - user not found", [
                'email' => $email,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            Log::warning("Login attempt for inactive account: {$user->id}");
            
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        $authenticated = false;

        // Try password authentication
        if ($password) {
            if (Hash::check($password, $user->password)) {
                // Check if 2FA is enabled
                if ($user->two_factor_enabled && !$otp) {
                    // Send OTP for 2FA
                    $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
                    $result = $this->otpService->sendOtp($identifier, $user->two_factor_method, $user);
                    
                    Log::info("2FA OTP sent for user: {$user->id}");
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Two-factor authentication required',
                        'requires_2fa' => true,
                        'two_factor_method' => $user->two_factor_method,
                        'data' => [
                            'otp_sent' => true,
                            'expires_in' => $result['expires_in'] ?? 60,
                        ],
                    ], 200);
                }
                
                // If 2FA is enabled and OTP is provided, verify it
                if ($user->two_factor_enabled && $otp) {
                    $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
                    $otpResult = $this->otpService->verifyOtp($identifier, $otp);
                    
                    if (!$otpResult['success']) {
                        Log::warning("2FA OTP verification failed for user: {$user->id}");
                        return response()->json($otpResult, 401);
                    }
                }
                
                $authenticated = true;
                Log::info("User logged in via password: {$user->id}");
            }
        }

        // Try OTP authentication if password failed or not provided (email-based OTP)
        if (!$authenticated && $otp && $email) {
            $result = $this->otpService->verifyOtp($email, $otp);
            
            if ($result['success']) {
                $authenticated = true;
                Log::info("User logged in via email OTP: {$user->id}");
            } else {
                return response()->json($result, 401);
            }
        }

        if (!$authenticated) {
            Log::warning("Failed login attempt", [
                'user_id' => $user->id,
                'email' => $email,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Issue Sanctum token
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $this->formatUserResponse($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Logout user and revoke token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        Log::info("User logged out: {$request->user()->id}");

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Get authenticated user profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Eager load relationships
        $user->load(['student', 'teacher', 'parentProfile', 'alumnus']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUserResponse($user),
            ],
        ], 200);
    }

    /**
     * Refresh Sanctum token — revoke current, issue new
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $user->currentAccessToken()->delete();

        // Issue a fresh token
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        Log::info("Token refreshed for user: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Update the authenticated user's profile
     * Fields: name, phone, gender, dob, address, avatar (file)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => ['sometimes', 'string', 'max:255'],
            'phone'  => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other'])],
            'dob'    => ['sometimes', 'nullable', 'date', 'before:today'],
            'address'=> ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists and is stored locally
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        Log::info("Profile updated for user: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $this->formatUserResponse($user->fresh()),
            ],
        ], 200);
    }

    /**
     * Change the authenticated user's password
     * Requires current_password verification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all tokens so other sessions are logged out
        $user->tokens()->delete();

        // Issue a new token for the current session
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        Log::info("Password changed for user: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. Please log in again on other devices.',
            'data' => [
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Update notification preferences
     * Stores a JSON map of notification channel toggles
     *
     * Expected body example:
     * {
     *   "email_notices": true,
     *   "email_marks": false,
     *   "push_notices": true,
     *   "push_assignments": true
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'notification_preferences'                    => ['required', 'array'],
            'notification_preferences.email_notices'      => ['sometimes', 'boolean'],
            'notification_preferences.email_marks'        => ['sometimes', 'boolean'],
            'notification_preferences.email_assignments'  => ['sometimes', 'boolean'],
            'notification_preferences.push_notices'       => ['sometimes', 'boolean'],
            'notification_preferences.push_marks'         => ['sometimes', 'boolean'],
            'notification_preferences.push_assignments'   => ['sometimes', 'boolean'],
            'notification_preferences.push_attendance'    => ['sometimes', 'boolean'],
        ]);

        // Merge with existing preferences so partial updates don't wipe other keys
        $existing = $user->notification_preferences ?? [];
        $merged   = array_merge($existing, $request->notification_preferences);

        $user->update(['notification_preferences' => $merged]);

        Log::info("Notification preferences updated for user: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => [
                'notification_preferences' => $user->fresh()->notification_preferences,
            ],
        ], 200);
    }

    /**
     * Toggle two-factor authentication on/off and set the preferred method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'two_factor_enabled' => ['required', 'boolean'],
            'two_factor_method'  => ['required_if:two_factor_enabled,true', Rule::in(['email', 'phone'])],
        ]);

        // If enabling 2FA via phone, make sure a phone number is on file
        if ($request->two_factor_enabled && $request->two_factor_method === 'phone' && !$user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'A phone number is required to enable phone-based 2FA. Please add one to your profile first.',
            ], 422);
        }

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
            'two_factor_method'  => $request->two_factor_method ?? $user->two_factor_method,
        ]);

        $status = $request->two_factor_enabled ? 'enabled' : 'disabled';
        Log::info("2FA {$status} for user: {$user->id}");

        return response()->json([
            'success' => true,
            'message' => "Two-factor authentication {$status} successfully",
            'data' => [
                'two_factor_enabled' => $user->fresh()->two_factor_enabled,
                'two_factor_method'  => $user->fresh()->two_factor_method,
            ],
        ], 200);
    }

    /**
     * Get panel type based on user role
     * 
     * @param User $user
     * @return string
     */
    private function getPanelType(User $user): string
    {
        return $user->getPanelType();
    }

    /**
     * Format user response data
     * 
     * @param User $user
     * @return array
     */
    private function formatUserResponse(User $user): array
    {
        return [
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'phone'               => $user->phone,
            'gender'              => $user->gender,
            'dob'                 => $user->dob?->toDateString(),
            'address'             => $user->address,
            'avatar_url'          => $user->avatar_url,
            'role'                => $user->primaryRole(),
            'panel_type'          => $this->getPanelType($user),
            'two_factor_enabled'  => $user->two_factor_enabled,
            'two_factor_method'   => $user->two_factor_method,
            'notification_preferences' => $user->notification_preferences,
        ];
    }
}
