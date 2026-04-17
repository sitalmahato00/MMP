<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=2">
    <meta name="application-name" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#8B0000">
    <link rel="icon" href="{{ route('public.brand-logo') }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}">
    
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

    {{-- Floating Save Button (auto-detects the main save/edit form on the page) --}}
    <div id="floating-save-btn" class="hidden fixed bottom-6 right-6 z-50">
        <button type="button" id="floating-save-trigger"
            class="flex items-center gap-2 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold text-sm px-5 py-3 rounded-full shadow-lg shadow-green-900/30 transition-all duration-150 hover:scale-105 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            Save Changes
        </button>
    </div>
    <script>
    (function () {
        // Find the primary "save" form on this page (has a submit button containing save/create/update text)
        const allForms = Array.from(document.querySelectorAll('form'));
        const mainForm = allForms.find(function (f) {
            const btn = f.querySelector('button[type="submit"]');
            return btn && /save|create|update|add|publish/i.test(btn.textContent.trim());
        });
        if (mainForm) {
            document.getElementById('floating-save-btn').classList.remove('hidden');
            document.getElementById('floating-save-trigger').addEventListener('click', function () {
                mainForm.requestSubmit ? mainForm.requestSubmit() : mainForm.submit();
            });
        }
    })();
    </script>
    
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
