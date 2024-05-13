<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,

    InnoShop\Install\InstallServiceProvider::class,
    InnoShop\Common\CommonServiceProvider::class,
    InnoShop\Panel\PanelServiceProvider::class,
    InnoShop\Front\FrontServiceProvider::class,
    InnoShop\Plugin\PluginServiceProvider::class,
];
