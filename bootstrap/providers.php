<?php

return [
    // Application Service Providers...
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,

    // Laravel Framework Service Providers...
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,

    // Package Service Providers...
    InnoShop\Install\InstallServiceProvider::class,
    InnoShop\Common\CommonServiceProvider::class,
    InnoShop\Aicore\AicoreServiceProvider::class,
    InnoShop\Mcp\McpServiceProvider::class,
    InnoShop\Panel\PanelServiceProvider::class,
    InnoShop\Front\FrontServiceProvider::class,
    InnoShop\Restapi\RestapiServiceProvider::class,
    InnoShop\Plugin\PluginServiceProvider::class,
    InnoShop\Devtools\DevtoolsServiceProvider::class,
];
