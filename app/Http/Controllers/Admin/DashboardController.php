<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeReminderQueue;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $paymentsInRange = FeePayment::whereBetween('payment_date', [$startDate, $endDate]);

        $totalCollected = (clone $paymentsInRange)->sum('amount');
        $fineCollected = (clone $paymentsInRange)->sum('fine_portion');
        $scholarshipLapseCollected = (clone $paymentsInRange)->sum('scholarship_lapse_portion');
        $baseFeeCollected = $totalCollected - $fineCollected - $scholarshipLapseCollected;

        $totalStudents = Student::count();
        $fullyPaidCount = Student::whereDoesntHave('feeRecords', function ($q) {
            $q->where('is_fully_paid', false);
        })->count();
        $pendingCount = $totalStudents - $fullyPaidCount;

        $overdueCount = Student::whereHas('feeRecords', function ($q) {
    $q->where('is_fully_paid', false);
})->get()->filter(function (Student $student): bool {
    return $student->getEffectiveDueDate()->isPast();
})->count();

        $recentPayments = FeePayment::with('feeRecord.student')
            ->latest('payment_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $queuedReminders = FeeReminderQueue::count();

        return view('admin.dashboard', compact(
            'startDate',
            'endDate',
            'totalCollected',
            'fineCollected',
            'scholarshipLapseCollected',
            'baseFeeCollected',
            'totalStudents',
            'fullyPaidCount',
            'pendingCount',
            'overdueCount',
            'recentPayments',
            'queuedReminders'
        ));
    }
}