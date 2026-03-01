<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Services;

use InnoShop\Plugin\Services\PluginService;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginServiceTest extends TestCase
{
    #[Test]
    public function test_plugin_service_class_exists(): void
    {
        $this->assertTrue(class_exists(PluginService::class));
    }

    #[Test]
    public function test_has_get_instance_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'getInstance'));
    }

    #[Test]
    public function test_has_install_plugin_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'installPlugin'));
    }

    #[Test]
    public function test_has_uninstall_plugin_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'uninstallPlugin'));
    }

    #[Test]
    public function test_has_migrate_database_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'migrateDatabase'));
    }

    #[Test]
    public function test_has_rollback_database_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'rollbackDatabase'));
    }
}
