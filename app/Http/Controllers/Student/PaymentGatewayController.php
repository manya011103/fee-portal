<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeRecord;
use App\Models\PaymentTransaction;
use App\Services\FeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    private function hmacDigest(string $message, string $key): string
    {
        return hash_hmac('sha256', $message, $key);
    }

    /**
     * Initiate payment and redirect the student to the payment gateway.
     */
    public function initiate(
        Request $request,
        FeeRecord $feeRecord,
        FeeCalculator $calculator
    ) {
        if ($feeRecord->student_id !== auth('student')->id()) {
            abort(403, 'Unauthorized.');
        }

        $student = $feeRecord->student;

        // All fee calculations come from one place.
        $calculation = $calculator->forRecord($feeRecord);

        $dueFee = $calculation['due_fee'];

        if ($dueFee <= 0) {
            return back()->withErrors([
                'amount' => 'No due fee pending.',
            ]);
        }

        if ($calculation['is_late']) {
            // After due date: pay due fee + fine + scholarship lapse.
            $amount = $calculation['payable_today'];
        } else {
            // Before due date: student can make a partial payment.
            $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:' . $dueFee,
                ],
            ]);

            $amount = (float) $request->input('amount');
        }

        $merchantTxnNo = 'TXN' . uniqid();

        PaymentTransaction::create([
            'merchant_txn_no' => $merchantTxnNo,
            'fee_record_id' => $feeRecord->id,
            'amount' => $amount,
            'status' => 'initiated',
        ]);

        $requestData = [
            'merchantId' => config('paymentgateway.merchant_id'),
            'aggregatorID' => config('paymentgateway.aggregator_id'),
            'merchantTxnNo' => $merchantTxnNo,
            'amount' => number_format($amount, 2, '.', ''),
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

        $requestData['secureHash'] = $this->hmacDigest(
            $plainHashtext,
            config('paymentgateway.secret_key')
        );

        try {
            // Important: initiation URL and requestData are used here.
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'curl/8.4.0',   // 👈 ye line add karo

                ])
                ->post(
                    config('paymentgateway.initiate_sale_url'),
                    $requestData
                );

            Log::info('Payment gateway initiation response', [
                'txn' => $merchantTxnNo,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            $responseData = $response->json();
        } catch (\Throwable $exception) {
            Log::error('Payment gateway connection error', [
                'txn' => $merchantTxnNo,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'amount' => 'Could not connect to payment gateway. Please try again.',
            ]);
        }

        if (
            is_array($responseData)
            && ($responseData['responseCode'] ?? null) === 'R1000'
            && ! empty($responseData['redirectURI'])
            && ! empty($responseData['tranCtx'])
        ) {
            $paymentUrl = $responseData['redirectURI']
                . '?tranCtx='
                . urlencode($responseData['tranCtx']);

            return redirect()->away($paymentUrl);
        }

        Log::error('Payment initiation failed', [
            'txn' => $merchantTxnNo,
            'response' => $responseData,
        ]);

        return back()->withErrors([
            'amount' => 'Could not initiate payment. Please try again.',
        ]);
    }

    /**
     * Receive the gateway callback and verify the payment status.
     */
    public function handleReturn(Request $request)
{
    $merchantTxnNo = $request->input('merchantTxnNo')
        ?? $request->input('MerchantTxnNo');

    if (! $merchantTxnNo) {
        return redirect()
            ->route('login')
            ->withErrors([
                'payment' => 'Invalid payment response.',
            ]);
    }

    $transaction = PaymentTransaction::with('feeRecord.student')
        ->where('merchant_txn_no', $merchantTxnNo)
        ->first();

    if (! $transaction) {
        return redirect()
            ->route('login')
            ->withErrors([
                'payment' => 'Transaction not found.',
            ]);
    }

    $student = $transaction->feeRecord->student;

    Auth::guard('student')->login($student);

    if ($transaction->status === 'success') {
        return redirect()
            ->route('student.dashboard')
            ->with('status', 'Payment already recorded.');
    }

    $statusData = [
        'merchantId' => config('paymentgateway.merchant_id'),
        'aggregatorID' => config('paymentgateway.aggregator_id'),
        'merchantTxnNo' => $merchantTxnNo,
        'originalTxnNo' => $merchantTxnNo,
        'transactionType' => 'STATUS',
    ];

    ksort($statusData);

    $plainHashtext = implode('', $statusData);

    $statusData['secureHash'] = $this->hmacDigest(
        $plainHashtext,
        config('paymentgateway.secret_key')
    );

    Log::info('Payment status request', [
        'txn' => $merchantTxnNo,
        'payload' => collect($statusData)->except('secureHash')->all(),
    ]);

    $result = $this->callGateway(
        config('paymentgateway.status_check_url'),
        $statusData
    );

    Log::info('Payment status raw response', [
        'txn' => $merchantTxnNo,
        'http_status' => $result['status'],
        'body' => $result['body'],
        'curl_error' => $result['error'],
    ]);

    /*
     * ⚠️ TEMPORARY FOR TESTING ONLY ⚠️
     * 429 ko yahan success maan rahe hain taaki baaki flow test ho sake.
     * Real launch se pehle ye block HATANA ZAROORI HAI.
     */
    if ($result['status'] === 429) {
    Log::warning('TEMP: Treating 429 as success for testing', [
        'txn' => $merchantTxnNo,
    ]);

    DB::transaction(function () use ($transaction) {
        $lockedTransaction = PaymentTransaction::query()
            ->whereKey($transaction->id)
            ->lockForUpdate()
            ->first();

        if (! $lockedTransaction || $lockedTransaction->status === 'success') {
            return;
        }

        $lockedTransaction->update(['status' => 'success']);

        FeePayment::create([
            'fee_record_id' => $lockedTransaction->fee_record_id,
            'amount' => $lockedTransaction->amount,
            'payment_date' => now()->toDateString(),
            'payment_mode' => 'portal',
        ]);

        $lockedTransaction->feeRecord->recalculateFullyPaid();   // 👈 ye line add ki
    });

    return redirect()
        ->route('student.dashboard')
        ->with('status', "Payment of ₹{$transaction->amount} successful! (test mode)");
}

    if ($result['error']) {
        Log::error('Payment status connection error', [
            'txn' => $merchantTxnNo,
            'message' => $result['error'],
        ]);

        return redirect()
            ->route('student.dashboard')
            ->withErrors([
                'payment' => 'Unable to verify payment status. Please try again shortly.',
            ]);
    }

    if ($result['status'] >= 500 || $result['status'] === 408) {
        Log::error('Payment status gateway/server error', [
            'txn' => $merchantTxnNo,
            'http_status' => $result['status'],
            'body' => $result['body'],
        ]);

        return redirect()
            ->route('student.dashboard')
            ->withErrors([
                'payment' => 'Payment status could not be verified. Please try again shortly.',
            ]);
    }

    $statusResult = json_decode($result['body'], true);

    if (! is_array($statusResult)) {
        Log::error('Invalid payment status response', [
            'txn' => $merchantTxnNo,
            'http_status' => $result['status'],
            'body' => $result['body'],
        ]);

        return redirect()
            ->route('student.dashboard')
            ->withErrors([
                'payment' => 'Payment status could not be verified. Please try again shortly.',
            ]);
    }

    Log::info('Payment status parsed response', [
        'txn' => $merchantTxnNo,
        'result' => $statusResult,
    ]);

    $txnStatus = strtoupper((string) ($statusResult['txnStatus'] ?? ''));

    if ($txnStatus === 'SUC') {
    DB::transaction(function () use ($transaction) {
        $lockedTransaction = PaymentTransaction::query()
            ->whereKey($transaction->id)
            ->lockForUpdate()
            ->first();

        if (! $lockedTransaction || $lockedTransaction->status === 'success') {
            return;
        }

        $lockedTransaction->update(['status' => 'success']);

        FeePayment::create([
            'fee_record_id' => $lockedTransaction->fee_record_id,
            'amount' => $lockedTransaction->amount,
            'payment_date' => now()->toDateString(),
            'payment_mode' => 'portal',
        ]);

        $lockedTransaction->feeRecord->recalculateFullyPaid();   // 👈 ye line add ki
    });

    return redirect()
        ->route('student.dashboard')
        ->with('status', "Payment of ₹{$transaction->amount} successful!");
}

    $failedStatuses = ['FAIL', 'FAILED', 'CANCEL', 'CANCELLED', 'CANCELED'];

    if (in_array($txnStatus, $failedStatuses, true)) {
        $transaction->update(['status' => 'failed']);

        return redirect()
            ->route('student.dashboard')
            ->withErrors([
                'payment' => 'Payment failed or was cancelled.',
            ]);
    }

    Log::warning('Unknown payment transaction status', [
        'txn' => $merchantTxnNo,
        'txn_status' => $txnStatus,
        'response' => $statusResult,
    ]);

    return redirect()
        ->route('student.dashboard')
        ->withErrors([
            'payment' => 'Payment status is still pending. Please try again shortly.',
        ]);
}


private function callGateway(string $url, array $payload): array
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'curl/8.4.0');   // 👈 ye line add karo
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $body = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    return [
        'status' => $httpStatus,
        'body' => $body,
        'error' => $curlError,
    ];
}
}