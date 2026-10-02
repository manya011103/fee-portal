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
    <form method="GET" action="{{ route('admin.students.index') }}" class="mb-4">
    @if (request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif

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