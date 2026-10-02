@extends('layouts.admin')

@section('title', 'Import Students')

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v8" />
                    </svg>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Import Students
                    </h1>
                    <p class="text-sm text-gray-500">
                        Upload an Excel file to add students in bulk.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Upload Card --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Upload Student File
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Select an Excel file containing student records.
                        </p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.students.import') }}"
                        enctype="multipart/form-data"
                        class="p-6">
                        @csrf

                        <label for="student-file"
                            class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-12 text-center transition hover:border-indigo-400 hover:bg-indigo-50/40">

                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 transition group-hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v8" />
                                </svg>
                            </div>

                            <p class="text-base font-semibold text-gray-800">
                                Choose an Excel file
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                Click here to browse your computer
                            </p>

                            <p class="mt-3 rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-500 shadow-sm">
                                XLSX or XLS · Maximum file size allowed
                            </p>

                            <input id="student-file"
                                type="file"
                                name="file"
                                accept=".xlsx,.xls"
                                required
                                class="sr-only">

                            <p id="selected-file"
                                class="mt-4 hidden rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700">
                            </p>
                        </label>

                        @error('file')
                            <p class="mt-3 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-gray-500">
                                Existing students will be skipped automatically.
                            </p>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V3m0 0L8 7m4-4l4 4" />
                                </svg>
                                Import Students
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Import Errors --}}
                @if (session('import_errors') && count(session('import_errors')) > 0)
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">
                        <div class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600"
                                fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.73 3h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z" />
                            </svg>

                            <div>
                                <h3 class="font-semibold">Some records could not be imported</h3>

                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                                    @foreach (session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Skipped Students --}}
                @if (session('import_skipped') && count(session('import_skipped')) > 0)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800">
                        <div class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600"
                                fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                            </svg>

                            <div>
                                <h3 class="font-semibold">Already imported students</h3>

                                <ul class="mt-3 list-disc space-y-1 pl-5 text-sm">
                                    @foreach (session('import_skipped') as $msg)
                                        <li>{{ $msg }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Instructions Card --}}
            <div class="h-fit rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-gray-900">
                    Before you import
                </h2>

                <ul class="mt-5 space-y-4 text-sm text-gray-600">
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                            1
                        </span>
                        <span>Make sure your file is in <strong> XLSX </strong> or <strong> XLS </strong> format.</span>
                    </li>

                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                            2
                        </span>
                        <span>Keep the column names and format consistent with your import template.</span>
                    </li>

                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                            3
                        </span>
                        <span>Duplicate students will not be imported again.</span>
                    </li>

                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                            4
                        </span>
                        <span>Review the error and skipped records after import.</span>
                    </li>
                </ul>

                <div class="mt-6 rounded-xl bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Supported formats
                    </p>

                    <div class="mt-3 flex gap-2">
                        <span class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-green-700 shadow-sm">
                            .XLSX
                        </span>
                        <span class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-green-700 shadow-sm">
                            .XLS
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('student-file');
        const selectedFile = document.getElementById('selected-file');

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                selectedFile.textContent = `Selected file: ${this.files[0].name}`;
                selectedFile.classList.remove('hidden');
            } else {
                selectedFile.textContent = '';
                selectedFile.classList.add('hidden');
            }
        });
    </script>
@endsection