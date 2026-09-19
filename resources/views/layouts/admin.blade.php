<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - Fee Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white p-4 flex flex-col">
            <h2 class="text-xl font-bold mb-6">Fee Portal Admin</h2>
            <nav class="space-y-2 flex-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.students.index') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.students.*') ? 'bg-gray-700' : '' }}">
                    Students
                </a>
                <a href="{{ route('admin.students.import.form') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700 {{ request()->routeIs('admin.students.import*') ? 'bg-gray-700' : '' }}">
                     Import Students
                </a>
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-3 py-2 rounded hover:bg-red-700 text-red-300">
                    Logout
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>