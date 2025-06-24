<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
     public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

 public function handleGoogleCallback()
{
    $googleUser = Socialite::driver('google')->stateless()->user();

    $baseUsername = strtolower(Str::slug($googleUser->getName(), '_')); // e.g. ali_khan
    $username = $baseUsername;
    $counter = 1;

    // Ensure the username is unique
    while (User::where('username', $username)->exists()) {
        $username = $baseUsername . '_' . $counter;
        $counter++;
    }

    $user = User::updateOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'name' => $googleUser->getName(),
            'username' => $username,
            'password' => bcrypt(Str::random(16)),
        ]
    );

    Auth::login($user);

    return redirect(config('app.frontend_url') . '/auth/social/callback?token=' . $user->createToken('web')->plainTextToken);
}
}
