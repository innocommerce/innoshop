<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Models\Admin;
use InnoShop\Panel\Middleware\AdminAuthenticate;

class PanelServiceProvider extends ServiceProvider
{
    /**
     * Boot panel service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        load_settings();
        $this->registerGuard();
        $this->registerWebRoutes();
        $this->registerApiRoutes();
        $this->loadTranslations();
        $this->loadViewTemplates();
        $this->loadViewComponents();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        app('router')->aliasMiddleware('admin_auth', AdminAuthenticate::class);
    }

    /**
     * Register admin user guard.
     */
    private function registerGuard(): void
    {
        Config::set('auth.providers.admins', [
            'driver' => 'eloquent',
            'model'  => Admin::class,
        ]);

        Config::set('auth.guards.admin', [
            'driver'   => 'session',
            'provider' => 'admins',
        ]);
    }

    /**
     * Register admin panel routes.
     *
     * @return void
     */
    private function registerWebRoutes(): void
    {
        $adminName = panel_name();
        Route::prefix($adminName)
            ->middleware('web')
            ->name("$adminName.")
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
            });
    }

    /**
     * Register admin api routes.
     * @todo This middleware should be instead of token-based authentication, not admin_auth
     *
     * @return void
     */
    private function registerApiRoutes(): void
    {
        $adminName = panel_name();
        Route::prefix("api/$adminName")
            ->middleware(['api', 'web', 'admin_auth:admin'])
            ->name("api.$adminName.")
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/api.php'));
            });
    }

    /**
     * Register panel language
     * @return void
     */
    private function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel');
    }

    /**
     * Load view components.
     *
     * @return void
     */
    private function loadViewComponents(): void
    {
        $this->loadViewComponentsAs('panel', [
            'form-input'             => Components\Forms\Input::class,
            'sidebar'                => Components\Sidebar::class,
            'alert'                  => Components\Alert::class,
            'form-image'             => Components\Forms\Image::class,
            'form-select'            => Components\Forms\Select::class,
            'form-rich-text'         => Components\Forms\RichText::class,
            'form-lang-tab'          => Components\Forms\LangTab::class,
            'form-textarea'          => Components\Forms\Textarea::class,
            'form-codemirror'        => Components\Forms\Codemirror::class,
            'form-autocomplete-list' => Components\Forms\AutocompleteList::class,
            'form-switch-radio'      => Components\Forms\SwitchRadio::class,
            'no-data'                => Components\NoData::class,
        ]);
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = inno_path('panel/resources/views');
        $customViewPath = resource_path('views/vendor/innoshop-panel');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');

        $this->loadViewsFrom($customViewPath, 'panel');
        $this->loadViewsFrom($originViewPath, 'panel');
    }
}
