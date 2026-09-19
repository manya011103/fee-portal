<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify OTP - Fee Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h2 class="text-xl font-bold mb-2 text-center">Enter OTP</h2>
        <p class="text-sm text-gray-500 mb-6 text-center">
            OTP expires within 5 minutes.
        </p>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify.submit') }}">
            @csrf
            <label class="block text-sm font-medium mb-1">OTP</label>
            <input
                type="text"
                name="otp"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                required
                autofocus
                autocomplete="one-time-code"
                class="w-full border rounded px-3 py-2 mb-4 text-center text-lg tracking-widest"
                placeholder="••••••">

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
                Verify & Login
            </button>
        </form>

        <!-- Resend Section -->
        <div class="mt-4 text-center text-sm">
            <p id="timer-text" class="text-gray-500">
                Resend OTP in <span id="countdown">30</span>s
            </p>

            <form id="resend-form" method="POST" action="{{ route('otp.resend') }}" class="hidden">
                @csrf
                <button type="submit" class="text-indigo-600 hover:underline font-medium">
                    Resend OTP
                </button>
            </form>
        </div>
    </div>

    <script>
        let seconds = 30;
        const countdownEl = document.getElementById('countdown');
        const timerText = document.getElementById('timer-text');
        const resendForm = document.getElementById('resend-form');

        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;

            if (seconds <= 0) {
                clearInterval(interval);
                timerText.classList.add('hidden');
                resendForm.classList.remove('hidden');
            }
        }, 1000);
    </script>
</body>
</html>