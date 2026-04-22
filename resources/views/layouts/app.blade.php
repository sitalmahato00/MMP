<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=2">
    <meta name="application-name" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#8B0000">
    <link rel="icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    
    <title>@yield('title', 'MMP CMS') | {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full overflow-x-hidden text-gray-800 antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('mmp.sidebar.collapsed') === '1' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('mmp.sidebar.collapsed', value ? '1' : '0'))">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm lg:hidden" x-cloak @click="sidebarOpen = false"></div>

    <div class="flex h-full w-full overflow-x-hidden">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <!-- Navbar -->
            <x-navbar />

            <!-- Page Content -->
            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden bg-gray-50 p-3 sm:p-4 md:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-full">
                    @if (session('success'))
                        <x-alert type="success" :message="session('success')" class="mb-6" />
                    @endif

                    @if (session('error'))
                        {{-- Only show error if it's relevant to current state --}}
                        @php
                            $showError = true;
                            // Don't show "no department" error if user actually has a department now
                            if (str_contains(session('error'), 'department is assigned') && 
                                auth()->check() && 
                                auth()->user()->hasRole('hod')) {
                                $dept = \App\Models\Department::where('hod_id', auth()->id())->first();
                                if ($dept) {
                                    $showError = false;
                                }
                            }
                        @endphp
                        @if($showError)
                            <x-alert type="error" :message="session('error')" class="mb-6" />
                        @endif
                    @endif

                    @yield('content')

                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    console.log('SW registered');
                }).catch(err => {
                    console.log('SW registration failed', err);
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
