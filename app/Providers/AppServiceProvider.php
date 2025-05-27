<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;

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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        $this->configRateLimiting();
    }
    protected function configRateLimiting()
    {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()->id?:$request->ip() );
        });
        RateLimiter::for('chat', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });
        RateLimiter::for('follow', function ($request) {
            return Limit::perMinute(20)->by($request->user()->id?:$request->ip() );
        });
    }

}
