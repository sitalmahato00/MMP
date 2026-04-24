@extends('layouts.app')
@section('title', 'Account Settings')

@section('content')

<div class="mx-auto max-w-7xl">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Account Settings</h1>
        <p class="mt-1 text-sm text-slate-500">Manage your personal account preferences and security</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6" x-data="{ activeTab: 'profile', hasChanges: false }">
        {{-- Sidebar Navigation --}}
        <aside class="w-full lg:w-64 shrink-0">
            <nav class="lg:sticky lg:top-6 space-y-1 rounded-xl border border-slate-200/80 bg-white p-2 shadow-sm">
                <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </button>
                
                <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Security
                </button>
                
                <button @click="activeTab = 'notifications'" :class="activeTab === 'notifications' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifications
                </button>
                
                <div class="my-2 border-t border-slate-100"></div>
                
                <button @click="activeTab = 'danger'" :class="activeTab === 'danger' ? 'bg-red-50 text-red-700' : 'text-slate-600 hover:bg-red-50 hover:text-red-600'" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Danger Zone
                </button>
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="min-w-0 flex-1">
            {{-- Profile Settings --}}
            <div x-show="activeTab === 'profile'" x-cloak class="space-y-6">
                <form action="{{ route('student.settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    
                    {{-- Profile Photo & Basic Info --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="text-base font-semibold text-slate-900">Profile Information</h2>
                            <p class="mt-1 text-sm text-slate-500">Update your personal information and profile photo</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid gap-6 lg:grid-cols-[1fr_300px]">
                                {{-- Form Fields --}}
                                <div class="space-y-5 order-2 lg:order-1">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                        @error('name')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                                        <input type="email" value="{{ $user->email }}" disabled class="block w-full rounded-lg border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-500 shadow-sm cursor-not-allowed">
                                        <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Email cannot be changed as it's tied to your login
                                        </p>
                                    </div>

                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number</label>
                                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                            @error('phone')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Gender</label>
                                            <select name="gender" class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Date of Birth</label>
                                        <x-bs-date-picker 
                                            name="dob" 
                                            :value="old('dob', $user->dob?->format('Y-m-d'))" 
                                            placeholder="YYYY-MM-DD"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Address</label>
                                        <textarea name="address" rows="3" class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none resize-none">{{ old('address', $user->address) }}</textarea>
                                    </div>
                                </div>

                                {{-- Profile Card Preview --}}
                                <div class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-blue-50/30 p-6 order-1 lg:order-2">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Preview</p>
                                    <div class="mt-4 text-center">
                                        <div class="relative mx-auto h-24 w-24" x-data="{ preview: '{{ $user->avatar_url }}' }">
                                            <img :src="preview" alt="Profile" class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg">
                                            <label class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-blue-600 text-white shadow-lg transition hover:bg-blue-700">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <input type="file" name="avatar" accept="image/*" class="hidden" @change="preview = URL.createObjectURL($event.target.files[0])">
                                            </label>
                                        </div>
                                        <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $user->name }}</h3>
                                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                                        
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs">
                                                <span class="text-slate-500">Role</span>
                                                <span class="font-semibold text-slate-900">Student</span>
                                            </div>
                                            @if($user->student)
                                            <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs">
                                                <span class="text-slate-500">Student ID</span>
                                                <span class="font-semibold text-slate-900">{{ $user->student->student_no }}</span>
                                            </div>
                                            @if($user->student->program)
                                            <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs">
                                                <span class="text-slate-500">Program</span>
                                                <span class="font-semibold text-slate-900">{{ $user->student->program->name }}</span>
                                            </div>
                                            @endif
                                            <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs">
                                                <span class="text-slate-500">Semester</span>
                                                <span class="font-semibold text-slate-900">{{ $user->student->current_semester }}</span>
                                            </div>
                                            @if($user->student->section)
                                            <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-xs">
                                                <span class="text-slate-500">Section</span>
                                                <span class="font-semibold text-slate-900">{{ $user->student->section }}</span>
                                            </div>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <p class="text-xs text-slate-500">Changes will be saved immediately</p>
                                <button type="submit" class="w-full sm:w-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Security Settings --}}
            <div x-show="activeTab === 'security'" x-cloak class="space-y-6">
                {{-- Change Password --}}
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
                                <p class="mt-1 text-sm text-slate-500">Update your password regularly for security</p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('student.settings.password.update') }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Current Password</label>
                                <input type="password" name="current_password" required class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                                @error('current_password')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">New Password</label>
                                <input type="password" name="password" required class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none" x-data @input="
                                    const val = $event.target.value;
                                    const strength = val.length < 8 ? 0 : (val.match(/[a-z]/) && val.match(/[A-Z]/) && val.match(/[0-9]/) && val.match(/[^a-zA-Z0-9]/) ? 100 : val.match(/[a-z]/) && val.match(/[A-Z]/) && val.match(/[0-9]/) ? 66 : 33);
                                    $refs.strength.style.width = strength + '%';
                                    $refs.strength.className = 'h-full rounded-full transition-all ' + (strength === 100 ? 'bg-green-500' : strength >= 66 ? 'bg-yellow-500' : 'bg-red-500');
                                ">
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div x-ref="strength" class="h-full rounded-full bg-red-500 transition-all" style="width: 0%"></div>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Use 8+ characters with uppercase, lowercase, numbers & symbols
                                </p>
                                @error('password')<p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
                                <input type="password" name="password_confirmation" required class="block w-full rounded-lg border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="w-full sm:w-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Two-Factor Authentication --}}
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50">
                                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900">Two-Factor Authentication</h2>
                                    <p class="mt-1 text-sm text-slate-500">Add an extra layer of security to your account</p>
                                </div>
                            </div>
                            @if($user->two_factor_enabled)
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Enabled</span>
                            @else
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Disabled</span>
                            @endif
                        </div>
                    </div>
                    
                    <form action="{{ route('student.settings.two-factor.update') }}" method="POST" class="p-6">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-5">
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Enable Two-Factor Authentication</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Require OTP verification when logging in</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="two_factor_enabled" value="1" {{ $user->two_factor_enabled ? 'checked' : '' }} class="peer sr-only" onchange="document.getElementById('2fa-method-section').classList.toggle('hidden', !this.checked)">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-green-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300"></div>
                                </label>
                            </div>

                            <div id="2fa-method-section" class="{{ $user->two_factor_enabled ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Verification Method</label>
                                <div class="space-y-3">
                                    <label class="flex items-center gap-3 rounded-lg border-2 border-slate-200 bg-white px-4 py-3 cursor-pointer transition hover:border-blue-300 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                        <input type="radio" name="two_factor_method" value="email" {{ $user->two_factor_method === 'email' ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span class="text-sm font-semibold text-slate-900">Email</span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">Send OTP to {{ $user->email }}</p>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 rounded-lg border-2 border-slate-200 bg-slate-50 px-4 py-3 opacity-60 cursor-not-allowed">
                                        <input type="radio" name="two_factor_method" value="phone" disabled class="h-4 w-4 text-slate-400">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                <span class="text-sm font-semibold text-slate-600">Phone (SMS)</span>
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Coming Soon</span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">SMS verification will be available soon</p>
                                        </div>
                                    </label>
                                </div>
                                @error('two_factor_method')<p class="mt-2 text-xs text-red-600 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="w-full sm:w-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                Save 2FA Settings
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Active Sessions --}}
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50">
                                <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">Active Sessions</h2>
                                <p class="mt-1 text-sm text-slate-500">Manage devices where you're currently logged in</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-100">
                        @foreach($activeSessions as $session)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100">
                                    <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-slate-900">{{ $session['device'] }}</p>
                                        @if($session['is_current'])
                                        <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Current</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $session['ip'] }} • Last active {{ $session['last_active']->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <form action="{{ route('student.settings.logout-all') }}" method="POST" x-data @submit.prevent="
                            if (confirm('Are you sure you want to logout from all other devices?')) {
                                $el.submit();
                            }
                        ">
                            @csrf
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <p class="text-xs text-slate-500">Logout from all devices except this one</p>
                                <button type="submit" class="w-full sm:w-auto rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">
                                    Logout All Devices
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Login History --}}
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold text-slate-900">Login Activity</h2>
                        <p class="mt-1 text-sm text-slate-500">Recent login attempts and security events</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">Successful login</p>
                                        <p class="text-xs text-slate-500">{{ request()->ip() }} • {{ now()->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifications Settings --}}
            <div x-show="activeTab === 'notifications'" x-cloak class="space-y-6">
                <form action="{{ route('student.settings.notifications.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    {{-- Email Notifications --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900">Email Notifications</h2>
                                    <p class="mt-1 text-sm text-slate-500">Receive updates via email at {{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-slate-100 p-6">
                            <div class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Assignment Alerts</p>
                                    <p class="mt-0.5 text-xs text-slate-500">New assignments and submission deadlines</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="email_assignment_alerts" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Exam Alerts</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Exam schedules and result announcements</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="email_exam_alerts" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Grade Alerts</p>
                                    <p class="mt-0.5 text-xs text-slate-500">When marks and grades are published</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="email_grade_alerts" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Attendance Alerts</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Attendance warnings and reports</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="email_attendance_alerts" value="1" class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Notice Alerts</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Important notices and announcements</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="email_notice_alerts" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- In-App Notifications --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900">In-App Notifications</h2>
                                    <p class="mt-1 text-sm text-slate-500">Notifications shown in the dashboard</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-slate-100 p-6">
                            <div class="flex items-center justify-between py-4 first:pt-0">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Notices</p>
                                    <p class="mt-0.5 text-xs text-slate-500">New notices and announcements</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="inapp_notices" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Admin Comments</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Comments on your posts and activities</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="inapp_comments" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Assignment Updates</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Assignment submissions and student queries</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="inapp_assignments" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-4 last:pb-0">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Update Reminders</p>
                                    <p class="mt-0.5 text-xs text-slate-500">System updates and maintenance notices</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="inapp_updates" value="1" checked class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SMS Notifications --}}
                    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50">
                                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold text-slate-900">SMS Notifications</h2>
                                    <p class="mt-1 text-sm text-slate-500">Critical alerts sent to {{ $user->phone ?? 'your phone' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Important Alerts Only</p>
                                    <p class="mt-0.5 text-xs text-slate-500">Critical system failures and security alerts</p>
                                </div>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" name="sms_important_alerts" value="1" class="peer sr-only">
                                    <div class="peer h-6 w-11 rounded-full bg-slate-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300"></div>
                                </label>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50 px-6 py-3">
                            <button type="button" class="text-xs font-semibold text-blue-600 transition hover:text-blue-700">
                                Send Test Notification
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Save Notification Preferences
                        </button>
                    </div>
                </form>
            </div>

            {{-- Danger Zone --}}
            <div x-show="activeTab === 'danger'" x-cloak class="space-y-6">
                {{-- Reset Dashboard --}}
                <div class="overflow-hidden rounded-xl border-2 border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-red-900">Reset Dashboard Widgets</h2>
                                <p class="mt-1 text-sm text-red-700">Restore dashboard to default layout</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-sm text-slate-600">This will reset your dashboard widgets and layout to the default configuration. Your other settings will not be affected.</p>
                        
                        <form action="{{ route('student.settings.reset-dashboard') }}" method="POST" class="mt-4" x-data @submit.prevent="
                            if (confirm('Are you sure you want to reset your dashboard? This cannot be undone.')) {
                                $el.submit();
                            }
                        ">
                            @csrf
                            <button type="submit" class="rounded-lg border-2 border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">
                                Reset Dashboard
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Clear Preferences --}}
                <div class="overflow-hidden rounded-xl border-2 border-red-200 bg-white shadow-sm">
                    <div class="border-b border-red-100 bg-red-50 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-red-900">Clear All Preferences</h2>
                                <p class="mt-1 text-sm text-red-700">Reset all settings to system defaults</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-sm text-slate-600">This will clear all your personal preferences including theme, language, notifications, and dashboard settings. You'll need to reconfigure everything.</p>
                        
                        <form action="{{ route('student.settings.clear-preferences') }}" method="POST" class="mt-4" x-data @submit.prevent="
                            if (confirm('Are you sure you want to clear all preferences? This cannot be undone.')) {
                                $el.submit();
                            }
                        ">
                            @csrf
                            <button type="submit" class="rounded-lg border-2 border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">
                                Clear All Preferences
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Delete Account --}}
                <div class="overflow-hidden rounded-xl border-2 border-red-300 bg-white shadow-sm">
                    <div class="border-b border-red-200 bg-red-50 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-200">
                                <svg class="h-5 w-5 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-red-900">Delete Account</h2>
                                <p class="mt-1 text-sm text-red-700">Permanently delete your account</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="rounded-lg border-2 border-red-200 bg-red-50 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-900">Account deletion is disabled</p>
                                    <p class="mt-1 text-xs text-red-700">Principal and Admin accounts cannot be deleted for security reasons. Please contact system administrator if you need to deactivate this account.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@endsection

@push('scripts')
@include('partials.settings.form-state', ['preferences' => $preferences ?? [], 'notificationPreferences' => $notificationPreferences ?? []])
<script>
document.addEventListener('alpine:init', () => {
    // Auto-save indicator
    Alpine.data('settingsForm', () => ({
        hasChanges: false,
        init() {
            this.$watch('hasChanges', value => {
                if (value) {
                    console.log('Changes detected');
                }
            });
        }
    }));
});
</script>
@endpush
