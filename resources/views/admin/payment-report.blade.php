@extends('layouts.admin')

@section('title', 'Payment Report')

@section('content')

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.payment-report') }}"
        class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 flex flex-wrap items-end gap-4">

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Class</label>
            <select name="class" class="border rounded px-3 py-2 text-sm">
                <option value="">All Classes</option>
                @foreach ($classes as $c)
                    <option value="{{ $c }}" {{ $class === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>

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

        <a href="{{ route('admin.payment-report') }}" class="text-gray-500 hover:text-gray-700 border border-gray-300 hover:border-gray-400 px-4 py-1.5 rounded text-sm font-medium">
                    Reset
        </a>
    </form>

    <!-- Summary -->
    <div class="bg-green-50 border border-green-100 rounded-lg p-4 mb-6 flex justify-between items-center">
        <p class="text-sm text-green-700">
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @if ($class) &middot; {{ $class }} @endif
            &middot; {{ $payments->total() }} payment(s)
        </p>
        <p class="text-lg font-bold text-green-700">Total: ₹{{ number_format($totalAmount) }}</p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Enrollment No</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Mobile</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Payment Mode</th>
                    <th class="px-4 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->enrollment_no ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->class_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->mobile ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium">₹{{ number_format($payment->amount) }}</td>
                        <td class="px-4 py-3 capitalize">{{ $payment->payment_mode ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            No payments found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
@endsection