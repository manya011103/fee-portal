<?php

namespace App\Services;

use App\Models\FeeRecord;
use App\Models\Student;

class FeeCalculator
{
    public function forRecord(FeeRecord $record): array
    {
        $student = $record->student;

        $totalFee = (float) $record->total_fee;
        $scholarship = (float) $record->scholarship_fee;
        $paidTotal = (float) $record->payments()->sum('amount');

        // Normal fee after scholarship
        $totalPayable = max($totalFee - $scholarship, 0);

        // Amount still pending before fine/scholarship lapse
        $dueFee = max($totalPayable - $paidTotal, 0);

        $daysLate = $student->getDaysLate();
        $isLate = $daysLate > 0 && $dueFee > 0;

        $fine = $isLate
            ? $daysLate * config('fee.fine_per_day')
            : 0;

        $scholarshipLapse = $isLate
            ? $scholarship
            : 0;

        $totalFine = $fine + $scholarshipLapse;

        $payableToday = $dueFee + $totalFine;

        return [
            'total_fee' => $totalFee,
            'scholarship' => $scholarship,
            'total_payable' => $totalPayable,
            'paid_total' => $paidTotal,
            'due_fee' => $dueFee,
            'days_late' => $daysLate,
            'is_late' => $isLate,
            'fine' => $fine,
            'scholarship_lapse' => $scholarshipLapse,
            'total_fine' => $totalFine,
            'payable_today' => $payableToday,
            'is_fully_paid' => $dueFee <= 0,
        ];
    }

    public function forStudent(Student $student): array
    {
        $records = $student->feeRecords;

        $calculations = $records->mapWithKeys(function (FeeRecord $record) {
            return [
                $record->id => $this->forRecord($record),
            ];
        });

        $totalDue = $calculations->sum('due_fee');

        $scholarshipAtRisk = $calculations->sum('scholarship_lapse');

        $fine = $calculations->sum('fine');

        return [
            'records' => $calculations,
            'total_due' => $totalDue,
            'fine' => $fine,
            'scholarship_at_risk' => $scholarshipAtRisk,
            'payable_today' => $totalDue + $fine + $scholarshipAtRisk,
            'is_fully_paid' => $totalDue <= 0,
        ];
    }
}