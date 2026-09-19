<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function initiate(Request $request, FeeRecord $feeRecord)
    {
        $student = $feeRecord->student;

        $paidTotal = $feeRecord->payments()->sum('amount');
        $totalPayable = $feeRecord->total_fee - $feeRecord->scholarship_fee;
        $fine = $student->getFineAmount();
        $dueFee = $totalPayable - $paidTotal + $fine;

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:' . max($dueFee, 0),
            ],
        ]);

        FeePayment::create([
            'fee_record_id' => $feeRecord->id,
            'amount' => $request->amount,
            'payment_date' => now()->format('Y-m-d'),
            'payment_mode' => 'portal',
        ]);

        Log::info("Payment recorded: Student #{$student->id} ({$student->name}) — Amount: ₹{$request->amount} for {$feeRecord->class_name} (Portal)");

        return back()->with('status', "Payment of ₹{$request->amount} recorded successfully.");
    }
}