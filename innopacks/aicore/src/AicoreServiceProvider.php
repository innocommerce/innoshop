<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use InnoShop\Aicore\Images\ImageDriverManager;
use InnoShop\Aicore\Services\ProviderRegistry;
use InnoShop\Aicore\Services\ToolRegistry;
use InnoShop\Aicore\Tools\ArticleDetailTool;
use InnoShop\Aicore\Tools\ArticleListTool;
use InnoShop\Aicore\Tools\ArticleUpdateTool;
use InnoShop\Aicore\Tools\AttributeCreateTool;
use InnoShop\Aicore\Tools\AttributeDetailTool;
use InnoShop\Aicore\Tools\AttributeGroupListTool;
use InnoShop\Aicore\Tools\AttributeListTool;
use InnoShop\Aicore\Tools\AttributeUpdateTool;
use InnoShop\Aicore\Tools\BrandCreateTool;
use InnoShop\Aicore\Tools\BrandDetailTool;
use InnoShop\Aicore\Tools\BrandListTool;
use InnoShop\Aicore\Tools\BrandUpdateTool;
use InnoShop\Aicore\Tools\CatalogListTool;
use InnoShop\Aicore\Tools\CategoryAutocompleteTool;
use InnoShop\Aicore\Tools\CategoryCreateTool;
use InnoShop\Aicore\Tools\CategoryDetailTool;
use InnoShop\Aicore\Tools\CategoryListTool;
use InnoShop\Aicore\Tools\CategoryUpdateTool;
use InnoShop\Aicore\Tools\CountryListTool;
use InnoShop\Aicore\Tools\CurrencyListTool;
use InnoShop\Aicore\Tools\CustomerAutocompleteTool;
use InnoShop\Aicore\Tools\CustomerDetailTool;
use InnoShop\Aicore\Tools\CustomerListTool;
use InnoShop\Aicore\Tools\CustomerUpdateTool;
use InnoShop\Aicore\Tools\DashboardTool;
use InnoShop\Aicore\Tools\FileListTool;
use InnoShop\Aicore\Tools\FileUploadTool;
use InnoShop\Aicore\Tools\LocaleListTool;
use InnoShop\Aicore\Tools\OptionListTool;
use InnoShop\Aicore\Tools\OrderDetailTool;
use InnoShop\Aicore\Tools\OrderListTool;
use InnoShop\Aicore\Tools\OrderNoteTool;
use InnoShop\Aicore\Tools\OrderReturnDetailTool;
use InnoShop\Aicore\Tools\OrderReturnListTool;
use InnoShop\Aicore\Tools\OrderUpdateStatusTool;
use InnoShop\Aicore\Tools\PageListTool;
use InnoShop\Aicore\Tools\ProductAutocompleteTool;
use InnoShop\Aicore\Tools\ProductCreateTool;
use InnoShop\Aicore\Tools\ProductDetailTool;
use InnoShop\Aicore\Tools\ProductListTool;
use InnoShop\Aicore\Tools\ProductUpdateTool;
use InnoShop\Aicore\Tools\RegionListTool;
use InnoShop\Aicore\Tools\ReviewDetailTool;
use InnoShop\Aicore\Tools\ReviewListTool;
use InnoShop\Aicore\Tools\SalesStatsTool;
use InnoShop\Aicore\Tools\ShipmentCreateTool;
use InnoShop\Aicore\Tools\ShipmentDetailTool;
use InnoShop\Aicore\Tools\ShipmentListTool;
use InnoShop\Aicore\Tools\ShipmentTracesTool;
use InnoShop\Aicore\Tools\SkuAutocompleteTool;
use InnoShop\Aicore\Tools\StockReportTool;
use InnoShop\Aicore\Tools\TagListTool;
use InnoShop\Aicore\Tools\TaxClassListTool;
use InnoShop\Aicore\Tools\TaxRateListTool;

class AicoreServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom($this->basePath.'resources/views', 'aicore');
        $this->loadTranslationsFrom($this->basePath.'lang', 'aicore');
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
        $this->registerDefaultImageDriver();
        $this->registerPanelRoutes();
        $this->registerPanelApiRoutes();
        $this->loadViewTemplates();
    }

    /**
     * Register the default image driver manager as a low-priority fallback on
     * the `ai.image_generate_driver` hook. The manager dispatches to the
     * correct vendor-specific driver (OpenAI-compatible, MiniMax, ...) based
     * on the active provider. Plugins providing a custom driver should
     * register with a higher priority to bypass this entirely.
     */
    private function registerDefaultImageDriver(): void
    {
        app('eventy')->addFilter('ai.image_generate_driver', function ($driver) {
            return $driver ?: ImageDriverManager::class;
        }, -100, 1);
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
            ProductCreateTool::class,
            ProductUpdateTool::class,
            OrderListTool::class,
            OrderDetailTool::class,
            StockReportTool::class,
            SalesStatsTool::class,
            CustomerListTool::class,
            CustomerDetailTool::class,
            CategoryListTool::class,
            CategoryDetailTool::class,
            DashboardTool::class,
            ShipmentListTool::class,
            ShipmentDetailTool::class,
            ShipmentTracesTool::class,
            OrderUpdateStatusTool::class,
            OrderNoteTool::class,
            BrandListTool::class,
            BrandDetailTool::class,
            BrandCreateTool::class,
            BrandUpdateTool::class,
            ReviewListTool::class,
            ReviewDetailTool::class,
            OrderReturnListTool::class,
            OrderReturnDetailTool::class,
            AttributeListTool::class,
            AttributeGroupListTool::class,
            AttributeDetailTool::class,
            AttributeCreateTool::class,
            AttributeUpdateTool::class,
            OptionListTool::class,
            LocaleListTool::class,
            CurrencyListTool::class,
            CountryListTool::class,
            RegionListTool::class,
            TaxRateListTool::class,
            TaxClassListTool::class,
            FileListTool::class,
            ProductAutocompleteTool::class,
            SkuAutocompleteTool::class,
            CategoryCreateTool::class,
            CategoryUpdateTool::class,
            CustomerUpdateTool::class,
            ShipmentCreateTool::class,
            FileUploadTool::class,
            ArticleListTool::class,
            ArticleDetailTool::class,
            ArticleUpdateTool::class,
            CatalogListTool::class,
            PageListTool::class,
            TagListTool::class,
            CustomerAutocompleteTool::class,
            CategoryAutocompleteTool::class,
        ] as $toolClass) {
            $registry->register($this->app->make($toolClass));
        }
    }

    /**
     * Load AI config from system_setting into config('ai.*') for laravel/ai SDK.
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
