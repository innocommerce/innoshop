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
use InnoShop\Common\Components\Base;
use InnoShop\Common\Components\Forms;
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
        Config::set('app.debug', system_setting('debug', false));
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
                Commands\MigrateProductImages::class,
                Commands\NormalizeLocales::class,
                Commands\MigrateImagePaths::class,
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
                'encryption' => strtolower(system_setting('smtp_encryption')),
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
        // Base components
        $this->loadViewComponentsAs('common', [
            'alert'         => Base\Alert::class,
            'no-data'       => Base\NoData::class,
            'delete-button' => Base\DeleteButton::class,
        ]);

        // Form components
        $this->loadViewComponentsAs('common-form', [
            'input'        => Forms\Input::class,
            'select'       => Forms\Select::class,
            'textarea'     => Forms\Textarea::class,
            'rich-text'    => Forms\RichText::class,
            'image'        => Forms\Image::class,
            'imagep'       => Forms\ImagePure::class,
            'images'       => Forms\Images::class,
            'imagesp'      => Forms\ImagesPure::class,
            'file'         => Forms\File::class,
            'date'         => Forms\Date::class,
            'switch-radio' => Forms\SwitchRadio::class,
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
