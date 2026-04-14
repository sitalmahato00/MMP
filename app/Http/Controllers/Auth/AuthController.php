<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        // Audit login
        \App\Models\AuditLog::log('login');

        return redirect()->intended($this->redirectByRole());
    }

    public function logout(Request $request)
    {
        \App\Models\AuditLog::log('logout');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user to their role-specific dashboard.
     */
    protected function redirectByRole(): string
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('principal') => route('admin.dashboard'),
            $user->hasRole('hod') => route('hod.dashboard'),
            $user->hasRole('teacher') => route('teacher.dashboard'),
            $user->hasRole('student') => route('student.dashboard'),
            $user->hasRole('parent') => route('parent.dashboard'),
            $user->hasRole('alumni') => route('alumni.dashboard'),
            default => route('home'),
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
