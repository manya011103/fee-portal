<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeRecord;
use Illuminate\Http\Request;

class NoDuesReportController extends Controller
{
    public function index(Request $request)
    {
        $class = $request->input('class');

        $query = FeeRecord::with(['student', 'payments'])
            ->where('is_fully_paid', true);

        if ($class) {
            $query->where('class_name', $class);
        }

        $records = $query->paginate(25)->withQueryString();

        $classes = FeeRecord::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');

        return view('admin.no-dues-report', compact('records', 'classes', 'class'));
    }
}