<?php

require_once __DIR__.'/../app/Support/helpers.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null;

        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: $appEnv === 'testing' ? ['*'] : []);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'provider_or_agent_verified' => \App\Http\Middleware\EnsureProviderOrAgentEmailIsVerified::class,
            'orchestrator.key' => \App\Http\Middleware\EnsureOrchestratorKey::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
