<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpLoginController extends Controller
{
    // Login page dikhana
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Step 1: Mobile number daal ke OTP bhejna
    public function sendOtp(Request $request)
{
    $request->validate([
        'mobile' => ['required', 'string'],
    ]);

    $mobile = $request->mobile;

    $user = User::where('mobile', $mobile)->first();
    $type = 'admin';

    if (! $user) {
        $user = Student::where('mobile', $mobile)->first();
        $type = 'student';
    }

    if (! $user) {
        return back()->withErrors(['mobile' => 'Mobile Number not registered']);
    }

    $otp = rand(100000, 999999);
    Cache::put('otp_'.$type.'_'.$user->id, $otp, now()->addMinutes(5));

    Log::info("OTP for {$mobile} ({$type} #{$user->id}): {$otp}");

    // ID/type ab session mein — URL mein nahi
    session(['otp_type' => $type, 'otp_user_id' => $user->id]);

    return redirect()->route('otp.verify.form')
        ->with('status', 'OTP sent successfully.');
}

public function resendOtp()
{
    $type = session('otp_type');
    $id = session('otp_user_id');

    if (! $type || ! $id) {
        return redirect()->route('login')->withErrors(['mobile' => 'Session expired, please login again.']);
    }

    $user = $type === 'admin' ? User::find($id) : Student::find($id);

    if (! $user) {
        abort(404);
    }

    $otp = rand(100000, 999999);
    Cache::put('otp_'.$type.'_'.$user->id, $otp, now()->addMinutes(5));

    Log::info("Resent OTP for {$user->mobile} ({$type} #{$user->id}): {$otp}");

    return back()->with('status', 'OTP resent successfully.');
}

public function showVerifyForm()
{
    if (! session('otp_type') || ! session('otp_user_id')) {
        return redirect()->route('login');
    }

    return view('auth.otp-verify');
}

public function verifyOtp(Request $request)
{
    $request->validate(['otp' => ['required', 'numeric']]);

    $type = session('otp_type');
    $id = session('otp_user_id');

    if (! $type || ! $id) {
        return redirect()->route('login')->withErrors(['mobile' => 'Session expired, please login again.']);
    }

    $cachedOtp = Cache::get('otp_'.$type.'_'.$id);

    if (! $cachedOtp || $request->otp != $cachedOtp) {
        return back()->withErrors(['otp' => 'Incorrect/ Expired OTP']);
    }

    Cache::forget('otp_'.$type.'_'.$id);
    session()->forget(['otp_type', 'otp_user_id']);

    if ($type === 'admin') {
        $user = User::findOrFail($id);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    } else {
        $student = Student::findOrFail($id);
        Auth::guard('student')->login($student);
        $request->session()->regenerate();
        return redirect()->route('student.dashboard');
    }
}

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}