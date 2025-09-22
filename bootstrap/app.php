<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// 👇 add these uses
use App\Http\Middleware\EnsureAdmin;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
// If you DO use cookie-based API auth, you may also want:
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ---- Web pipeline (default good) ----
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        // ---- API pipeline ----
        // If your API uses Bearer tokens (Sanctum personal access tokens), DO NOT put CSRF here.
        // Keep it lean; add EnsureFrontendRequestsAreStateful ONLY if you also support cookie auth on /api.
        $middleware->api(prepend: [
            // Uncomment this ONLY if you intentionally support cookie/session auth to /api routes:
            // EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ---- Route middleware aliases (Laravel 11 replacement for Kernel aliases) ----
        $middleware->alias([
            'admin'     => EnsureAdmin::class,      // your custom admin gate
            'abilities' => CheckAbilities::class,   // Sanctum: requires ALL listed abilities
            'ability'   => CheckForAnyAbility::class, // Sanctum: requires ANY listed ability
            // 'auth' and 'auth:sanctum' are provided by the framework/providers already
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
