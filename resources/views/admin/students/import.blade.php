@extends('layouts.admin')

@section('title', 'Import Students')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Import Students</h1>

    <div class="bg-white p-6 rounded shadow max-w-lg">
        <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls" required class="mb-4">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
                Import
            </button>
        </form>

        @if (session('import_errors') && count(session('import_errors')) > 0)
            <div class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded">
                <p class="font-bold mb-2">Errors:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('import_skipped') && count(session('import_skipped')) > 0)
    <div class="mt-4 p-3 bg-yellow-50 text-yellow-700 text-sm rounded">
        <p class="font-bold mb-2">Already Imported:</p>
        <ul class="list-disc pl-5 space-y-1">
            @foreach (session('import_skipped') as $msg)
                <li>{{ $msg }}</li>
            @endforeach
        </ul>
    </div>
@endif

    </div>
@endsection