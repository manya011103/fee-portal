<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeePayment;
use App\Models\FeeRecord;
use Illuminate\Http\Request;

class LateFeeReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $class = $request->input('class');

        $query = FeePayment::with('feeRecord.student')
            ->where('fine_portion', '>', 0)
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($class) {
            $query->whereHas('feeRecord', function ($q) use ($class) {
                $q->where('class_name', $class);
            });
        }

        $totalFine = (clone $query)->sum('fine_portion');

        $payments = $query
            ->orderByDesc('payment_date')
            ->paginate(25)
            ->withQueryString();

        $classes = FeeRecord::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');

        return view('admin.late-fee-report', compact(
            'payments', 'classes', 'startDate', 'endDate', 'class', 'totalFine'
        ));
    }
}