@extends('layouts.app')

@section('title', 'Alumni Settings')

@section('content')
<div class="mx-auto max-w-7xl" x-data="{ activeTab: 'profile' }">
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Account Settings</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your alumni profile, notification channels, and account security.</p>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row">
        <aside class="w-full lg:w-72 shrink-0">
            <nav class="space-y-1 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm lg:sticky lg:top-6">
                <button type="button" @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </button>
                <button type="button" @click="activeTab = 'preferences'" :class="activeTab === 'preferences' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
                    Preferences
                </button>
                <button type="button" @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm6 0v5a2 2 0 01-2 2H8a2 2 0 01-2-2v-5m12 0V9a4 4 0 10-8 0v2m8 0H6"/></svg>
                    Security
                </button>
                <button type="button" @click="activeTab = 'notifications'" :class="activeTab === 'notifications' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </button>
                <button type="button" @click="activeTab = 'danger'" :class="activeTab === 'danger' ? 'bg-red-50 text-red-700' : 'text-slate-600 hover:bg-red-50 hover:text-red-600'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-7.938 4h15.876c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L2.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Danger Zone
                </button>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 space-y-6">
            <form x-show="activeTab === 'profile'" x-cloak action="{{ route('alumni.settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Profile Information</h2>
                        <p class="mt-1 text-sm text-slate-500">Keep your alumni profile current for networking and opportunities.</p>
                    </div>
                    <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                        <div class="space-y-5">
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Full Name</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email Address</label>
                                    <input type="email" value="{{ $user->email }}" disabled class="block w-full rounded-xl border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-500 shadow-sm">
                                </div>
                            </div>
                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Gender</label>
                                    <select name="gender" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                        <option value="">Select gender</option>
                                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Male</option>
                                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Female</option>
                                        <option value="other" @selected(old('gender', $user->gender) === 'other')>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Date of Birth</label>
                                    <x-bs-date-picker name="dob" :value="old('dob', $user->dob?->format('Y-m-d'))" placeholder="YYYY-MM-DD" />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Address</label>
                                <textarea name="address" rows="3" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">{{ old('address', $user->address) }}</textarea>
                            </div>
                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Current Role</label>
                                    <input type="text" name="current_job" value="{{ old('current_job', $user->alumnus?->current_job) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Company</label>
                                    <input type="text" name="company_name" value="{{ old('company_name', $user->alumnus?->company_name) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Work Location</label>
                                    <input type="text" name="work_location" value="{{ old('work_location', $user->alumnus?->work_location) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                            </div>
                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $user->alumnus?->linkedin_url) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">GitHub URL</label>
                                    <input type="url" name="github_url" value="{{ old('github_url', $user->alumnus?->github_url) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Portfolio URL</label>
                                    <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $user->alumnus?->portfolio_url) }}" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Bio</label>
                                <textarea name="bio" rows="4" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">{{ old('bio', $user->alumnus?->bio) }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Skills</label>
                                <input type="text" name="skills" value="{{ old('skills', $user->alumnus?->skills ? implode(', ', $user->alumnus->skills) : '') }}" placeholder="Laravel, PHP, UI Design" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="text-center">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg">
                                <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $user->name }}</h3>
                                <p class="text-sm text-slate-500">Alumni Portal</p>
                            </div>
                            <div class="mt-5 space-y-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Profile Photo</label>
                                    <input type="file" name="avatar" accept="image/*" class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 shadow-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200">
                                </div>
                                <div class="rounded-xl bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Department</p>
                                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $user->alumnus?->department?->name ?? 'Not linked' }}</p>
                                </div>
                                <div class="rounded-xl bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-slate-400">Program</p>
                                    <p class="mt-1 text-sm font-medium text-slate-900">{{ $user->alumnus?->program?->name ?? 'Not linked' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save Profile</button>
                        </div>
                    </div>
                </div>
            </form>

            <form x-show="activeTab === 'preferences'" x-cloak action="{{ route('alumni.settings.preferences.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Portal Preferences</h2>
                        <p class="mt-1 text-sm text-slate-500">Personalize how the alumni portal behaves for you.</p>
                    </div>
                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Theme</label>
                            <select name="theme" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                                <option value="auto">Auto</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Language</label>
                            <select name="language" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="en">English</option>
                                <option value="ne">Nepali</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Date Format</label>
                            <select name="date_format" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="bs">Bikram Sambat</option>
                                <option value="ad">Gregorian</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Default Page</label>
                            <select name="default_page" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="dashboard">Dashboard</option>
                                <option value="profile">Profile</option>
                                <option value="projects">Projects</option>
                                <option value="career">Career</option>
                                <option value="achievements">Achievements</option>
                                <option value="notices">Notices</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Dashboard Layout</label>
                            <select name="dashboard_layout" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="compact">Compact</option>
                                <option value="comfortable">Comfortable</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Table Density</label>
                            <select name="table_density" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="normal">Normal</option>
                                <option value="compact">Compact</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Pagination Size</label>
                            <select name="pagination_size" class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Use Nepali Numerals</p>
                                <p class="mt-0.5 text-xs text-slate-500">Show dates and counts in Nepali digits where supported.</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="nepali_numbers" value="1" class="peer sr-only">
                                <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                            </label>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save Preferences</button>
                        </div>
                    </div>
                </div>
            </form>

            <div x-show="activeTab === 'security'" x-cloak class="space-y-6">
                <form action="{{ route('alumni.settings.password.update') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @csrf
                    @method('PATCH')
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
                        <p class="mt-1 text-sm text-slate-500">Keep your alumni account secure with a strong password.</p>
                    </div>
                    <div class="grid gap-5 p-6 md:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Current Password</label>
                            <input type="password" name="current_password" required class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">New Password</label>
                            <input type="password" name="password" required class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" required class="block w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Update Password</button>
                        </div>
                    </div>
                </form>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Active Sessions</h2>
                        <p class="mt-1 text-sm text-slate-500">Review the devices currently signed into your account.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($activeSessions as $session)
                            <div class="flex items-center justify-between px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $session['device'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $session['ip'] }} · Last active {{ $session['last_active']->diffForHumans() }}</p>
                                </div>
                                @if($session['is_current'])
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Current</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <form action="{{ route('alumni.settings.logout-all') }}" method="POST" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            @csrf
                            <div>
                                <p class="text-sm font-medium text-slate-900">Sign out other devices</p>
                                <p class="mt-0.5 text-xs text-slate-500">You will stay signed in on this current session.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="password" name="password" required placeholder="Current password" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none sm:w-56">
                                <button type="submit" class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">Logout All</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <form x-show="activeTab === 'notifications'" x-cloak action="{{ route('alumni.settings.notifications.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Notification Channels</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose how you want to receive alumni community updates.</p>
                    </div>
                    <div class="grid gap-6 p-6 lg:grid-cols-2">
                        <div class="space-y-4 rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-900">Email Notifications</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Notice alerts</p>
                                    <p class="text-xs text-slate-500">General alumni notices and announcements.</p>
                                </div>
                                <input type="checkbox" name="email_notice_alerts" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Event alerts</p>
                                    <p class="text-xs text-slate-500">Reunions, meetups, and campus events.</p>
                                </div>
                                <input type="checkbox" name="email_event_alerts" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Career alerts</p>
                                    <p class="text-xs text-slate-500">Professional opportunities and alumni highlights.</p>
                                </div>
                                <input type="checkbox" name="email_career_alerts" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="space-y-4 rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-900">In-App Notifications</h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Notices</p>
                                    <p class="text-xs text-slate-500">Updates inside the alumni portal header and inbox.</p>
                                </div>
                                <input type="checkbox" name="inapp_notices" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Events</p>
                                    <p class="text-xs text-slate-500">Event-specific reminders and invitations.</p>
                                </div>
                                <input type="checkbox" name="inapp_events" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Update reminders</p>
                                    <p class="text-xs text-slate-500">Official CTEVT and system update reminders.</p>
                                </div>
                                <input type="checkbox" name="inapp_updates" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Important SMS alerts</p>
                                    <p class="text-xs text-slate-500">Reserved for critical notices only.</p>
                                </div>
                                <input type="checkbox" name="sms_important_alerts" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Save Notification Preferences</button>
                        </div>
                    </div>
                </div>
            </form>

            <div x-show="activeTab === 'danger'" x-cloak class="space-y-6">
                <div class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 px-6 py-4">
                        <h2 class="text-base font-semibold text-red-900">Reset Dashboard</h2>
                        <p class="mt-1 text-sm text-red-700">Restore your alumni dashboard widgets to the default layout.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('alumni.settings.reset-dashboard') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">Reset Dashboard</button>
                        </form>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 px-6 py-4">
                        <h2 class="text-base font-semibold text-red-900">Clear Preferences</h2>
                        <p class="mt-1 text-sm text-red-700">Reset all saved UI and notification preferences back to defaults.</p>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('alumni.settings.clear-preferences') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-xl border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">Clear All Preferences</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.settings.form-state', ['preferences' => $preferences ?? [], 'notificationPreferences' => $notificationPreferences ?? []])
@endpush
