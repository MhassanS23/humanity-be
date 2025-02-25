<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Passport\Passport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use App\Http\Middleware\ValidateOAuthRequest;

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
    public function boot()
    {
        RateLimiter::for('resend-verification', function (Request $request) {
            return Limit::perMinute(2)->by($request->email ?: $request->ip())->response(function () {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.'
                ], Response::HTTP_TOO_MANY_REQUESTS);
            });
        });

        Route::middleware('validate.oauth')->group(function () {
            Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
                ->name('passport.token');
        });
        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(now()->addMinutes(2));
        Passport::refreshTokensExpireIn(now()->addMinutes(3));
        Passport::personalAccessTokensExpireIn(now()->addMonths(2));
    }
}
