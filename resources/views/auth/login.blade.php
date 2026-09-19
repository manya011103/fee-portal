<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Fee Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h2 class="text-xl font-bold mb-6 text-center">Fee Portal Login</h2>

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

        <form method="POST" action="{{ route('otp.send') }}">
            @csrf
            <label class="block text-sm font-medium mb-1">Mobile Number</label>
            <input
                type="text"
                name="mobile"
                inputmode="numeric"
                required
                autofocus
                class="w-full border rounded px-3 py-2 mb-4"
                placeholder="Enter your mobile number">

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
                Send OTP
            </button>
        </form>
    </div>
</body>
</html>