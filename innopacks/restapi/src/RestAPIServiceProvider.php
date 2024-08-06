<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI;

use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Middleware\ContentFilterHook;
use InnoShop\Common\Middleware\EventActionHook;
use InnoShop\RestAPI\Middleware\SetAPILocale;

class RestAPIServiceProvider extends ServiceProvider
{
    private array $middlewares = [
        SetAPILocale::class,
        EventActionHook::class,
        ContentFilterHook::class,
    ];

    /**
     * Boot front service provider.
     *
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        if (! installed()) {
            return;
        }

        load_settings();
        $this->registerFrontApiRoutes();
        $this->registerPanelApiRoutes();
    }

    /**
     * Register frontend api routes.
     *
     * @return void
     */
    protected function registerFrontApiRoutes(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewares as $middleware) {
            $router->pushMiddlewareToGroup('api', $middleware);
        }

        Route::prefix('api')
            ->middleware('api')
            ->name('api.')
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/front-api.php'));
            });
    }

    /**
     * Register admin api routes.
     *
     * @return void
     */
    private function registerPanelApiRoutes(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewares as $middleware) {
            $router->pushMiddlewareToGroup('panel_api', $middleware);
        }

        Route::prefix('api/panel')
            ->middleware('panel_api')
            ->name('api.panel.')
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/panel-api.php'));
            });
    }
}
