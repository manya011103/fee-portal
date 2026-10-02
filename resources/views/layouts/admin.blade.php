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
        <aside class="w-64 bg-gray-900 text-white p-4">
            <h2 class="text-xl font-bold mb-6">
                Fee Portal Admin
            </h2>

            <nav class="space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    Dashboard
                </a>

                <a href="{{ route('admin.students.index') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.students.*') ? 'bg-gray-700' : '' }}">
                    Students
                </a>

                <a href="{{ route('admin.students.import.form') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.students.import*') ? 'bg-gray-700' : '' }}">
                    Import Students
                </a>

                <a href="{{ route('admin.payment-report') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.payment-report') ? 'bg-gray-700' : '' }}">
                    Payment Report
                </a>

                <a href="{{ route('admin.scholarship-lapse-report') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.scholarship-lapse-report') ? 'bg-gray-700' : '' }}">
                    Scholarship Lapse Report
                </a>

                <a href="{{ route('admin.no-dues-report') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.no-dues-report') ? 'bg-gray-700' : '' }}">
                    No Dues Report
                </a>

                <a href="{{ route('admin.late-fee-report') }}"
                    class="block px-3 py-2 rounded hover:bg-gray-700
                    {{ request()->routeIs('admin.late-fee-report') ? 'bg-gray-700' : '' }}">
                    Late Fee Collection Report
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1">

            <!-- Top Header -->
            <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h1 class="text-lg font-bold text-gray-800">
                    @yield('title', 'Admin Panel')
                </h1>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Logout
                    </button>
                </form>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>