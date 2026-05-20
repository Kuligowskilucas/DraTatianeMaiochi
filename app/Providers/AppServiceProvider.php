<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $temporarySignedURL = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $frontend = config('app.frontend_verify_url');
            return $frontend
                ? "{$frontend}?verify_url=".urlencode($temporarySignedURL)
                : $temporarySignedURL;
        });
    }
}