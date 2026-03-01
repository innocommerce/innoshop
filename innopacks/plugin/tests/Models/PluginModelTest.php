<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Models;

use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PluginModelTest extends TestCase
{
    #[Test]
    public function test_plugin_model_class_exists(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
    }

    #[Test]
    public function test_has_table_property(): void
    {
        $reflection = new \ReflectionClass(Plugin::class);
        $this->assertTrue($reflection->hasProperty('table'));
    }

    #[Test]
    public function test_has_fillable_property(): void
    {
        $reflection = new \ReflectionClass(Plugin::class);
        $this->assertTrue($reflection->hasProperty('fillable'));
    }

    #[Test]
    public function test_fillable_contains_expected_fields(): void
    {
        $plugin   = new Plugin;
        $fillable = $plugin->getFillable();

        $this->assertContains('type', $fillable);
        $this->assertContains('code', $fillable);
        $this->assertContains('priority', $fillable);
    }

    #[Test]
    public function test_extends_base_model(): void
    {
        $this->assertTrue(is_subclass_of(Plugin::class, \InnoShop\Common\Models\BaseModel::class));
    }
}
