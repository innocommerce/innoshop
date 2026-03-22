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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InnoShop\Common\Middleware\ContentFilterHook;
use InnoShop\Common\Middleware\EventActionHook;
use InnoShop\RestAPI\Commands\SyncApifoxCommand;
use InnoShop\RestAPI\Middleware\SetAPICurrency;
use InnoShop\RestAPI\Middleware\SetAPILocale;

class RestAPIServiceProvider extends ServiceProvider
{
    protected array $commands = [
        SyncApifoxCommand::class,
    ];

    private array $middlewares = [
        SetAPILocale::class,
        SetAPICurrency::class,
        EventActionHook::class,
        ContentFilterHook::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/scribe_front.php', 'scribe');
        $this->mergeConfigFrom(__DIR__.'/../config/scribe_panel.php', 'scribe_panel');
    }

    /**
     * Boot front service provider.
     *
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/scribe_front.php' => config_path('scribe.php'),
            __DIR__.'/../config/scribe_panel.php' => config_path('scribe_panel.php'),
        ], 'innoshop-scribe-config');

        $this->commands($this->commands);

        $this->registerScribePanelDocumentationRoutes();

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
                $path = __DIR__.'/../routes/front-api.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
    }

    /**
     * Register admin api routes.
     *
     * @return void
     */
    /**
     * Panel Scribe uses config `scribe_panel`, but the package only registers routes for `config('scribe')`.
     * Mirror vendor/knuckleswtf/scribe/routes/laravel.php so /docs/panel and /docs/panel.openapi serve
     * the admin API spec (separate from front /docs and /docs.openapi).
     */
    protected function registerScribePanelDocumentationRoutes(): void
    {
        $docsType = config('scribe_panel.type', 'laravel');
        if (! Str::endsWith($docsType, 'laravel') || ! config('scribe_panel.laravel.add_routes', true)) {
            return;
        }

        $prefix     = config('scribe_panel.laravel.docs_url', '/docs/panel');
        $middleware = config('scribe_panel.laravel.middleware', []);

        Route::middleware($middleware)->group(function () use ($prefix) {
            Route::view($prefix, 'scribe_panel.index')->name('scribe_panel');

            Route::get("{$prefix}.postman", function () {
                return new JsonResponse(Storage::disk('local')->get('scribe_panel/collection.json'), json: true);
            })->name('scribe_panel.postman');

            Route::get("{$prefix}.openapi", function () {
                return response()->file(Storage::disk('local')->path('scribe_panel/openapi.yaml'));
            })->name('scribe_panel.openapi');
        });
    }

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
                $path = __DIR__.'/../routes/panel-api.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
    }
}
