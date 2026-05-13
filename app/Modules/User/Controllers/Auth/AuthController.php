<?php

namespace App\Modules\User\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\CMS\Models\Page;
use App\Modules\Staff\Models\Staff;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\Otp;
use App\Modules\User\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'otp' => 'nullable|string|digits:6',
        ]);

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Please contact support.',
            ]);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            // If OTP is provided, verify it
            if ($request->filled('otp')) {
                $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
                $result = $this->otpService->verifyOtp($identifier, $request->otp);

                if (!$result['success']) {
                    throw ValidationException::withMessages([
                        'otp' => $result['message'],
                    ]);
                }

                // OTP verified, proceed with login
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                \App\Models\AuditLog::log('login');

                return redirect()->intended($this->redirectByRole());
            }

            // 2FA enabled but no OTP provided - send OTP and show verification page
            $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
            $this->otpService->sendOtp($identifier, $user->two_factor_method ?? 'email', $user);

            // Store user ID in session for OTP verification
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            return redirect()->route('login.2fa')->with('success', 'Verification code sent to your ' . ($user->two_factor_method ?? 'email'));
        }

        // No 2FA - proceed with normal login
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        \App\Models\AuditLog::log('login');

        return redirect()->intended($this->redirectByRole());
    }

    public function show2fa()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-2fa');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|digits:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        // Verify OTP
        $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
        $result = $this->otpService->verifyOtp($identifier, $request->otp);

        if (!$result['success']) {
            return back()->withErrors(['otp' => $result['message']]);
        }

        // OTP verified - log user in
        Auth::login($user, session('2fa_remember', false));
        $request->session()->regenerate();
        $request->session()->forget(['2fa_user_id', '2fa_remember']);
        \App\Models\AuditLog::log('login');

        return redirect($this->redirectByRole($user));
    }

    public function resend2fa(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        // Check rate limiting
        $identifier = $user->two_factor_method === 'email' ? $user->email : $user->phone;
        if ($this->otpService->isRateLimited($identifier)) {
            return back()->withErrors(['otp' => 'Please wait before requesting another code.']);
        }

        // Resend OTP
        $this->otpService->sendOtp($identifier, $user->two_factor_method ?? 'email', $user);

        return back()->with('success', 'Verification code resent successfully.');
    }

    public function logout(Request $request)
    {
        \App\Models\AuditLog::log('logout');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect()->route('login');
        
        // Add headers to prevent caching after logout
        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
    }

    /**
     * Redirect user to their role-specific dashboard.
     */
    protected function redirectByRole(?User $user = null): string
    {
        $user ??= auth()->user();

        return match (true) {
            $user->hasRole('principal') => route('admin.dashboard'),
            $user->hasRole('hod')       => route('hod.dashboard'),
            $user->hasRole('teacher')   => route('teacher.dashboard'),
            $user->hasRole('student')   => route('student.dashboard'),
            $user->hasRole('parent')    => route('parent.dashboard'),
            $user->hasRole('alumni')    => route('alumni.dashboard'),
            $user->hasRole('staff')     => route('admin.dashboard'),
            default                     => route('home'),
        };
    }

    /**
     * Authenticated redirect — callable from /dashboard
     */
    public function dashboardRedirect()
    {
        return redirect($this->redirectByRole());
    }
}
