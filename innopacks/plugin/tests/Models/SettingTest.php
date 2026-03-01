<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Models;

use InnoShop\Plugin\Models\Setting;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SettingTest extends TestCase
{
    #[Test]
    public function test_setting_model_class_exists(): void
    {
        $this->assertTrue(class_exists(Setting::class));
    }

    #[Test]
    public function test_has_fillable_property(): void
    {
        $reflection = new \ReflectionClass(Setting::class);
        $this->assertTrue($reflection->hasProperty('fillable'));
    }

    #[Test]
    public function test_fillable_contains_expected_fields(): void
    {
        $setting  = new Setting;
        $fillable = $setting->getFillable();

        $this->assertContains('space', $fillable);
        $this->assertContains('name', $fillable);
        $this->assertContains('value', $fillable);
        $this->assertContains('json', $fillable);
    }

    #[Test]
    public function test_extends_base_model(): void
    {
        $this->assertTrue(is_subclass_of(Setting::class, \InnoShop\Common\Models\BaseModel::class));
    }
}
