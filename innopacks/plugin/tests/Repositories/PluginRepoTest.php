<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Repositories;

use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginRepoTest extends TestCase
{
    #[Test]
    public function test_plugin_repo_class_exists(): void
    {
        $this->assertTrue(class_exists(PluginRepo::class));
    }

    #[Test]
    public function test_has_get_instance_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getInstance'));
    }

    #[Test]
    public function test_has_all_plugins_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'allPlugins'));
    }

    #[Test]
    public function test_has_get_builder_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getBuilder'));
    }

    #[Test]
    public function test_has_get_plugins_group_code_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getPluginsGroupCode'));
    }

    #[Test]
    public function test_has_get_plugin_by_code_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getPluginByCode'));
    }

    #[Test]
    public function test_has_installed_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'installed'));
    }

    #[Test]
    public function test_has_check_active_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'checkActive'));
    }

    #[Test]
    public function test_has_get_priority_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getPriority'));
    }

    #[Test]
    public function test_has_get_shipping_methods_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getShippingMethods'));
    }

    #[Test]
    public function test_has_get_billing_methods_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'getBillingMethods'));
    }

    #[Test]
    public function test_has_shipping_method_options_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'shippingMethodOptions'));
    }

    #[Test]
    public function test_has_billing_method_options_method(): void
    {
        $this->assertTrue(method_exists(PluginRepo::class, 'billingMethodOptions'));
    }
}
