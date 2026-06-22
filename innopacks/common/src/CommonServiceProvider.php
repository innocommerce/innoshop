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
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Components\Base;
use InnoShop\Common\Components\Forms;
use InnoShop\Common\Console\Commands;
use InnoShop\Common\Services\AI\ProviderRegistry;
use InnoShop\Common\Services\Notification\NotificationEventSubscriber;
use InnoShop\Common\Services\StorageService;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Register common view namespace early so Blade components can resolve
     * `common::...` even if boot() is skipped or reordered (e.g. Octane edge cases).
     */
    public function register(): void
    {
        $this->loadViewsFrom($this->basePath.'resources/views', 'common');
        $this->app->singleton(StorageService::class);
        $this->app->singleton(ProviderRegistry::class);
    }

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        load_settings();
        $this->loadAiConfig();
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        $this->registerSchedules();
        $this->loadMailSettings();
        $this->loadViewComponents();
        $this->loadViewTemplates();
        $this->registerTopbarAnnouncements();
        $this->registerNotificationListeners();
    }

    /**
     * Register default topbar announcement hook filter.
     * Plugins can append via `front.topbar.announcements`, or insert rows
     * directly into the inno_topbar_announcements table.
     */
    private function registerTopbarAnnouncements(): void
    {
        listen_hook_filter('front.announcements', function (array $items): array {
            $dbItems = Models\Announcement::getActiveItems();

            return array_merge($items, $dbItems);
        }, 10);
    }

    /**
     * Register system event listeners for the notification system.
     * Queue failures, long waits, etc.
     */
    private function registerNotificationListeners(): void
    {
        NotificationEventSubscriber::register();
    }

    /**
     * Load AI config from system_setting into config('ai.*')
     */
    private function loadAiConfig(): void
    {
        if (! installed()) {
            return;
        }

        app(ProviderRegistry::class)->buildLaravelAiConfig();
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/innoshop.php', 'innoshop');
        if (installed()) {
            Config::set('app.debug', system_setting('debug', false));
        }
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
                Commands\OrderComplete::class,
                Commands\AggregateVisitStatistics::class,
                Commands\BackfillVisitGeo::class,
                Commands\TagBots::class,
            ]);
        }
    }

    /**
     * Register scheduled tasks. Activated on the server by adding this cron entry:
     *   * * * * * cd /path/to/innoshop && php artisan schedule:run >> /dev/null 2>&1
     *
     * @return void
     */
    private function registerSchedules(): void
    {
        // Aggregate yesterday's visit stats (pv/uv/conversion/country/hour/device) shortly after midnight.
        Schedule::command('visits:aggregate --date=yesterday')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->name('visits:aggregate-yesterday')
            ->description('Aggregate yesterday\'s visit data into daily summary tables');

        // Re-scan for crawlers/scanners weekly (new UA patterns emerge over time).
        Schedule::command('visits:tag-bots --include-suspicious')
            ->weeklyOn(1, '03:00')
            ->name('visits:tag-bots-weekly')
            ->description('Tag bots/crawlers/scanners via User-Agent and behavior');

        // Backfill missing geo data nightly in small chunks.
        Schedule::command('visits:backfill-geo --limit=10000')
            ->dailyAt('03:30')
            ->withoutOverlapping()
            ->name('visits:backfill-geo-nightly')
            ->description('Backfill country/browser/os for visits missing geo data');
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
            $smtpUsername = system_setting('smtp_username');
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => system_setting('smtp_host'),
                'port'       => (int) system_setting('smtp_port', 587),
                'encryption' => strtolower(system_setting('smtp_encryption')),
                'username'   => $smtpUsername,
                'password'   => system_setting('smtp_password'),
                'timeout'    => (int) system_setting('smtp_timeout', 60),
            ]);
            Config::set('mail.from.address', $smtpUsername);
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
            'locale-input' => Forms\LocaleInput::class,
            'locale-modal' => Forms\LocaleModal::class,
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
            'model-switch' => Forms\ModelSwitch::class,
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
    }
}
