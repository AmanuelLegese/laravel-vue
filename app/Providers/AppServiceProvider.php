<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;

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
        /**
         * Register the policies for the application.
         */
        $this->registerPolicies();

        // // Force JSON response for all API routes
        Response::macro('forceJson', function ($data = [], $status = 200) {
            return response()->json($data, $status);
        });
        /**
         * check if it is super admin and allow all permissions
         */
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        /**
         * Passport token expiration settings
         * 15 days for access tokens
         * 30 days for refresh tokens
         * 6 months for personal access tokens
         */
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
