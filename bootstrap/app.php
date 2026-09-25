<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AddSecurityHeaders::class);

        $middleware->alias([
            'active' => EnsureAccountActive::class,
            'role' => EnsureRole::class,
        ]);

        $trustedProxies = array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('TRUSTED_PROXIES', '')),
        )));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(
                $trustedProxies,
                Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Selama APP_DEBUG=false, error tak terduga (500) tampil sebagai
        // halaman ramah tanpa membocorkan stack trace. HttpException
        // (404/403/419/dst.) tetap memakai alur penanganan bawaan Laravel.
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            if (config('app.debug') || $e instanceof HttpExceptionInterface || $request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return response()->view('errors.500', ['exception' => $e], 500);
        });
    })->create();
