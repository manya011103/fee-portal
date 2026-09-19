<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('admin.students.import');
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => ['required', 'mimes:xlsx,xls'],
    ]);

    $import = new StudentsImport();
    Excel::import($import, $request->file('file'));

    return back()
        ->with('status', "{$import->successCount} students imported successfully.")
        ->with('import_errors', $import->errors)
        ->with('import_skipped', $import->skipped);
}
}