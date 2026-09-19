<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::latest()->paginate(15);
        return view('admin.students.index', compact('students'));
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