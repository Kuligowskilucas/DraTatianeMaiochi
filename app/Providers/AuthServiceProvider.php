<?php

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider
{
    public function boot(): void
    {
        Gate::define('admin', fn($user) => $user->role === 'admin');
        
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $temporarySignedURL = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            // Redireciona o usuário para sua SPA com a URL assinada em query param
            // Ex.: FRONTEND_VERIFY_URL=https://meu-frontend.com/verify-email
            $frontend = config('app.frontend_verify_url'); // defina no .env e leia aqui via config/app.php
            return $frontend ? "{$frontend}?verify_url=".urlencode($temporarySignedURL) : $temporarySignedURL;
        });
    }
}
