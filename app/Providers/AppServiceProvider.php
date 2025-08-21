<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 👇 Force the reset link to point to your SPA page
        ResetPassword::createUrlUsing(function ($user, string $token) {
            // reads from config('app.frontend_url') or env('FRONTEND_URL')
            $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://nexora.uk.com')), '/');
            $email = urlencode($user->getEmailForPasswordReset());

            return "{$frontend}/auth/reset-password?token={$token}&email={$email}";
        });
    }
}
