<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 dark:bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full dark:bg-slate-900">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                @php $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion(); @endphp
                <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center shadow-md mb-4 border-[3px] border-[#DAA520]" style="background: radial-gradient(circle, #003D82, #001F4D);">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="text-2xl font-bold text-white">MMP</span>
                    @endif
                </div>
            </div>

            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Enter the verification code sent to your {{ session('2fa_method', 'email') }}
            </p>
            
            <!-- Countdown Timer -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Code expires in: <span id="countdown" class="font-bold text-red-600">60</span> seconds
                </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 dark:bg-slate-800">
                @if (session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.2fa.verify') }}" id="otpForm">
                    @csrf

                    <div>
                        <label for="otp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Verification Code
                        </label>
                        <div class="mt-1">
                            <input 
                                id="otp" 
                                name="otp" 
                                type="text" 
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                required 
                                autofocus
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#003D82] focus:border-[#003D82] sm:text-sm dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:placeholder-slate-400 @error('otp') border-red-300 @enderror"
                                placeholder="Enter 6-digit code"
                            >
                        </div>
                        @error('otp')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <button 
                            type="submit" 
                            id="verifyBtn"
                            class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium text-white transition-all focus:outline-none"
                            style="background-color: #003D82; box-shadow: 0 2px 4px rgba(0,0,0,0.15);"
                            onmouseover="this.style.backgroundColor=document.documentElement.classList.contains('dark')?'#1d4ed8':'#001F4D'"
                            onmouseout="this.style.backgroundColor=document.documentElement.classList.contains('dark')?'#2563eb':'#003D82'"
                            x-data x-init="$el.style.backgroundColor = document.documentElement.classList.contains('dark') ? '#2563eb' : '#003D82'"
                        >
                            Verify Code
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <form method="POST" action="{{ route('login.2fa.resend') }}" id="resendForm">
                        @csrf
                        <button 
                            type="submit" 
                            id="resendBtn"
                            class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium transition-all focus:outline-none"
                            style="background-color: transparent; color: #003D82; border: 1px solid #003D82;"
                            onmouseover="if(document.documentElement.classList.contains('dark')){this.style.backgroundColor='#2563eb';this.style.color='#fff';this.style.borderColor='#2563eb';}else{this.style.backgroundColor='#003D82';this.style.color='#fff';this.style.borderColor='#003D82';}"
                            onmouseout="if(document.documentElement.classList.contains('dark')){this.style.backgroundColor='transparent';this.style.color='#60a5fa';this.style.borderColor='#60a5fa';}else{this.style.backgroundColor='transparent';this.style.color='#003D82';this.style.borderColor='#003D82';}"
                            x-data x-init="if(document.documentElement.classList.contains('dark')){$el.style.color='#60a5fa';$el.style.borderColor='#60a5fa';}"
                        >
                            Resend Code
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-[#003D82] hover:text-[#001F4D] dark:text-blue-400 dark:hover:text-blue-300">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // OTP Countdown Timer (60 seconds)
        let timeLeft = 60;
        const countdownElement = document.getElementById('countdown');
        const otpForm = document.getElementById('otpForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const otpInput = document.getElementById('otp');

        const countdown = setInterval(function() {
            timeLeft--;
            countdownElement.textContent = timeLeft;

            if (timeLeft <= 10) {
                countdownElement.classList.add('text-red-700', 'font-extrabold');
            }

            if (timeLeft <= 0) {
                clearInterval(countdown);
                countdownElement.textContent = 'EXPIRED';
                countdownElement.classList.add('text-red-800');
                
                // Disable form
                otpInput.disabled = true;
                verifyBtn.disabled = true;
                verifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Show message
                const expiredMsg = document.createElement('div');
                expiredMsg.className = 'mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-center';
                expiredMsg.textContent = 'OTP has expired. Please request a new code.';
                otpForm.insertAdjacentElement('afterend', expiredMsg);
            }
        }, 1000);

        // Reset countdown when resending
        resendBtn.addEventListener('click', function() {
            clearInterval(countdown);
        });
    </script>
</body>
</html>
