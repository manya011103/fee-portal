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
        $query = Student::query();

        if ($request->filled('class')) {
            $query->whereHas('feeRecords', function ($q) use ($request) {
                $q->where('class_name', $request->class);
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        $classes = FeeRecord::select('class_name')
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