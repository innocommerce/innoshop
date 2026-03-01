<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Services;

use InnoShop\Plugin\Services\MarketplaceService;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MarketplaceServiceTest extends TestCase
{
    #[Test]
    public function test_marketplace_service_class_exists(): void
    {
        $this->assertTrue(class_exists(MarketplaceService::class));
    }

    #[Test]
    public function test_has_get_instance_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getInstance'));
    }

    #[Test]
    public function test_has_set_page_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'setPage'));
    }

    #[Test]
    public function test_has_set_per_page_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'setPerPage'));
    }

    #[Test]
    public function test_has_get_plugin_categories_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getPluginCategories'));
    }

    #[Test]
    public function test_has_get_theme_categories_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getThemeCategories'));
    }

    #[Test]
    public function test_has_get_plugin_products_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getPluginProducts'));
    }

    #[Test]
    public function test_has_get_theme_products_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getThemeProducts'));
    }

    #[Test]
    public function test_has_get_product_detail_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getProductDetail'));
    }

    #[Test]
    public function test_has_get_market_products_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getMarketProducts'));
    }

    #[Test]
    public function test_has_get_market_products_with_params_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'getMarketProductsWithParams'));
    }

    #[Test]
    public function test_has_quick_checkout_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'quickCheckout'));
    }

    #[Test]
    public function test_has_download_method(): void
    {
        $this->assertTrue(method_exists(MarketplaceService::class, 'download'));
    }
}
