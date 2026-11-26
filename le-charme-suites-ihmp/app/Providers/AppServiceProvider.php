<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('login', fn (Request $request): array => [
            Limit::perMinute(5)->by('account:'.Str::lower($request->string('email')->toString())),
            Limit::perMinute(20)->by('ip:'.$request->ip()),
        ]);
        RateLimiter::for('registration', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request): array => [
            Limit::perHour(5)->by('account:'.Str::lower($request->string('email')->toString())),
            Limit::perHour(20)->by('ip:'.$request->ip()),
        ]);
        RateLimiter::for('booking', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('token-refresh', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));
        RateLimiter::for('integration', function (Request $request): Limit {
            $credential = (string) ($request->bearerToken() ?: $request->header('X-IHMP-Key'));

            return Limit::perMinute(60)->by(hash('sha256', $credential).'|'.$request->ip());
        });
    }
}
