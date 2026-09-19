<?php

use App\Http\Controllers\Auth\OtpLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\DueDateController;
use App\Http\Controllers\Student\PaymentGatewayController;

// Login (Guest) Routes
Route::get('/login', [OtpLoginController::class, 'showLoginForm'])->name('login');

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login/otp', [OtpLoginController::class, 'sendOtp'])->name('otp.send');
    Route::post('/login/otp/resend', [OtpLoginController::class, 'resendOtp'])->name('otp.resend');
    Route::post('/login/otp/verify', [OtpLoginController::class, 'verifyOtp'])->name('otp.verify.submit');
});

Route::get('/login/otp/verify', [OtpLoginController::class, 'showVerifyForm'])->name('otp.verify.form');
Route::post('/logout', [OtpLoginController::class, 'logout'])->name('logout');

// Admin Routes (protected)
Route::middleware('auth:web')->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('students', StudentController::class)->names('admin.students');

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/students/{student}/due-date/request-otp', [DueDateController::class, 'requestOtp'])
            ->name('admin.students.due-date.request-otp');
        Route::post('/students/{student}/due-date/resend', [DueDateController::class, 'resendOtp'])
            ->name('admin.students.due-date.resend');
        Route::post('/students/{student}/due-date/confirm', [DueDateController::class, 'confirm'])
            ->name('admin.students.due-date.confirm');
    });
});

// Student Routes (protected)
Route::middleware('auth:student')->prefix('student')->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');

    Route::post('/payment/initiate/{feeRecord}', [PaymentGatewayController::class, 'initiate'])
        ->name('payment.initiate');
});

// Payment Return — PUBLIC route, koi auth middleware nahi
// Bank isko seedha call karta hai, session available na bhi ho toh bhi kaam karna chahiye
Route::any('/payment/return', [PaymentGatewayController::class, 'handleReturn'])
    ->name('payment.return');

// Home — root URL ko login pe bhej do
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/students/import', [ImportController::class, 'showForm'])->name('admin.students.import.form');
Route::post('/students/import', [ImportController::class, 'import'])->name('admin.students.import');