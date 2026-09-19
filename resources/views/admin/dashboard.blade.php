@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Welcome, {{ auth('web')->user()->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-6 rounded shadow">
            <p class="text-gray-500 text-sm">Total Students</p>
            <p class="text-3xl font-bold">{{ \App\Models\Student::count() }}</p>
        </div>
    </div>
@endsection