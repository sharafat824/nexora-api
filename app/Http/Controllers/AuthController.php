<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => strtolower(str_replace(' ', '_', $request->name)),
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'referred_by' => $referrer ? $referrer->id : null
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return success(['access_token' => $token, 'user' => $user], 'Registration successful', 201);
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return success(['access_token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

      return success([],'Logout successfully');
    }
}
