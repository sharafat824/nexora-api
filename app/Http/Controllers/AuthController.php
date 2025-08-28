<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\OtpVerificationMail;
use App\Models\EmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            // 'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'referral_code' => 'nullable|exists:users,referral_code'
        ]);

        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }
        $fullName = $request->first_name . ' ' . $request->last_name;

        // ✅ Generate base username from name
        $baseUsername = Str::slug($request->first_name . $request->last_name, '_');

        // ✅ Ensure it's unique
        $username = $baseUsername;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $i;
            $i++;
        }

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'referred_by' => $referrer ? $referrer->id : null
        ]);

        // Create OTP record
        $otp = rand(100000, 999999);
        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(10)]
        );

        // Send OTP via email
        Mail::to($user->email)->send(new OtpVerificationMail($otp));

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return success([
            'access_token' => $token,
            'user' => $user
        ], 'Registered successfully. OTP has been sent to your email.', 201);
    }

    public function sendOtp(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return error('Email already verified.', 400);
        }

        $otp = rand(100000, 999999);
        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            ['otp' => $otp, 'expires_at' => Carbon::now()->addMinutes(20)]
        );

        // Send OTP via email
        Mail::to($user->email)->send(new \App\Mail\OtpVerificationMail($otp));

        return success([], 'OTP sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $user = $request->user();

        $record = EmailVerification::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->first();

        if (!$record || Carbon::now()->gt($record->expires_at)) {
            return error('Invalid or expired OTP.', 422);
        }

        $user->email_verified_at = now();
        $user->save();
        $record->delete();

        return success(['user' => $user], 'Email verified successfully.');
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return error('Invalid credentials', 401);
        }

        if ($user->is_blocked) {
            return error('Your account has been blocked. Please contact support.', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        LoginLog::create([
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'logged_in_at' => now(),
        ]);

        return success(['access_token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return success([], 'Logout successfully');
    }
}
