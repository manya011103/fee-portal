@extends('layouts.admin')

@section('title', 'Students')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Students</h1>

    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
    <tr>
        <th class="px-4 py-3">Name</th>
        <th class="px-4 py-3">Enrollment No</th>
        <th class="px-4 py-3">Mobile</th>
        <th class="px-4 py-3">Action</th>
    </tr>
</thead>
<tbody>
    @forelse ($students as $student)
        <tr class="border-b">
            <td class="px-4 py-3">{{ $student->name }}</td>
            <td class="px-4 py-3">{{ $student->enrollment_no }}</td>
            <td class="px-4 py-3">{{ $student->mobile }}</td>
            <td class="px-4 py-3">
                <a href="{{ route('admin.students.show', $student) }}" class="text-indigo-600 hover:underline">
                    View
                </a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-3 text-center text-gray-500">No student found</td>
        </tr>
    @endforelse
</tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
@endsection