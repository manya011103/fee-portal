@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Financial Summary (date-filtered) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Financial Summary</h2>
                <p class="text-xs text-gray-400 mt-0.5">Figures below reflect the selected date range only</p>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-1.5 text-sm">
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded text-sm font-medium">
                    Apply
                </button>
                <a href="{{ route('admin.dashboard') }}"
                    class="text-gray-500 hover:text-gray-700 border border-gray-300 hover:border-gray-400 px-4 py-1.5 rounded text-sm font-medium">
                    Reset
                </a>
            </form>
        </div>

        <div class="px-6 py-3 text-xs text-gray-400">
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 pt-0">
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-100">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Collected</p>
                <p class="text-2xl font-bold text-gray-800">₹{{ number_format($totalCollected) }}</p>
            </div>

            <div class="bg-blue-50 p-5 rounded-lg border border-blue-100">
                <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">Base Fee Collected</p>
                <p class="text-2xl font-bold text-blue-700">₹{{ number_format($baseFeeCollected) }}</p>
            </div>

            <div class="bg-orange-50 p-5 rounded-lg border border-orange-100">
                <p class="text-xs text-orange-600 uppercase tracking-wide mb-1">Late Fee Collected</p>
                <p class="text-2xl font-bold text-orange-700">₹{{ number_format($fineCollected) }}</p>
            </div>

            <div class="bg-red-50 p-5 rounded-lg border border-red-100">
                <p class="text-xs text-red-600 uppercase tracking-wide mb-1">Scholarship Lapse Collected</p>
                <p class="text-2xl font-bold text-red-700">₹{{ number_format($scholarshipLapseCollected) }}</p>
            </div>
        </div>
    </div>

    <!-- Student Status (unified bar, NOT date-filtered) -->
    <!-- Student Status (separate cards, always colored) -->
<div class="mb-3 flex items-center justify-between">
    <h2 class="text-lg font-bold text-gray-800">Student Status</h2>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <a href="{{ route('admin.students.index') }}"
        class="bg-gray-50 p-5 rounded-xl border border-gray-200 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Students</p>
        <p class="text-3xl font-bold text-gray-800">{{ $totalStudents }}</p>
    </a>

    <a href="{{ route('admin.students.index') }}"
        class="bg-green-100 p-5 rounded-xl border border-green-200 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="text-xs font-semibold text-green-700 uppercase tracking-wide mb-2">Fully Paid</p>
        <p class="text-3xl font-bold text-green-800">{{ $fullyPaidCount }}</p>
    </a>

    <a href="{{ route('admin.students.index', ['status' => 'pending']) }}"
        class="bg-yellow-100 p-5 rounded-xl border border-yellow-200 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="text-xs font-semibold text-yellow-700 uppercase tracking-wide mb-2">Pending</p>
        <p class="text-3xl font-bold text-yellow-800">{{ $pendingCount }}</p>
    </a>

    <a href="{{ route('admin.students.index', ['status' => 'overdue']) }}"
        class="bg-red-100 p-5 rounded-xl border border-red-200 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="text-xs font-semibold text-red-700 uppercase tracking-wide mb-2">Overdue</p>
        <p class="text-3xl font-bold text-red-800">{{ $overdueCount }}</p>
    </a>

    <div class="bg-blue-100 p-5 rounded-xl border border-blue-200">
        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-2">Reminders Queued</p>
        <p class="text-3xl font-bold text-blue-800">{{ $queuedReminders }}</p>
    </div>
</div>
@endsection