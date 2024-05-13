<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common;

use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Console\Commands;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        $this->loadViewComponents();
        $this->loadViewTemplates();
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/innoshop.php', 'innoshop');
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
     * Register common languages.
     *
     * @return void
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'common');
    }

    /**
     * Register common commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\UpdateCountries::class,
                Commands\UpdateStates::class,
                Commands\PublishFrontTheme::class,
            ]);
        }
    }

    /**
     * Load view components.
     *
     * @return void
     */
    protected function loadViewComponents(): void
    {
        $this->loadViewComponentsAs('common', [
            'alert'             => Components\Alert::class,
            'form-input'        => Components\Forms\Input::class,
            'form-date'         => Components\Forms\Date::class,
            'form-image'        => Components\Forms\Image::class,
            'form-images'       => Components\Forms\Images::class,
            'form-rich-text'    => Components\Forms\RichText::class,
            'form-select'       => Components\Forms\Select::class,
            'form-switch-radio' => Components\Forms\SwitchRadio::class,
            'form-textarea'     => Components\Forms\Textarea::class,
            'no-data'           => Components\NoData::class,
        ]);
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = inno_path('common/resources/views');
        $customViewPath = resource_path('views/vendor/common');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');

        $this->loadViewsFrom($originViewPath, 'common');
    }
}
