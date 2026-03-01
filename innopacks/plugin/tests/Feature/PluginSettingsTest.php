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
use InnoShop\Plugin\Models\Setting;
use InnoShop\Plugin\Repositories\SettingRepo;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginSettingsTest extends TestCase
{
    #[Test]
    public function test_setting_model_exists(): void
    {
        $this->assertTrue(class_exists(Setting::class));
    }

    #[Test]
    public function test_setting_repo_exists(): void
    {
        $this->assertTrue(class_exists(SettingRepo::class));
    }

    #[Test]
    public function test_plugin_has_get_setting_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getSetting'));
    }

    #[Test]
    public function test_plugin_has_set_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'setFields'));
    }

    #[Test]
    public function test_plugin_has_get_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'getFields'));
    }

    #[Test]
    public function test_plugin_has_validate_fields_method(): void
    {
        $this->assertTrue(method_exists(Plugin::class, 'validateFields'));
    }

    #[Test]
    public function test_setting_repo_has_get_plugin_active_field_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginActiveField'));
    }

    #[Test]
    public function test_setting_repo_has_get_plugin_available_field_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginAvailableField'));
    }

    #[Test]
    public function test_setting_repo_has_get_plugin_fields_method(): void
    {
        $this->assertTrue(method_exists(SettingRepo::class, 'getPluginFields'));
    }

    #[Test]
    public function test_setting_model_has_fillable_fields(): void
    {
        $setting  = new Setting;
        $fillable = $setting->getFillable();

        $this->assertContains('space', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('json', $fillable);
    }
}
