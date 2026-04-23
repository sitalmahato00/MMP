<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Manmohan Memorial Polytechnic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4" style="background-image: url('{{ asset('assets/bg-pattern.png') }}'); background-blend-mode: overlay; background-color: #f9fafb;">
    <div class="w-full max-w-md bg-white rounded-md shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden border-t-4 border-[#003D82]">
        <div class="p-8">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block hover:opacity-90 transition-opacity">
                    @php $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion(); @endphp
                    <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center shadow-md mb-4 border-[3px] border-[#DAA520]" style="background: radial-gradient(circle, #003D82, #001F4D);">
                        @if($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-full">
                        @else
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>
                </a>
                <h1 class="text-2xl font-black text-[#003D82] font-serif tracking-tight">MMP Portal</h1>
                <p class="text-gray-500 text-sm mt-1 font-medium">Secure System Authentication</p>
            </div>

            @if ($errors->any())
                <x-alert type="error" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded border-gray-300 focus:border-[#003D82] focus:ring-[#003D82] shadow-sm py-2.5 px-3 border transition-colors outline-none text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-bold text-gray-700">Password</label>
                        <a href="#" class="text-xs font-bold text-[#003D82] hover:text-blue-900 hover:underline">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required class="w-full rounded border-gray-300 focus:border-[#003D82] focus:ring-[#003D82] shadow-sm py-2.5 px-3 border transition-colors outline-none text-sm">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-[#003D82] rounded border-gray-300 focus:ring-[#003D82]">
                    <label for="remember" class="ml-2 block text-sm font-medium text-gray-700">Remember me</label>
                </div>

                <button type="submit" class="w-full bg-[#003D82] hover:bg-[#001F4D] text-white font-bold py-3 px-4 rounded shadow-md transition-all active:scale-[0.98] outline-none focus:ring-2 focus:ring-[#003D82] focus:ring-offset-2 uppercase tracking-wider text-sm mt-2">
                    Sign in to Account
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-xs font-bold text-gray-500 hover:text-[#003D82] flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Home
                </a>
                <p class="text-[10px] text-gray-400 font-medium">Restricted Access</p>
            </div>
        </div>
    </div>
</body>
</html>
