<?php

namespace App\Modules\Api\Controllers;

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
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'role' => $user->primaryRole(),
            'panel_type' => $this->getPanelType($user),
        ];
    }
}
