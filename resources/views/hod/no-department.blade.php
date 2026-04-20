@extends('layouts.app')

@section('title', 'Department Not Assigned')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center">
    <div class="w-full max-w-md">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-8 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <h1 class="mt-4 text-xl font-bold text-slate-900">Department Not Assigned</h1>
            
            <p class="mt-2 text-sm text-slate-600">
                Hello <span class="font-semibold">{{ $userName }}</span>,
            </p>
            
            <p class="mt-3 text-sm text-slate-600">
                You have been designated as a Head of Department (HOD), but you haven't been assigned to a specific department yet.
            </p>
            
            <div class="mt-6 rounded-lg bg-white p-4 text-left">
                <p class="text-xs font-semibold text-slate-700">What to do:</p>
                <ol class="mt-2 space-y-2 text-xs text-slate-600">
                    <li class="flex gap-2">
                        <span class="font-semibold text-slate-900">1.</span>
                        <span>Contact the Principal or System Administrator</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-semibold text-slate-900">2.</span>
                        <span>Ask them to assign you to a department from the <strong>Departments</strong> management page</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-semibold text-slate-900">3.</span>
                        <span>Once assigned, refresh this page to access your HOD dashboard</span>
                    </li>
                </ol>
            </div>
            
            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button onclick="window.location.reload()" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 transition">
                    Refresh Page
                </button>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition sm:w-auto">
                        Logout
                    </button>
                </form>
            </div>
            
            <p class="mt-4 text-xs text-slate-500">
                Your email: <span class="font-medium">{{ $userEmail }}</span>
            </p>
        </div>
    </div>
</div>
@endsection
