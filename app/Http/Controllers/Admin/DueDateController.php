<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DueDateController extends Controller
{
    // Step 1: Naya due date daalna, OTP bhejna
    public function requestOtp(Request $request, Student $student)
    {
        $request->validate([
            'new_due_date' => [
                'required',
                'date',
                'after:' . $student->getEffectiveDueDate()->format('Y-m-d'),
            ],
        ]);

        $admin = Auth::guard('web')->user();

        $otp = rand(100000, 999999);
        Cache::put('due_date_otp_' . $admin->id, [
            'otp' => $otp,
            'student_id' => $student->id,
            'new_due_date' => $request->new_due_date,
        ], now()->addMinutes(5));

        Log::info("Due date change OTP for admin #{$admin->id} ({$admin->mobile}): {$otp}");

        return back()
            ->with('status', 'OTP sent to your mobile.')
            ->with('show_due_date_otp_modal', true)
            ->with('pending_student_id', $student->id);
    }

    // Step 2: OTP verify karke due date confirm karna
   public function confirm(Request $request, Student $student)
{
    $request->validate(['otp' => ['required', 'numeric']]);

    $admin = Auth::guard('web')->user();
    $cached = Cache::get('due_date_otp_' . $admin->id);

    if (! $cached || $cached['otp'] != $request->otp || $cached['student_id'] != $student->id) {
        return back()
            ->withErrors(['otp' => 'Incorrect/Expired OTP'])
            ->with('show_due_date_otp_modal', true)
            ->with('pending_student_id', $student->id);
    }

    // History save karo, update se pehle
    \App\Models\DueDateHistory::create([
        'student_id' => $student->id,
        'old_due_date' => $student->due_date,
        'new_due_date' => $cached['new_due_date'],
        'changed_by' => $admin->id,
    ]);

    $student->update(['due_date' => $cached['new_due_date']]);
    Cache::forget('due_date_otp_' . $admin->id);

    return back()->with('status', 'Due date updated successfully.');
}

    public function resendOtp(Student $student)
{
    $admin = Auth::guard('web')->user();
    $cached = Cache::get('due_date_otp_' . $admin->id);

    if (! $cached || $cached['student_id'] != $student->id) {
        return back()->withErrors(['otp' => 'Session expired, please try again.']);
    }

    $otp = rand(100000, 999999);
    Cache::put('due_date_otp_' . $admin->id, [
        'otp' => $otp,
        'student_id' => $student->id,
        'new_due_date' => $cached['new_due_date'],
    ], now()->addMinutes(5));

    Log::info("Resent due date OTP for admin #{$admin->id} ({$admin->mobile}): {$otp}");

    return back()
        ->with('status', 'OTP resent successfully.')
        ->with('show_due_date_otp_modal', true);
}
}