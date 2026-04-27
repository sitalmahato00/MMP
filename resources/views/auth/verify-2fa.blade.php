<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                @php $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion(); @endphp
                <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center shadow-md border-[3px] border-[#DAA520]" style="background: radial-gradient(circle, #003D82, #001F4D);">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="Logo" class="w-12 h-12 object-contain">
                    @else
                        <span class="text-2xl font-bold text-white">MMP</span>
                    @endif
                </div>
            </div>

            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Two-Factor Authentication
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter the verification code sent to your {{ session('2fa_method', 'email') }}
            </p>
            
            <!-- Countdown Timer -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Code expires in: <span id="countdown" class="font-bold text-red-600">60</span> seconds
                </p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
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
                        <label for="otp" class="block text-sm font-medium text-gray-700">
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
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('otp') border-red-300 @enderror"
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
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Resend Code
                        </button>
                    </form>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
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
