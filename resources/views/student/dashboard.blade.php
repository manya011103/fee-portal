@extends('layouts.student')

@section('title', 'My Details')

@php
    $student = auth('student')->user();
    $student->load('feeRecords');
@endphp

@section('content')
    <!-- Basic Info -->
    <div class="bg-white p-6 rounded shadow mb-6">
        <h1 class="text-2xl font-bold mb-4">{{ $student->name }}</h1>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Enrollment No</p>
                <p class="font-medium">{{ $student->enrollment_no }}</p>
            </div>
            <div>
                <p class="text-gray-500">Mobile</p>
                <p class="font-medium">{{ $student->mobile }}</p>
            </div>
            <div>
                <p class="text-gray-500">Registration Date</p>
                <p class="font-medium">{{ $student->registration_date }}</p>
            </div>
            <div>
                <p class="text-gray-500">Father's Name</p>
                <p class="font-medium">{{ $student->father_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Mother's Name</p>
                <p class="font-medium">{{ $student->mother_name ?? '-' }}</p>
            </div>
        </div>
    </div>



     <!-- Fee Records -->
<h2 class="text-xl font-bold mb-4">Fee Records</h2>

@forelse ($student->feeRecords as $index => $record)
    @php
            $paidTotal = $record->payments()->sum('amount');
            $totalPayable = $record->total_fee - $record->scholarship_fee;
            $fine = $student->getFineAmount();
            $totalFine = $fine + $record->scholarship_fee;
            $dueFee = $totalPayable - $paidTotal; 
            $payableWithFine = $dueFee + $totalFine;
            $modalId = 'fee-history-' . $index;
            $isLate = $student->getDaysLate() > 0;
        @endphp


    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
    <h3 class="font-bold text-lg mb-5 text-gray-800">{{ $record->class_name }}</h3>

    <!-- Due Date Card -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Due Date</p>
            <p class="text-lg font-bold text-gray-800">
                {{ $student->getEffectiveDueDate()->format('d M Y') }}
                @if (! $student->due_date)
                    <span class="text-xs font-normal text-gray-400">(default)</span>
                @endif
            </p>
        </div>

        
    </div>

    @if (session('status'))
        <p class="text-sm text-green-600 font-medium mt-3">{{ session('status') }}</p>
    @endif
</div>


    <!-- Summary Cards -->
<!-- Summary Cards -->
<div class="grid grid-cols-1 gap-3">
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Fee</p>
                    <p class="text-lg font-bold text-gray-800">₹{{ number_format($record->total_fee) }}</p>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 text-center">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Scholarship</p>
                    <p class="text-lg font-bold text-gray-800">₹{{ number_format($record->scholarship_fee) }}</p>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide mb-2">Total Payable</p>
                    <p class="text-lg font-bold text-blue-700">₹{{ number_format($totalPayable) }}</p>
                </div>

                

                <div class="bg-green-50 border border-green-100 rounded-lg p-4 text-center relative">
                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide mb-2 flex items-center justify-center gap-1.5">
                        Total Paid
                        <button type="button"
                            onclick="document.getElementById('{{ $modalId }}').classList.remove('hidden')"
                            title="View fee history"
                            class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-green-600 text-white hover:bg-green-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </p>
                    <p class="text-lg font-bold text-green-700">₹{{ number_format($paidTotal) }}</p>
                </div>


                <div class="{{ $dueFee > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100' }} border rounded-lg p-4 text-center">
                    <p class="text-xs font-medium {{ $dueFee > 0 ? 'text-red-600' : 'text-gray-500' }} uppercase tracking-wide mb-2">Due Fee</p>
                    <p class="text-lg font-bold {{ $dueFee > 0 ? 'text-red-700' : 'text-gray-700' }}">
                        ₹{{ number_format(max($dueFee, 0)) }}
                    </p>
                </div>

                @if ($isLate && $dueFee > 0)
            <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 text-center">
                <p class="text-xs font-medium text-orange-600 uppercase tracking-wide mb-2">Fine + Scholarship Lapse</p>
                <p class="text-lg font-bold text-orange-700">₹{{ number_format($fine) }} + ₹{{ number_format($record->scholarship_fee) }}</p>
                <p class="text-lg font-bold text-orange-700">Total Fine = ₹{{ number_format($totalFine) }}</p>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 text-center">
                <p class="text-xs font-medium text-orange-600 uppercase tracking-wide mb-2">Total Payable Amount</p>
                <p class="text-lg font-bold text-orange-700">₹{{ number_format($payableWithFine) }}</p>
            </div>
        @endif


            </div>

            @if ($dueFee <= 0)
        <p class="text-sm text-green-600 font-medium mt-4 text-center">✓ Fully Paid</p>
    @endif
</div>

    @if ($dueFee > 0)
    <button type="button"
        onclick="document.getElementById('pay-modal-{{ $index }}').classList.remove('hidden')"
        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
        Pay Now
    </button>
@endif

<!-- Payment Modal -->
<div id="pay-modal-{{ $index }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 max-w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Pay Fee</h3>
            <button type="button" onclick="document.getElementById('pay-modal-{{ $index }}').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <p class="text-sm text-gray-500 mb-4">
            Amount Due: <span class="font-bold text-red-600">₹{{ number_format($dueFee) }}</span>
        </p>

        @if ($errors->has('amount'))
            <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
                {{ $errors->first('amount') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('payment.initiate', $record) }}">
    @csrf
    <label class="block text-sm font-medium mb-1">Enter Amount</label>
    <input
        type="number"
        name="amount"
        min="1"
        max="{{ $dueFee }}"
        step="1"
        required
        class="w-full border rounded px-3 py-2 mb-1"
        placeholder="Enter amount to pay">

    <p class="text-xs text-gray-400 mb-4">Maximum: ₹{{ number_format($dueFee) }}</p>

    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
        Proceed to Pay
    </button>
</form>
    </div>
</div>

    <!-- Fee History Modal -->
<div id="{{ $modalId }}" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-96 max-w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Fee History</h3>
            <button onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="border-b text-gray-500">
                <tr>
                    <th class="py-2">Amount</th>
                    <th class="py-2">Date</th>
                    <th class="py-2">Mode</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($record->payments as $payment)
                    <tr class="border-b">
                        <td class="py-2">₹{{ number_format($payment->amount) }}</td>
                        <td class="py-2">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="py-2 capitalize">{{ $payment->payment_mode }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-3 text-center text-gray-500">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@empty
    <p class="text-gray-500">No fee record found.</p>
@endforelse

@endsection