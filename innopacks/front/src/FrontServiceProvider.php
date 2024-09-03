<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use InnoShop\Common\Middleware\ContentFilterHook;
use InnoShop\Common\Middleware\EventActionHook;
use InnoShop\Common\Models\Customer;
use InnoShop\Front\Middleware\CustomerAuthentication;
use InnoShop\Front\Middleware\GlobalFrontData;
use InnoShop\Front\Middleware\SetFrontLocale;
use InnoShop\Panel\Repositories\ThemeRepo;

class FrontServiceProvider extends ServiceProvider
{
    /**
     * Boot front service provider.
     *
     * @return void
     * @throws \Exception
     */
    public function boot(): void
    {
        $this->loadTranslations();

        if (! installed()) {
            return;
        }

        load_settings();
        $this->registerGuard();
        $this->registerWebRoutes();
        $this->publishViewTemplates();
        $this->loadThemeViewPath();
        $this->loadViewComponents();
        $this->loadThemeTranslations();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        app('router')->aliasMiddleware('customer_auth', CustomerAuthentication::class);
    }

    /**
     * Register guard for frontend.
     */
    protected function registerGuard(): void
    {
        Config::set('auth.providers.customer', [
            'driver' => 'eloquent',
            'model'  => Customer::class,
        ]);

        Config::set('auth.guards.customer', [
            'driver'   => 'session',
            'provider' => 'customer',
        ]);
    }

    /**
     * Register admin front routes.
     *
     * @return void
     * @throws \Exception
     */
    protected function registerWebRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [SetFrontLocale::class, EventActionHook::class, ContentFilterHook::class, GlobalFrontData::class];
        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('front', $middleware);
        }

        Route::middleware('front')
            ->get('/', [Controllers\HomeController::class, 'index'])
            ->name('front.home.index');

        $locales = locales();
        if (count($locales) == 1) {
            Route::middleware('front')
                ->name('front.')
                ->group(function () {
                    $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
                });
        } else {
            foreach ($locales as $locale) {
                Route::middleware('front')
                    ->prefix($locale->code)
                    ->name($locale->code.'.front.')
                    ->group(function () {
                        $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
                    });
            }
        }
    }

    /**
     * Register front language
     * @return void
     */
    protected function loadTranslations(): void
    {
        if (! is_dir(__DIR__.'/../lang')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'front');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/front'),
        ], 'lang');
    }

    /**
     * Publish view as default theme.
     * php artisan vendor:publish --provider='InnoShop\Front\FrontServiceProvider' --tag=views
     *
     * @return void
     */
    protected function publishViewTemplates(): void
    {
        $originViewPath = __DIR__.'/../resources';
        $customViewPath = base_path('themes/default');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');
    }

    /**
     * Load theme view path.
     *
     * @return void
     */
    protected function loadThemeViewPath(): void
    {
        $this->app->singleton('view.finder', function ($app) {
            $themePaths = [];
            if ($theme = system_setting('theme')) {
                $themeViewPath = base_path("themes/{$theme}/views");
                if (is_dir($themeViewPath)) {
                    $themePaths[] = $themeViewPath;
                }
            }
            $themePaths[] = realpath(__DIR__.'/../resources/views');

            $viewPaths = $app['config']['view.paths'];
            $viewPaths = array_merge($themePaths, $viewPaths);

            return new FileViewFinder($app['files'], $viewPaths);
        });
    }

    /**
     * Load view components.
     *
     * @return void
     */
    protected function loadViewComponents(): void
    {
        $this->loadViewComponentsAs('front', [
            'breadcrumb' => Components\Breadcrumb::class,
        ]);
    }

    /**
     * Load theme languages.
     *
     * @return void
     */
    protected function loadThemeTranslations(): void
    {
        $themes = ThemeRepo::getInstance()->getListFromPath();
        foreach ($themes as $theme) {
            $themeCode     = $theme['code'];
            $themeLangPath = base_path("themes/{$themeCode}/lang");
            if (! is_dir($themeLangPath)) {
                continue;
            }
            $this->loadTranslationsFrom($themeLangPath, "theme-$themeCode");
        }
    }
}
