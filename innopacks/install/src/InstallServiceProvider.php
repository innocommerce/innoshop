<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class InstallServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->loadViewsFrom(inno_path('install/resources/views'), 'install');
    }

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        // Security fix: skip installer route registration after installation
        // to prevent unauthenticated reinstall/takeover attacks.
        // Use has_install_lock() instead of installed() to avoid DB dependency,
        // ensuring routes stay blocked even during database outages.
        if (has_install_lock()) {
            return;
        }

        $this->registerWebRoutes();
        $this->loadTranslations();
    }

    /**
     * Register admin front routes.
     *
     * @return void
     */
    protected function registerWebRoutes(): void
    {
        Route::name('install.')
            ->group(function () {
                $path = __DIR__.'/../routes/web.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
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

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'install');
    }
}
