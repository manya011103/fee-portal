@extends('layouts.admin')

@section('title', 'No Dues Report')

@section('content')
    <h1 class="text-2xl font-bold mb-6">No Dues Report</h1>

    <form method="GET" action="{{ route('admin.no-dues-report') }}"
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

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium">
            Apply Filter
        </button>

        <a href="{{ route('admin.no-dues-report') }}"class="text-gray-500 hover:text-gray-700 border border-gray-300 hover:border-gray-400 px-4 py-1.5 rounded text-sm font-medium">
                    Reset
        </a>
    </form>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Enrollment No</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Payment Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    @php
                        $lastPaymentDate = $record->payments->max('payment_date');
                    @endphp
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $record->student->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $record->student->enrollment_no ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $record->class_name }}</td>
                        <td class="px-4 py-3">{{ $lastPaymentDate ? $lastPaymentDate->format('d M Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No fully-paid records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $records->links() }}
    </div>
@endsection