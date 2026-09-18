<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\Reservation;
use App\Models\User;
use App\Observers\RecapObserver;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        User::observe(UserObserver::class);
        Reservation::observe(RecapObserver::class);
        Report::observe(RecapObserver::class);

        RateLimiter::for('report-submissions', function (Request $request): array {
            $user = $request->user();
            $key = $user === null
                ? 'ip:'.$request->ip()
                : 'user:'.$user->getAuthIdentifier();

            return [
                Limit::perMinute(5)->by($key),
                Limit::perDay(20)->by($key),
            ];
        });

        RateLimiter::for('reservation-submissions', function (Request $request): array {
            $user = $request->user();
            $key = $user === null
                ? 'ip:'.$request->ip()
                : 'user:'.$user->getAuthIdentifier();

            return [
                Limit::perMinute(10)->by($key),
                Limit::perDay(30)->by($key),
            ];
        });

        RateLimiter::for('public-browse', function (Request $request): Limit {
            return Limit::perMinute(60)->by('ip:'.$request->ip());
        });
    }
}
