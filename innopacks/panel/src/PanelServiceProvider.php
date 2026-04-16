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
use InnoShop\Common\Middleware\ContentFilterHook;
use InnoShop\Common\Middleware\EventActionHook;
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Services\StorageService;
use InnoShop\Panel\Console\Commands\ChangeRootPassword;
use InnoShop\Panel\Middleware\AdminAuthenticate;
use InnoShop\Panel\Middleware\GlobalPanelData;
use InnoShop\Panel\Middleware\SetPanelLocale;
use InnoShop\RestAPI\Services\FileManagerInterface;
use InnoShop\RestAPI\Services\FileManagerService;
use InnoShop\RestAPI\Services\OSSService;

class PanelServiceProvider extends ServiceProvider
{
    /**
     * Boot panel service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! has_install_lock()) {
            return;
        }

        load_settings();
        $this->registerWebRoutes();
        $this->registerGuard();
        $this->registerUploadFileSystem();
        $this->registerFileManagerService();
        $this->registerCommands();
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

        $this->loadViewsFrom(inno_path('panel/resources/views'), 'panel');
    }

    /**
     * Register admin user guard.
     */
    private function registerGuard(): void
    {
        Config::set('auth.providers.admin', [
            'driver' => 'eloquent',
            'model'  => Admin::class,
        ]);

        Config::set('auth.guards.admin', [
            'driver'   => 'session',
            'provider' => 'admin',
        ]);
    }

    /**
     * Register the media filesystem disk.
     * When OSS driver is configured, the media disk points to S3; otherwise local.
     *
     * @return void
     */
    protected function registerUploadFileSystem(): void
    {
        $driver    = system_setting('file_manager_driver', 'local');
        $s3Drivers = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];

        if (in_array($driver, $s3Drivers)) {
            $prefix   = "storage_{$driver}_";
            $s3Config = [
                'driver'                  => 's3',
                'key'                     => system_setting($prefix.'key', system_setting('storage_key', '')),
                'secret'                  => system_setting($prefix.'secret', system_setting('storage_secret', '')),
                'region'                  => system_setting($prefix.'region', system_setting('storage_region', '')),
                'bucket'                  => system_setting($prefix.'bucket', system_setting('storage_bucket', '')),
                'endpoint'                => system_setting($prefix.'endpoint', system_setting('storage_endpoint', '')),
                'url'                     => system_setting($prefix.'cdn_domain', system_setting('storage_cdn_domain', '')) ?: null,
                'use_path_style_endpoint' => false,
                'visibility'              => 'public',
                'options'                 => ['ACL' => 'public-read'],
                'throw'                   => true,
            ];
            Config::set('filesystems.disks.media', $s3Config);
        } else {
            Config::set('filesystems.disks.media', [
                'driver'      => 'local',
                'root'        => public_path(rtrim(StorageService::STORAGE_PREFIX, '/')),
                'url'         => env('APP_URL').'/media',
                'visibility'  => 'public',
                'throw'       => true,
                'permissions' => [
                    'file' => [
                        'public'  => 0755,
                        'private' => 0755,
                    ],
                    'dir' => [
                        'public'  => 0755,
                        'private' => 0755,
                    ],
                ],
            ]);
        }
    }

    /**
     * Bind FileManagerInterface to the appropriate implementation.
     */
    protected function registerFileManagerService(): void
    {
        $driver    = system_setting('file_manager_driver', 'local');
        $s3Drivers = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];

        if (in_array($driver, $s3Drivers)) {
            $this->app->singleton(FileManagerInterface::class, function () {
                return new OSSService;
            });
        } else {
            $this->app->singleton(FileManagerInterface::class, function () {
                return new FileManagerService;
            });
        }
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ChangeRootPassword::class,
            ]);
        }
    }

    /**
     * Register admin panel routes.
     *
     * @return void
     */
    private function registerWebRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [EventActionHook::class, ContentFilterHook::class, GlobalPanelData::class, SetPanelLocale::class];
        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('panel', $middleware);
        }

        $adminName = panel_name();
        Route::prefix($adminName)
            ->middleware('panel')
            ->name("$adminName.")
            ->group(function () {
                $path = __DIR__.'/../routes/web.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
    }

    /**
     * Register panel language
     * @return void
     */
    private function loadTranslations(): void
    {
        if (! is_dir(__DIR__.'/../lang')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/panel'),
        ], 'lang');
    }

    /**
     * Load view components.
     *
     * @return void
     */
    private function loadViewComponents(): void
    {
        $this->loadViewComponentsAs('panel', [
            'data-criteria'          => Components\Data\Criteria::class,
            'data-sorter'            => Components\Data\Sorter::class,
            'data-info'              => Components\Data\DataInfo::class,
            'data-data-search'       => Components\Data\DataSearch::class,
            'layout-sidebar'         => Components\Layout\Sidebar::class,
            'chart-line'             => Components\Charts\Line::class,
            'chart-pie'              => Components\Charts\Pie::class,
            'form-codemirror'        => Components\Forms\Codemirror::class,
            'form-autocomplete-list' => Components\Forms\AutocompleteList::class,
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
        $customViewPath = resource_path('views/vendor/panel');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');
    }
}
