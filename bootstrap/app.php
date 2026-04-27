<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\UpgradePrompt;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AddSecurityHeaders::class);
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'super_admin' => App\Http\Middleware\EnsureSuperAdmin::class,
            'subscription' => CheckSubscription::class,
            'upgrade_prompt' => UpgradePrompt::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
