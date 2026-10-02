<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeRecord;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
{
    $query = Student::with('feeRecords');

    if ($request->filled('class')) {
        $query->whereHas('feeRecords', function ($q) use ($request) {
            $q->where('class_name', $request->class);
        });
    }

    $status = $request->input('status');

    if ($status === 'pending') {
        $query->whereHas('feeRecords', function ($q) {
            $q->where('is_fully_paid', false);
        });
    }


     if ($status === 'fully_paid') {
         $query->whereDoesntHave('feeRecords', function ($q) {
             $q->where('is_fully_paid', false);
         });
     }

    if ($status === 'overdue') {
        $query->whereHas('feeRecords', function ($q) {
            $q->where('is_fully_paid', false);
        });

        $students = $query->latest()->get()->filter(function ($student) {
            return $student->getEffectiveDueDate()->isPast();
        });

        $classes = \App\Models\FeeRecord::select('class_name')
            ->distinct()->orderBy('class_name')->pluck('class_name');

        return view('admin.students.index', [
            'students' => $students,
            'classes' => $classes,
            'isOverdueView' => true,
        ]);
    }

    $students = $query->latest()->paginate(15)->withQueryString();

    $classes = \App\Models\FeeRecord::select('class_name')
        ->distinct()
        ->orderBy('class_name')
        ->pluck('class_name');

    return view('admin.students.index', compact('students', 'classes'));
}

    public function create() {}
    public function store(Request $request) {}
    public function edit(Student $student) {}
    public function update(Request $request, Student $student) {}
    public function destroy(Student $student) {}

    public function show(Student $student)
    {
        $student->load(['feeRecords', 'dueDateHistories.admin']);
        return view('admin.students.show', compact('student'));
    }
}