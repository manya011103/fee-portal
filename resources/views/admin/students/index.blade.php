@extends('layouts.admin')

@section('title', 'Students')

@section('content')
   @php
    $pageTitle = match (request('status')) {
        'fully_paid' => 'Fully Paid Students',
        'pending' => 'Pending Students',
        'overdue' => 'Overdue Students',
        default => '',
    };
@endphp

<h1 class="text-2xl font-bold mb-6">{{ $pageTitle }}</h1>

    <!-- Class Filter -->
    <!-- Filters -->
    <form method="GET" action="{{ route('admin.students.index') }}"
        class="mb-4 flex flex-wrap items-center gap-3">

        @if (request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by name, enrollment no, or mobile..."
            class="border rounded px-3 py-2 text-sm w-full md:w-80"
        >

        <select name="class" onchange="this.form.submit()"
            class="border rounded px-3 py-2 text-sm">
            <option value="">All Classes</option>

            @foreach ($classes as $class)
                <option value="{{ $class }}"
                    {{ request('class') == $class ? 'selected' : '' }}>
                    {{ $class }}
                </option>
            @endforeach
        </select>

        <button type="submit"
            class="bg-indigo-600 text-white rounded px-4 py-2 text-sm hover:bg-indigo-700">
            Search
        </button>

        @if (request('search') || request('class'))
            <a href="{{ route('admin.students.index', ['status' => request('status')]) }}"
                class="text-red-600 hover:underline text-sm">
                Clear
            </a>
        @endif
    </form>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Enrollment No</th>
                    <th class="px-4 py-3">Class</th>
                    <th class="px-4 py-3">Mobile</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($students as $student)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $student->name }}</td>
                        <td class="px-4 py-3">{{ $student->enrollment_no }}</td>

                        <td class="px-4 py-3">
                            {{ $student->feeRecords->pluck('class_name')->filter()->unique()->implode(', ') ?: '-' }}
                        </td>

                        <td class="px-4 py-3">{{ $student->mobile }}</td>

                        <td class="px-4 py-3">
                            <a href="{{ route('admin.students.show', $student) }}"
                                class="text-indigo-600 hover:underline">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                            No student found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        @if (method_exists($students, 'links'))
            {{ $students->links() }}
        @endif
    </div>
@endsection