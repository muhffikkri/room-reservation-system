<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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
        Paginator::defaultView('pagination.clay');

        // Kuota harian laporan milik ReportService::createReport(): satu hari
        // kalender, satu pesan galat. Limiter di sini hanya menahan lonjakan
        // singkat, tidak mengulang kuota harian dengan jendela sendiri.
        // Jendela 24 jam berputar berbeda dari hari kalender tepat setelah
        // tengah malam: middleware menolak permintaan yang service izinkan,
        // dengan pesan yang berbeda.
        RateLimiter::for('report-submissions', function (Request $request): array {
            $user = $request->user();
            $key = $user === null
                ? 'ip:'.$request->ip()
                : 'user:'.$user->getAuthIdentifier();

            return [
                Limit::perMinute(5)->by($key),
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

        // Render PDF adalah operasi paling berat di aplikasi ini, jadi batasnya
        // per menit dan per admin, bukan hanya per IP.
        RateLimiter::for('recap-exports', function (Request $request): array {
            $user = $request->user();
            $key = $user === null
                ? 'ip:'.$request->ip()
                : 'user:'.$user->getAuthIdentifier();

            return [
                Limit::perMinute(6)->by($key),
                Limit::perHour(30)->by($key),
            ];
        });
    }
}
