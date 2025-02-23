<?php

return [
    // Application Service Providers...
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,

    // Laravel Framework Service Providers...
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,

    // Package Service Providers...
    InnoShop\Install\InstallServiceProvider::class,
    InnoShop\Common\CommonServiceProvider::class,
    InnoShop\Panel\PanelServiceProvider::class,
    InnoShop\Front\FrontServiceProvider::class,
    InnoShop\RestAPI\RestAPIServiceProvider::class,
    InnoShop\RestAPI\Providers\OSSServiceProvider::class,  // OSS
    InnoShop\Enterprise\EnterpriseServiceProvider::class,
    InnoShop\Plugin\PluginServiceProvider::class,
];
