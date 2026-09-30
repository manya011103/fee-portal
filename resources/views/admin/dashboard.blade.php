@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <!-- Date Range Filter -->
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium">
            Apply Filter
        </button>
    </form>

    <!-- Financial Summary -->
    <h2 class="text-lg font-bold mb-3 text-gray-700">Financial Summary ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Collected</p>
            <p class="text-2xl font-bold text-gray-800">₹{{ number_format($totalCollected) }}</p>
        </div>

        <div class="bg-blue-50 p-5 rounded-lg shadow-sm border border-blue-100">
            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">Base Fee Collected</p>
            <p class="text-2xl font-bold text-blue-700">₹{{ number_format($baseFeeCollected) }}</p>
        </div>

        <div class="bg-orange-50 p-5 rounded-lg shadow-sm border border-orange-100">
            <p class="text-xs text-orange-600 uppercase tracking-wide mb-1">Late Fee Collected</p>
            <p class="text-2xl font-bold text-orange-700">₹{{ number_format($fineCollected) }}</p>
        </div>

        <div class="bg-red-50 p-5 rounded-lg shadow-sm border border-red-100">
            <p class="text-xs text-red-600 uppercase tracking-wide mb-1">Scholarship Lapse Collected</p>
            <p class="text-2xl font-bold text-red-700">₹{{ number_format($scholarshipLapseCollected) }}</p>
        </div>
    </div>

    <!-- Student Status -->
    <h2 class="text-lg font-bold mb-3 text-gray-700">Student Status</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Students</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalStudents }}</p>
        </div>

        <div class="bg-green-50 p-5 rounded-lg shadow-sm border border-green-100">
            <p class="text-xs text-green-600 uppercase tracking-wide mb-1">Fully Paid</p>
            <p class="text-2xl font-bold text-green-700">{{ $fullyPaidCount }}</p>
        </div>

        <div class="bg-yellow-50 p-5 rounded-lg shadow-sm border border-yellow-100">
            <p class="text-xs text-yellow-600 uppercase tracking-wide mb-1">Pending</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $pendingCount }}</p>
        </div>

        <div class="bg-red-50 p-5 rounded-lg shadow-sm border border-red-100">
            <p class="text-xs text-red-600 uppercase tracking-wide mb-1">Overdue</p>
            <p class="text-2xl font-bold text-red-700">{{ $overdueCount }}</p>
        </div>
    </div>

    <!-- Reminder Queue Status -->
    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 mb-8">
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Reminders Currently Queued</p>
        <p class="text-2xl font-bold text-gray-800">{{ $queuedReminders }}</p>
    </div>

    <!-- Recent Payments -->
    <h2 class="text-lg font-bold mb-3 text-gray-700">Recent Payments</h2>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Mode</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentPayments as $payment)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->class_name ?? '-' }}</td>
                        <td class="px-4 py-3">₹{{ number_format($payment->amount) }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 capitalize">{{ $payment->payment_mode }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection