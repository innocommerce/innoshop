<?php

namespace InnoShop\RestAPI\Providers;

use Illuminate\Support\ServiceProvider;
use InnoShop\RestAPI\Services\OSSService;

class OSSServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 注册配置文件
        $this->mergeConfigFrom(
            __DIR__.'/../../config/filesystems.php',
            'filesystems'
        );

        // 注册文件系统服务
        if (! $this->app->bound('filesystem')) {
            $this->app->singleton('filesystem', function ($app) {
                return $app->make(\Illuminate\Filesystem\FilesystemManager::class);
            });
        }
    }

    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/../../config/filesystems.php' => config_path('filesystems.php'),
        ], 'config');

        // 注册 OSS 服务
        if (config('filesystems.file_manager.driver') === 'oss') {
            $this->app->singleton('file_manager.service', function ($app) {
                return new OSSService;
            });
        }
    }
}
