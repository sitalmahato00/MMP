@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Account Management</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Settings
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Manage your account settings and preferences</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Profile Settings --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Profile Settings</h2>
                <p class="text-xs text-slate-500">Update your basic profile information</p>
            </div>
            <form action="{{ route('student.settings.profile.update') }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $student->user->name) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $student->user->email) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Update Profile
                    </button>
                </div>
            </form>
        </section>

        {{-- Password Settings --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Change Password</h2>
                <p class="text-xs text-slate-500">Update your account password</p>
            </div>
            <form action="{{ route('student.settings.password.update') }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                        <input type="password" name="password" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <button type="submit" 
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Change Password
                    </button>
                </div>
            </form>
        </section>

        {{-- Preferences --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Preferences</h2>
                <p class="text-xs text-slate-500">Customize your experience</p>
            </div>
            <form action="{{ route('student.settings.preferences.update') }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                
                @php
                    $preferences = $student->preferences ? json_decode($student->preferences, true) : [];
                @endphp

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Language</label>
                        <select name="language" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="en" {{ ($preferences['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ne" {{ ($preferences['language'] ?? 'en') === 'ne' ? 'selected' : '' }}>नेपाली</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date Format</label>
                        <select name="date_format" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="bs" {{ ($preferences['date_format'] ?? 'bs') === 'bs' ? 'selected' : '' }}>Bikram Sambat (BS)</option>
                            <option value="ad" {{ ($preferences['date_format'] ?? 'bs') === 'ad' ? 'selected' : '' }}>Anno Domini (AD)</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Save Preferences
                    </button>
                </div>
            </form>
        </section>

        {{-- Notification Settings --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Notifications</h2>
                <p class="text-xs text-slate-500">Manage your notification preferences</p>
            </div>
            <form action="{{ route('student.settings.notifications.update') }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                
                @php
                    $notifications = $student->notification_preferences ? json_decode($student->notification_preferences, true) : [];
                @endphp

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Email Notices</label>
                            <p class="text-xs text-slate-500">Receive notices via email</p>
                        </div>
                        <input type="checkbox" name="email_notices" value="1" 
                               {{ ($notifications['email_notices'] ?? true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Assignment Notifications</label>
                            <p class="text-xs text-slate-500">Get notified about new assignments</p>
                        </div>
                        <input type="checkbox" name="email_assignments" value="1" 
                               {{ ($notifications['email_assignments'] ?? true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Marks Published</label>
                            <p class="text-xs text-slate-500">Get notified when marks are published</p>
                        </div>
                        <input type="checkbox" name="email_marks" value="1" 
                               {{ ($notifications['email_marks'] ?? true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Attendance Alerts</label>
                            <p class="text-xs text-slate-500">Get alerts about attendance issues</p>
                        </div>
                        <input type="checkbox" name="email_attendance" value="1" 
                               {{ ($notifications['email_attendance'] ?? false) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </div>

                    <button type="submit" 
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Save Notifications
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- Account Actions --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Account Actions</h2>
            <p class="text-xs text-slate-500">Manage your account and data</p>
        </div>
        <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <form action="{{ route('student.settings.logout-all') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            onclick="return confirm('Are you sure you want to logout from all devices?')">
                        Logout All Devices
                    </button>
                </form>

                <form action="{{ route('student.settings.reset-dashboard') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            onclick="return confirm('Are you sure you want to reset your dashboard?')">
                        Reset Dashboard
                    </button>
                </form>

                <form action="{{ route('student.settings.clear-preferences') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                            onclick="return confirm('Are you sure you want to clear all preferences?')">
                        Clear All Preferences
                    </button>
                </form>

                <a href="{{ route('student.profile.show') }}" 
                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 text-center">
                    View Full Profile
                </a>
            </div>
        </div>
    </section>
</div>
@endsection