<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'My Fee Details') - Fee Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h2 class="font-bold text-lg">Fee Portal</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-red-600">Logout</button>
        </form>
    </nav>

    <main class="p-6 max-w-4xl mx-auto">
        @yield('content')
    </main>
</body>
</html>