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
use InnoShop\Plugin\Services\PluginService;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginInstallationTest extends TestCase
{
    #[Test]
    public function test_plugin_service_has_install_plugin_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'installPlugin'));
    }

    #[Test]
    public function test_plugin_service_has_uninstall_plugin_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'uninstallPlugin'));
    }

    #[Test]
    public function test_plugin_service_has_migrate_database_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'migrateDatabase'));
    }

    #[Test]
    public function test_plugin_service_has_rollback_database_method(): void
    {
        $this->assertTrue(method_exists(PluginService::class, 'rollbackDatabase'));
    }

    #[Test]
    public function test_plugin_manager_has_import_method(): void
    {
        $this->assertTrue(method_exists(PluginManager::class, 'import'));
    }

    #[Test]
    public function test_plugin_has_set_installed_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setInstalled'));
    }

    #[Test]
    public function test_plugin_has_check_installed_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'checkInstalled'));
    }

    #[Test]
    public function test_plugin_has_validate_config_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'validateConfig'));
    }

    #[Test]
    public function test_plugin_has_get_boot_file_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getBootFile'));
    }

    #[Test]
    public function test_plugin_has_get_path_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getPath'));
    }
}
