<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Repositories;

use InnoShop\Plugin\Repositories\SettingRepo;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SettingRepoTest extends TestCase
{
    #[Test]
    public function test_setting_repo_class_exists(): void
    {
        $this->assertTrue(class_exists(SettingRepo::class));
    }

    #[Test]
    public function test_has_get_plugin_active_field_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginActiveField'));
    }

    #[Test]
    public function test_has_get_plugin_available_field_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginAvailableField'));
    }

    #[Test]
    public function test_has_get_plugin_fields_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginFields'));
    }

    #[Test]
    public function test_extends_common_setting_repo(): void
    {
        $this->assertTrue(is_subclass_of(SettingRepo::class, \InnoShop\Common\Repositories\SettingRepo::class));
    }
}
