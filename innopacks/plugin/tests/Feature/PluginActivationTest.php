<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Feature;

use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\Core\PluginManager;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginActivationTest extends TestCase
{
    #[Test]
    public function test_plugin_manager_has_get_enabled_plugins_method(): void
    {
        $this->assertTrue(method_exists(PluginManager::class, 'getEnabledPlugins'));
    }

    #[Test]
    public function test_plugin_manager_has_check_active_method(): void
    {
        $this->assertTrue(method_exists(PluginManager::class, 'checkActive'));
    }

    #[Test]
    public function test_plugin_has_set_enabled_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setEnabled'));
    }

    #[Test]
    public function test_plugin_has_get_enabled_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getEnabled'));
    }

    #[Test]
    public function test_plugin_has_check_active_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'checkActive'));
    }

    #[Test]
    public function test_plugin_has_check_installed_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'checkInstalled'));
    }

    #[Test]
    public function test_plugin_has_set_priority_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setPriority'));
    }

    #[Test]
    public function test_plugin_has_get_priority_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getPriority'));
    }

    #[Test]
    public function test_plugin_has_check_priority_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'checkPriority'));
    }
}
