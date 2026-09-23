@extends('layouts.admin')

@section('title', $student->name)

@section('content')
    <a href="{{ route('admin.students.index') }}" class="text-indigo-600 text-sm mb-4 inline-block">
        &larr; Back to Students
    </a>

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
            <div>
                <p class="text-gray-500">Father's Mobile</p>
                <p class="font-medium">{{ $student->father_mobile ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Mother's Mobile</p>
                <p class="font-medium">{{ $student->mother_mobile ?? '-' }}</p>
            </div>
        </div>
    </div>

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

            <div class="flex items-center gap-2">
                <button type="button"
                    onclick="document.getElementById('due-date-history-modal').classList.remove('hidden')"
                    title="View update history"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 hover:bg-blue-100 text-gray-500 hover:text-blue-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </button>

                <button type="button"
                    onclick="document.getElementById('set-due-date-modal').classList.remove('hidden')"
                    title="Edit due date"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 hover:bg-indigo-100 text-gray-500 hover:text-indigo-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>
        </div>

        @if (session('status'))
            <p class="text-sm text-green-600 font-medium mt-3">{{ session('status') }}</p>
        @endif
    </div>

    <!-- Due Date History Modal -->
    <div id="due-date-history-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-[28rem] max-w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Due Date Update History</h3>
                <button type="button" onclick="document.getElementById('due-date-history-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            @if ($student->dueDateHistories->isEmpty())
                <p class="text-sm text-gray-500 text-center py-4">No changes made yet.</p>
            @else
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @foreach ($student->dueDateHistories as $history)
                        <div class="border-b pb-2 text-sm">
                            <p>
                                <span class="text-gray-500">{{ $history->old_due_date?->format('d M Y') ?? 'Default' }}</span>
                                &rarr;
                                <span class="font-medium text-gray-800">{{ $history->new_due_date->format('d M Y') }}</span>
                            </p>
                            <p class="text-xs text-gray-400">
                                by {{ $history->admin->name }} on {{ $history->created_at->format('d M Y, h:i A') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Modal 1: Set New Due Date -->
    <div id="set-due-date-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-96 max-w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Set Due Date</h3>
                <button type="button" onclick="document.getElementById('set-due-date-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.students.due-date.request-otp', $student) }}">
                @csrf
                <label class="block text-sm font-medium mb-1">New Due Date</label>
                <input
                    type="date"
                    name="new_due_date"
                    required
                    min="{{ $student->getEffectiveDueDate()->copy()->addDay()->format('Y-m-d') }}"
                    class="w-full border rounded px-3 py-2 mb-1">

                @error('new_due_date')
                    <p class="text-red-600 text-xs mb-3">{{ $message }}</p>
                @enderror

                <p class="text-xs text-gray-400 mb-4">Must be after current due date.</p>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
                    Send OTP to Confirm
                </button>
            </form>
        </div>
    </div>

    <!-- Modal 2: OTP Confirmation -->
    <div id="due-date-otp-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-96 max-w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Confirm OTP</h3>
                <button type="button" onclick="document.getElementById('due-date-otp-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                OTP has been sent to your mobile. Check <code class="bg-gray-100 px-1 rounded">storage/logs/laravel.log</code>
            </p>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 text-sm rounded border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            @error('otp')
                <p class="text-red-600 text-sm mb-3">{{ $message }}</p>
            @enderror

            <form method="POST" action="{{ route('admin.students.due-date.confirm', $student) }}">
                @csrf
                <input
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    maxlength="6"
                    required
                    autofocus
                    class="w-full border rounded px-3 py-2 mb-4 text-center text-lg tracking-widest"
                    placeholder="••••••">

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded font-medium">
                    Confirm
                </button>
            </form>

            <!-- Resend Section -->
            <div class="mt-4 text-center text-sm">
                <p id="due-date-timer-text" class="text-gray-500">
                    Resend OTP in <span id="due-date-countdown">30</span>s
                </p>

                <form id="due-date-resend-form" method="POST"
                    action="{{ route('admin.students.due-date.resend', $student) }}" class="hidden">
                    @csrf
                    <button type="submit" class="text-indigo-600 hover:underline font-medium">
                        Resend OTP
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('show_due_date_otp_modal'))
        <script>
            document.getElementById('due-date-otp-modal').classList.remove('hidden');

            let dueDateSeconds = 30;
            const dueDateCountdownEl = document.getElementById('due-date-countdown');
            const dueDateTimerText = document.getElementById('due-date-timer-text');
            const dueDateResendForm = document.getElementById('due-date-resend-form');

            const dueDateInterval = setInterval(() => {
                dueDateSeconds--;
                dueDateCountdownEl.textContent = dueDateSeconds;

                if (dueDateSeconds <= 0) {
                    clearInterval(dueDateInterval);
                    dueDateTimerText.classList.add('hidden');
                    dueDateResendForm.classList.remove('hidden');
                }
            }, 1000);
        </script>
    @endif

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

            </div>

            @if ($dueFee <= 0)
                <p class="text-sm text-green-600 font-medium mt-4 text-center">✓ Fully Paid</p>
            @endif
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