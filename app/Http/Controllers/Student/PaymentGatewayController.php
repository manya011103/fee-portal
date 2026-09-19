<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeRecord;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayController extends Controller
{
    private function hmacDigest(string $msg, string $key): string
    {
        return hash_hmac('sha256', $msg, $key);
    }

    // Step 1: Payment initiate karna, gateway pe redirect karna
    public function initiate(Request $request, FeeRecord $feeRecord)
    {

        if ($feeRecord->student_id !== auth('student')->id()) {
        abort(403, 'Unauthorized.');
    }

        $student = $feeRecord->student;

        // Server pe dobara due fee calculate karo
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

        $merchantTxnNo = 'TXN' . uniqid();

        // Pending transaction record banao — return aane pe isi se match karenge
        PaymentTransaction::create([
            'merchant_txn_no' => $merchantTxnNo,
            'fee_record_id' => $feeRecord->id,
            'amount' => $request->amount,
            'status' => 'initiated',
        ]);

        $requestData = [
            'merchantId' => config('paymentgateway.merchant_id'),
            'aggregatorID' => config('paymentgateway.aggregator_id'),
            'merchantTxnNo' => $merchantTxnNo,
            'amount' => number_format($request->amount, 2, '.', ''),
            'currencyCode' => '356',
            'payType' => '0',
            'customerEmailID' => 'noreply@feeportal.com',
            'transactionType' => 'SALE',
            'returnURL' => route('payment.return'),
            'txnDate' => now()->format('YmdHis'),
            'customerMobileNo' => $student->mobile,
            'customerName' => $student->name,
            'addlParam1' => '000',
            'addlParam2' => '111',
        ];

        ksort($requestData);
        $plainHashtext = implode('', $requestData);
        $secureHash = $this->hmacDigest($plainHashtext, config('paymentgateway.secret_key'));
        $requestData['secureHash'] = $secureHash;

        try {
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
        ->post(config('paymentgateway.initiate_sale_url'), $requestData);

    Log::info('Payment gateway raw response', [
        'status' => $response->status(),
        'body' => $response->body(),
    ]);

    $responseData = $response->json();
} catch (\Exception $e) {
    Log::error('Payment gateway connection error', ['message' => $e->getMessage()]);
    return back()->withErrors(['amount' => 'Could not connect to payment gateway. Please try again.']);
}

if (isset($responseData['responseCode']) && $responseData['responseCode'] === 'R1000') {
    $paymentUrl = $responseData['redirectURI'] . '?tranCtx=' . urlencode($responseData['tranCtx']);
    return redirect()->away($paymentUrl);
}

Log::error('Payment initiation failed', ['response' => $responseData]);

return back()->withErrors(['amount' => 'Could not initiate payment. Please try again.']);
    }

    // Step 2: Bank se wapas aane pe — verify karke save karna
    public function handleReturn(Request $request)
{
    $merchantTxnNo = $request->input('merchantTxnNo') ?? $request->input('MerchantTxnNo');

    if (! $merchantTxnNo) {
        return redirect()->route('login')->withErrors(['payment' => 'Invalid payment response.']);
    }

    $transaction = PaymentTransaction::with('feeRecord.student')->where('merchant_txn_no', $merchantTxnNo)->first();

    if (! $transaction) {
        return redirect()->route('login')->withErrors(['payment' => 'Transaction not found.']);
    }

    // Student ko dobara login karo — chahe session survive ki ho ya na ho
    \Illuminate\Support\Facades\Auth::guard('student')->login($transaction->feeRecord->student);

    // Status Check API se verify karo
    $statusData = [
        'merchantId' => config('paymentgateway.merchant_id'),
        'aggregatorID' => config('paymentgateway.aggregator_id'),
        'merchantTxnNo' => $merchantTxnNo,
        'originalTxnNo' => $merchantTxnNo,
        'transactionType' => 'STATUS',
    ];

    ksort($statusData);
    $plainHashtext = implode('', $statusData);
    $statusData['secureHash'] = $this->hmacDigest($plainHashtext, config('paymentgateway.secret_key'));

    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
        ->post(config('paymentgateway.status_check_url'), $statusData);

    $statusResult = $response->json();

    Log::info('Payment status check', ['txn' => $merchantTxnNo, 'result' => $statusResult]);

    // 👇 Ye line badli hai — bank ka actual field 'txnStatus' hai, value 'SUC' hai
    $isSuccess = isset($statusResult['txnStatus']) && $statusResult['txnStatus'] === 'SUC';

    if ($isSuccess && $transaction->status !== 'success') {
        $transaction->update(['status' => 'success']);

        FeePayment::create([
            'fee_record_id' => $transaction->fee_record_id,
            'amount' => $transaction->amount,
            'payment_date' => now()->format('Y-m-d'),
            'payment_mode' => 'portal',
        ]);

        return redirect()->route('student.dashboard')->with('status', "Payment of ₹{$transaction->amount} successful!");
    }

    if (! $isSuccess) {
        $transaction->update(['status' => 'failed']);
        return redirect()->route('student.dashboard')->withErrors(['payment' => 'Payment failed or was cancelled.']);
    }

    return redirect()->route('student.dashboard')->with('status', 'Payment already recorded.');
}
}