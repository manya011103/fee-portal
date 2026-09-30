@extends('layouts.admin')

@section('title', 'Late Fee Collection Report')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Late Fee Collection Report</h1>

    <form method="GET" action="{{ route('admin.late-fee-report') }}"
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

        <a href="{{ route('admin.late-fee-report') }}" class="text-sm text-gray-500 hover:underline py-2">
            Reset
        </a>
    </form>

    <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 mb-6 flex justify-between items-center">
        <p class="text-sm text-orange-700">
            {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @if ($class) &middot; {{ $class }} @endif
            &middot; {{ $payments->total() }} record(s)
        </p>
        <p class="text-lg font-bold text-orange-700">Total Late Fee: ₹{{ number_format($totalFine) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Enrollment No</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Fine Amount</th>
                    <th class="px-4 py-3">Due Date</th>
                    <th class="px-4 py-3">Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->enrollment_no ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->class_name ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-orange-700">₹{{ number_format($payment->fine_portion) }}</td>
                        <td class="px-4 py-3">{{ $payment->feeRecord->student->getEffectiveDueDate()->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No late fee records found for the selected filters.
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