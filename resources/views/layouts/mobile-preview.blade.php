<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-preview-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="application-name" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#8b2332" id="preview-theme-color">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=4">
    <link rel="icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <title>@yield('title', 'Mobile Preview') | {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen overflow-x-hidden antialiased">
    @yield('content')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {
                    // Ignore preview registration failures.
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
