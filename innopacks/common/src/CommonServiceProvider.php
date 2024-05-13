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

class CommonServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $configPath = __DIR__.'/../config/innoshop.php';

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConfig();
    }

    /**
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->configPath, 'innoshop');
    }
}
