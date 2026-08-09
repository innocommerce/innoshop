<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp;

use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    public function register(): void
    {
        $this->loadViewsFrom($this->basePath.'resources/views', 'mcp');
        $this->loadTranslationsFrom($this->basePath.'lang', 'mcp');
    }

    public function boot(): void
    {
        if (! has_install_lock()) {
            return;
        }

        $this->loadRoutesFrom($this->basePath.'routes/mcp.php');
    }
}
