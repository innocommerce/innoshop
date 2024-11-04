<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Middleware\ContentFilterHook;
use InnoShop\Common\Middleware\EventActionHook;
use InnoShop\Front\Middleware\GlobalFrontData;
use InnoShop\Front\Middleware\SetFrontLocale;
use InnoShop\Panel\Middleware\GlobalPanelData;
use InnoShop\Panel\Middleware\SetPanelLocale;
use Throwable;

class EnterpriseServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Boot front service provider.
     *
     * @return void
     * @throws Throwable
     */
    public function boot(): void
    {
        load_settings();
        $this->registerConfig();
        $this->registerMigrations();

        $this->registerMigrations();
        $this->registerCommands();
        $this->registerFrontRoutes();
        $this->registerFrontApiRoutes();
        $this->registerPanelRoutes();
        $this->loadTranslations();
        $this->loadViewTemplates();

        EnterpriseHook::getInstance()->init();
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/innoshop.php', 'enterprise');
        Config::set('innoshop', config('enterprise'));
    }

    /**
     * Register migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->basePath.'database/migrations');
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }
    }

    /**
     * Register admin front routes.
     *
     * @return void
     * @throws Exception
     */
    protected function registerFrontRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [SetFrontLocale::class, EventActionHook::class, ContentFilterHook::class, GlobalFrontData::class];
        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('front', $middleware);
        }

        $routePath = realpath($this->basePath.'routes/front.php');
        if (! file_exists($routePath)) {
            return;
        }

        $locales = locales();
        if (count($locales) == 1) {
            Route::middleware('front')
                ->name('front.')
                ->group(function () use ($routePath) {
                    $this->loadRoutesFrom($routePath);
                });
        } else {
            foreach ($locales as $locale) {
                Route::middleware('front')
                    ->prefix($locale->code)
                    ->name($locale->code.'.front.')
                    ->group(function () use ($routePath) {
                        $this->loadRoutesFrom($routePath);
                    });
            }
        }
    }

    /**
     * Register frontend api routes.
     *
     * @return void
     */
    protected function registerFrontApiRoutes(): void
    {
        $frontApiRoutePath = realpath($this->basePath.'routes/api.php');
        if (file_exists($frontApiRoutePath)) {
            Route::prefix('api/enterprise')
                ->middleware('api')
                ->name('api.enterprise.')
                ->group(function () use ($frontApiRoutePath) {
                    $this->loadRoutesFrom($frontApiRoutePath);
                });
        }
    }

    /**
     * Register admin panel routes.
     *
     * @return void
     */
    private function registerPanelRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [EventActionHook::class, ContentFilterHook::class, GlobalPanelData::class, SetPanelLocale::class];
        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('panel', $middleware);
        }

        $routePath = realpath($this->basePath.'routes/panel.php');
        if (! file_exists($routePath)) {
            return;
        }

        $adminName = panel_name();
        Route::prefix($adminName)
            ->middleware(['panel', 'admin_auth:admin'])
            ->name("$adminName.")
            ->group(function () use ($routePath) {
                $this->loadRoutesFrom($routePath);
            });
    }

    /**
     * Register panel language
     * @return void
     */
    private function loadTranslations(): void
    {
        $this->loadTranslationsFrom($this->basePath.'lang', 'enterprise');
        $this->publishes([
            $this->basePath.'lang' => $this->app->langPath('vendor/panel'),
        ], 'lang');
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = realpath($this->basePath.'resources/views');

        $this->loadViewsFrom($originViewPath, 'enterprise');
    }
}
