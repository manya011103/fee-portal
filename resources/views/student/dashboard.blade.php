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


    @php
    // Saare fee records ka overall status
    $totalDue = 0;
    $scholarshipAtRisk = 0;

    foreach ($student->feeRecords as $r) {
        $due = ($r->total_fee - $r->scholarship_fee) - $r->payments()->sum('amount');
        if ($due > 0) {
            $totalDue += $due;
            $scholarshipAtRisk += $r->scholarship_fee;
        }
    }

    $dueDate   = $student->getEffectiveDueDate();
    $daysLate  = $student->getDaysLate();
    $daysLeft  = (int) now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
    $fineNow   = $student->getFineAmount();
    $payableWithFine = $totalDue + $fineNow + $scholarshipAtRisk;

    if ($totalDue <= 0) {
        $noticeType = 'paid';
    } elseif ($daysLate > 0) {
        $noticeType = 'late';
    } else {
        $noticeType = 'upcoming';
    }

    $noticeStyles = [
        'paid'     => 'bg-green-50 border-green-200 text-green-800',
        'upcoming' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'late'     => 'bg-red-50 border-red-200 text-red-800',
    ];
@endphp

<style>
    .notice-marquee { overflow: hidden; white-space: nowrap; }
    .notice-marquee-track {
        display: inline-block;
        padding-left: 100%;
        animation: notice-scroll 28s linear infinite;
    }
    .notice-marquee:hover .notice-marquee-track { animation-play-state: paused; }
    @keyframes notice-scroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
</style>

<div class="border rounded-lg shadow-sm mb-6 px-4 py-3 text-sm font-medium {{ $noticeStyles[$noticeType] }}">
    <div class="notice-marquee">
        <div class="notice-marquee-track">

            @if ($noticeType === 'paid')
                ✅ Congratulations! Your fees have been paid in full and no dues are pending. Thank you for paying on time.

            @elseif ($noticeType === 'upcoming')
                ⚠️ Your fee due date is {{ $dueDate->format('d M Y') }}
                @if ($daysLeft > 0)
                    ({{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left).
                @else
                    (today is the last day).
                @endif
                Please pay your outstanding fee of ₹{{ number_format($totalDue) }} by this date.
                @if ($scholarshipAtRisk > 0)
                    &nbsp;•&nbsp; If payment is not completed on time, your scholarship of ₹{{ number_format($scholarshipAtRisk) }} will lapse.
                @endif
                &nbsp;•&nbsp; A late fine of ₹100 per day will be applied after the due date.

            @else
                🚨 The due date ({{ $dueDate->format('d M Y') }}) has passed {{ $daysLate }} {{ Str::plural('day', $daysLate) }} ago.
                A fine of ₹{{ number_format($fineNow) }} has been imposed
                @if ($scholarshipAtRisk > 0)
                    and your scholarship of ₹{{ number_format($scholarshipAtRisk) }} has lapsed.
                @else
                    .
                @endif
                &nbsp;•&nbsp; A fine of ₹100 will be added for every additional day until the full fee is paid.
                &nbsp;•&nbsp; Total amount payable today: ₹{{ number_format($payableWithFine) }}.
            @endif

        </div>
    </div>
</div>



     <!-- Fee Records -->

     @if ($student->getDaysLate() > 0 && $errors->has('amount'))
    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
        {{ $errors->first('amount') }}
    </div>
@endif

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
<!-- Fee Summary -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

     

    <!-- Fee Details -->
    <div class="px-5 py-5">

        <!-- Basic Fee -->
        <div class="space-y-3">

            <!-- Total Fee -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    Total Fee
                </span>

                <span class="text-sm font-semibold text-gray-800">
                    ₹{{ number_format($record->total_fee) }}
                </span>
            </div>


            <!-- Scholarship -->
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    Scholarship
                </span>

                <span class="text-sm font-semibold text-gray-800">
                    − ₹{{ number_format($record->scholarship_fee) }}
                </span>
            </div>

        </div>


        <!-- Separator -->
        <div class="border-t border-gray-200 my-4"></div>


        <!-- Total Payable -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-blue-700">
                Total Payable
            </span>

            <span class="text-base font-bold text-blue-700">
                ₹{{ number_format($totalPayable) }}
            </span>
        </div>


        <!-- Payment Status -->
        <div class="mt-7 space-y-3">

            <!-- Total Paid -->
            <div class="flex items-center justify-between">

                <div class="flex items-center gap-2">

                    <span class="text-sm text-gray-600">
                        Total Paid
                    </span>

                    <!-- History Button -->
                    <button type="button"
                        onclick="document.getElementById('{{ $modalId }}').classList.remove('hidden')"
                        title="View fee history"
                        class="inline-flex items-center justify-center
                               h-5 w-5 rounded-full
                               bg-green-600 text-white
                               hover:bg-green-700 transition">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-3 w-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 11-18 0" />

                        </svg>

                    </button>

                </div>

                <span class="text-sm font-semibold text-green-700">
                    ₹{{ number_format($paidTotal) }}
                </span>

            </div>


            <!-- Due Fee -->
            <div class="flex items-center justify-between">

                <span class="text-sm text-gray-600">
                    Due Fee
                </span>

                <span class="text-sm font-semibold
                    {{ $dueFee > 0 ? 'text-red-600' : 'text-gray-700' }}">

                    ₹{{ number_format(max($dueFee, 0)) }}

                </span>

            </div>

        </div>


        @if ($isLate && $dueFee > 0)

            <!-- Additional Charges Separator -->
            <div class="border-t border-gray-200 my-4"></div>


            <!-- Additional Charges -->
            <div class="space-y-3">

                <!-- Fine -->
                <div class="flex items-center justify-between">

                    <span class="text-sm text-gray-600">
                        Fine
                    </span>

                    <span class="text-sm font-semibold text-orange-600">
                        ₹{{ number_format($fine) }}
                    </span>

                </div>


                <!-- Scholarship Lapse -->
                <div class="flex items-center justify-between">

                    <span class="text-sm text-gray-600">
                        Scholarship Lapse
                    </span>

                    <span class="text-sm font-semibold text-orange-600">
                        ₹{{ number_format($record->scholarship_fee) }}
                    </span>

                </div>

            </div>


            <!-- Separator -->
            <div class="border-t border-gray-200 my-4"></div>


            <!-- Total Fine -->
            <div class="flex items-center justify-between">

                <span class="text-sm font-semibold text-gray-700">
                    Total Fine
                </span>

                <span class="text-sm font-bold text-orange-600">
                    ₹{{ number_format($totalFine) }}
                </span>

            </div>


            <!-- Final Payable -->
            <div class="mt-6 pt-4 border-t border-gray-200">

                <div class="flex items-center justify-between">

                    <span class="text-sm font-bold text-gray-800 uppercase">
                        Total Payable Amount
                    </span>

                    <span class="text-lg font-bold text-orange-600">
                        ₹{{ number_format($payableWithFine) }}
                    </span>

                </div>

                <p class="text-xs text-gray-400 mt-1">
                    Due Fee + Total Fine
                </p>

            </div>

        @endif

    </div>

</div>
            @if ($dueFee <= 0)
        <p class="text-sm text-green-600 font-medium mt-4 text-center">✓ Fully Paid</p>
    @endif
</div>

   @if ($dueFee > 0)
    @if ($isLate)
        {{-- Due date nikal gayi: seedha payment gateway --}}
        <form method="POST" action="{{ route('payment.initiate', $record) }}">
            @csrf
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
                Pay Now
            </button>
        </form>
    @else
        {{-- Due date abhi baaki hai: amount wala popup --}}
        <button type="button"
            onclick="document.getElementById('pay-modal-{{ $index }}').classList.remove('hidden')"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
            Pay Now
        </button>
    @endif
@endif


<!-- Payment Modal -->
 @if ($dueFee > 0 && ! $isLate)
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
@endif

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