<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InnoShop\AI\Services\ProviderRegistry;
use InnoShop\AI\Services\ToolRegistry;
use InnoShop\AI\Tools\OrderDetailTool;
use InnoShop\AI\Tools\OrderListTool;
use InnoShop\AI\Tools\ProductDetailTool;
use InnoShop\AI\Tools\ProductListTool;
use InnoShop\AI\Tools\SalesStatsTool;
use InnoShop\AI\Tools\StockReportTool;

class AIServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Register AI services.
     */
    public function register(): void
    {
        $this->loadViewsFrom($this->basePath.'resources/views', 'ai');
        $this->loadTranslationsFrom($this->basePath.'lang', 'ai');
        $this->app->singleton(ProviderRegistry::class);
        $this->app->singleton(ToolRegistry::class);
    }

    /**
     * Boot AI service provider.
     */
    public function boot(): void
    {
        if (! has_install_lock()) {
            return;
        }

        $this->loadAiConfig();
        $this->registerCoreTools();
        $this->registerPanelRoutes();
        $this->registerPanelApiRoutes();
        $this->loadViewTemplates();
    }

    /**
     * Register built-in read-only tools into the ToolRegistry.
     * Plugin tools join later via the `ai.tools` hook on first registry read.
     */
    private function registerCoreTools(): void
    {
        $registry = $this->app->make(ToolRegistry::class);

        foreach ([
            ProductListTool::class,
            ProductDetailTool::class,
            OrderListTool::class,
            OrderDetailTool::class,
            StockReportTool::class,
            SalesStatsTool::class,
        ] as $toolClass) {
            $registry->register($this->app->make($toolClass));
        }
    }

    /**
     * Load AI config from system_setting into config('ai.*').
     */
    private function loadAiConfig(): void
    {
        if (! installed()) {
            return;
        }

        app(ProviderRegistry::class)->buildLaravelAiConfig();
    }

    /**
     * Register admin panel AI routes.
     */
    private function registerPanelRoutes(): void
    {
        $adminName = panel_name();

        Route::prefix($adminName)
            ->middleware('panel')
            ->name('panel.')
            ->group(function () {
                $path = $this->basePath.'routes/panel.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
    }

    /**
     * Register panel API AI routes.
     */
    private function registerPanelApiRoutes(): void
    {
        Route::prefix('api/panel')
            ->middleware('panel_api')
            ->name('api.panel.')
            ->group(function () {
                $path = $this->basePath.'routes/panel-api.php';
                if (is_file($path)) {
                    $this->loadRoutesFrom($path);
                }
            });
    }

    /**
     * Load templates for publishing.
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = inno_path('ai/resources/views');
        $customViewPath = resource_path('views/vendor/ai');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');
    }
}
