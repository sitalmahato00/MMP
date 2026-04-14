<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- PWA Requirements -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="apple-touch-icon" href="/icon-192.png">
    
    <title>@yield('title', 'MMP CMS') | {{ config('app.name') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden text-gray-800 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm lg:hidden" x-cloak @click="sidebarOpen = false"></div>

    <div class="flex h-full">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <!-- Navbar -->
            <x-navbar />

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6 lg:p-8">
                
                @if (session('success'))
                    <x-alert type="success" :message="session('success')" class="mb-6" />
                @endif
                
                @if (session('error'))
                    <x-alert type="error" :message="session('error')" class="mb-6" />
                @endif

                @yield('content')
                
                {{ $slot ?? '' }}

            </main>
        </div>
    </div>
    
    <!-- PWA Service Worker Registration -->
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
</body>
</html>
