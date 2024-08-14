<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $webMiddlewares = [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ];
        $middleware->group('front', $webMiddlewares);
        $middleware->group('panel', $webMiddlewares);

        $apiMiddlewares = [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ];
        $middleware->group('front_api', $apiMiddlewares);
        $middleware->group('panel_api', $apiMiddlewares);

        $middleware->redirectGuestsTo(function (Request $request) {
            if (\Illuminate\Support\Str::startsWith($request->route()->uri(), 'api')) {
                return front_route('home.index');
            }

            if (is_admin()) {
                return panel_route('login.index');
            } else {
                return front_route('login.index');
            }
        });

        $middleware->validateCsrfTokens(except: [
            '*callback*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();

        $exceptions->render(function (Exception $e, Request $request) {
            if ($request->is('api/*')) {
                return json_fail($e->getMessage());
            }
            return null;
        });
    })->create();
