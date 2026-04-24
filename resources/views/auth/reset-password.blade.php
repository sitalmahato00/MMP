<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | Manmohan Memorial Polytechnic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex min-h-screen items-center justify-center p-4" style="background-image: url('{{ asset('assets/bg-pattern.png') }}'); background-blend-mode: overlay; background-color: #f9fafb;">
    <div class="w-full max-w-md overflow-hidden rounded-md border-t-4 border-[#003D82] bg-white shadow-[0_8px_30px_rgb(0,0,0,0.12)]">
        <div class="p-8">
            <div class="mb-8 text-center">
                <a href="{{ route('home') }}" class="inline-block transition-opacity hover:opacity-90">
                    @php $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion(); @endphp
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-[3px] border-[#DAA520] shadow-md" style="background: radial-gradient(circle, #003D82, #001F4D);">
                        @if($brandLogoUrl)
                            <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="h-full w-full rounded-full object-cover">
                        @else
                            <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        @endif
                    </div>
                </a>
                <h1 class="font-serif text-2xl font-black tracking-tight text-[#003D82]">Choose a New Password</h1>
                <p class="mt-1 text-sm font-medium text-gray-500">Finish resetting your MMP portal password</p>
            </div>

            @if ($errors->any())
                <x-alert type="error" :message="$errors->first()" class="mb-4" />
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus class="w-full rounded border border-gray-300 px-3 py-2.5 text-sm shadow-sm outline-none transition-colors focus:border-[#003D82] focus:ring-[#003D82]">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">New Password</label>
                    <input type="password" name="password" required class="w-full rounded border border-gray-300 px-3 py-2.5 text-sm shadow-sm outline-none transition-colors focus:border-[#003D82] focus:ring-[#003D82]">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded border border-gray-300 px-3 py-2.5 text-sm shadow-sm outline-none transition-colors focus:border-[#003D82] focus:ring-[#003D82]">
                </div>

                <button type="submit" class="mt-2 w-full rounded bg-[#003D82] px-4 py-3 text-sm font-bold uppercase tracking-wider text-white shadow-md transition-all hover:bg-[#001F4D] focus:ring-2 focus:ring-[#003D82] focus:ring-offset-2">
                    Save New Password
                </button>
            </form>

            <div class="mt-8 flex items-center justify-between border-t border-gray-100 pt-6">
                <a href="{{ route('login') }}" class="flex items-center gap-1 text-xs font-bold text-gray-500 transition-colors hover:text-[#003D82]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Login
                </a>
                <p class="text-[10px] font-medium text-gray-400">Password Update</p>
            </div>
        </div>
    </div>
</body>
</html>
