<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | MMP CMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-blue-200 mb-4 transform rotate-3">
                    <svg class="w-8 h-8 text-white -rotate-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-700 to-blue-500 bg-clip-text text-transparent">MMP CMS</h1>
                <p class="text-gray-500 text-sm mt-1">Manage everything, securely.</p>
            </div>

            @if ($errors->any())
                <x-alert type="error" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm py-2.5 px-3 border transition-colors">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500 hover:underline">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm py-2.5 px-3 border transition-colors">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-xl shadow-md transition-all active:scale-[0.98] outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Sign in to Account
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center">Protected System Area. Unauthorized access is forbidden.</p>
            </div>
        </div>
    </div>
</body>
</html>
