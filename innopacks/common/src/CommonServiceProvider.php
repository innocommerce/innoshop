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
use InnoShop\Common\Jobs\QueueHeartbeatJob;
use InnoShop\Common\Repositories\SystemTaskRepo;
use InnoShop\Common\Services\MediaLookupRegistry;
use InnoShop\Common\Services\Notification\NotificationEventSubscriber;
use InnoShop\Common\Services\StorageService;
use InnoShop\Common\Services\UrlRedirectService;

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

        // Scoped, not singleton — array-style cache of MediaFile rows that
        // media_is_video / media_poster_url read on every cover render.
        // Scoped binding is reset per request under Octane and re-built per
        // FPM request, so a poster replacement becomes visible to the very
        // next page render instead of staying hidden for the array store's
        // TTL (#291).
        $this->app->scoped(MediaLookupRegistry::class);
    }

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
        $this->registerSchedules();
        $this->loadMailSettings();
        $this->loadViewComponents();
        $this->loadViewTemplates();
        $this->registerTopbarAnnouncements();
        $this->registerNotificationListeners();
        $this->registerUrlRedirects();
    }

    /**
     * SEO redirect mechanism (#157): record slug renames into url_redirects
     * and 301 front-side 404s that hit a rule. Management UI lives in a
     * future plugin; `redirects:add` is the manual stopgap.
     */
    private function registerUrlRedirects(): void
    {
        UrlRedirectService::register();
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
                Commands\UpdateGeoLite2::class,
                Commands\RecomputeProductRating::class,
                Commands\MediaRegisterExisting::class,
                Commands\MediaMigrateReferences::class,
                Commands\GenerateVideoPosters::class,
                Commands\PruneVideoPosters::class,
                Commands\VerifyVariants::class,
                Commands\MigrateBotsToDaily::class,
                Commands\PruneVisits::class,
                Commands\AddUrlRedirect::class,
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
        // Scheduler liveness heartbeat, persisted in DB so the panel system
        // task page can detect a dead scheduler even with CACHE_STORE=array.
        Schedule::call(fn () => SystemTaskRepo::touch('scheduler'))
            ->everyFiveMinutes()
            ->name('scheduler-heartbeat')
            ->description('Write scheduler liveness heartbeat');

        // Queue liveness probe: heartbeat is written by the worker when it
        // executes, so a fresh record proves the whole pipeline is alive.
        Schedule::job(new QueueHeartbeatJob)
            ->everyFiveMinutes()
            ->name('queue-heartbeat')
            ->description('Queue liveness probe writing heartbeat on execution');

        // Aggregate yesterday's visit stats (pv/uv/conversion/country/hour/device) shortly after midnight.
        Schedule::command('visits:aggregate --date=yesterday')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->name('visits:aggregate-yesterday')
            ->onSuccess(fn () => SystemTaskRepo::touch('visits-aggregate-yesterday'))
            ->description('Aggregate yesterday\'s visit data into daily summary tables');

        // Keep today's rollup fresh so the panel dashboard never needs to aggregate on read.
        // visit_events is large; aggregating on every pageview wasted ~400ms per request.
        Schedule::command('visits:aggregate --date=today')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->name('visits:aggregate-today')
            ->onSuccess(fn () => SystemTaskRepo::touch('visits-aggregate-today'))
            ->description('Refresh today\'s visit data into daily summary tables');

        // Re-scan for crawlers/scanners weekly (new UA patterns emerge over time).
        // withoutOverlapping(120) = 2h cache lock: weekly cadence has a 7-day buffer,
        // but if a previous run ever stalls we still serialize cleanly.
        // onFailure logs to the daily laravel log channel so exit-code-255 crashes
        // leave a trail beyond the SystemTaskHeartbeat (which only tracks success).
        Schedule::command('visits:tag-bots --include-suspicious')
            ->weeklyOn(1, '03:00')
            ->withoutOverlapping(120)
            ->name('visits:tag-bots-weekly')
            ->onSuccess(fn () => SystemTaskRepo::touch('visits-tag-bots-weekly'))
            ->onFailure(fn (\Throwable $e) => logger()->error(
                'visits:tag-bots failed: '.$e->getMessage(),
                ['trace' => $e->getTraceAsString()]
            ))
            ->description('Tag bots/crawlers/scanners via User-Agent and behavior');

        // Backfill missing geo data nightly in small chunks.
        Schedule::command('visits:backfill-geo --limit=10000')
            ->dailyAt('03:30')
            ->withoutOverlapping()
            ->name('visits:backfill-geo-nightly')
            ->onSuccess(fn () => SystemTaskRepo::touch('visits-backfill-geo-nightly'))
            ->description('Backfill country/browser/os for visits missing geo data');

        // Rolling retention: real-visitor detail 180d, bot IP dedup 30d.
        // Runs well after the 00:05 aggregate so expiring days are already
        // rolled up; the aggregate guard also refuses detail-less days.
        Schedule::command('visits:prune')
            ->dailyAt('00:40')
            ->withoutOverlapping()
            ->name('visits:prune-nightly')
            ->onSuccess(fn () => SystemTaskRepo::touch('visits-prune-nightly'))
            ->description('Delete visit detail rows past the retention window');

        // Refresh the GeoLite2 mmdb weekly so newly added/renamed regions
        // keep resolving; scheduled after the nightly geo backfill cluster.
        Schedule::command('geolite2:download')
            ->weeklyOn(0, '04:00')
            ->withoutOverlapping()
            ->name('geolite2-download-weekly')
            ->onSuccess(fn () => SystemTaskRepo::touch('geolite2-download-weekly'))
            ->description('Refresh the GeoLite2 country database');
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
