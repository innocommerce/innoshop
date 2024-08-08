<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common;

use Illuminate\Support\Facades\Config;
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
        load_settings();
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        $this->loadMailSettings();
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
        $this->loadTranslationsFrom($this->basePath.'lang', 'common');
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
     * Load the email configuration, fetch values from the backend mail,
     * and override them in config/mail and config/services.
     * @return void
     */
    private function loadMailSettings(): void
    {
        $mailEngine = strtolower(system_setting('email_engine'));

        if (empty($mailEngine)) {
            return;
        }

        $storeMailAddress = system_setting('email', '');

        Config::set('mail.default', $mailEngine);
        Config::set('mail.from.address', $storeMailAddress);
        Config::set('mail.from.name', config('app.name'));

        if ($mailEngine == 'smtp') {
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => system_setting('smtp_host'),
                'port'       => system_setting('smtp_port'),
                'encryption' => system_setting('smtp_encryption'),
                'username'   => system_setting('smtp_username'),
                'password'   => system_setting('smtp_password'),
                'timeout'    => system_setting('smtp_timeout'),
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
            'form-file'         => Components\Forms\File::class,
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
