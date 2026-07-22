<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Product\Sku;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SkuTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Sku::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Sku::class));
    }

    #[Test]
    public function test_fillable_contains_weight_field(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $this->assertContains('weight', $fillable);
    }

    #[Test]
    public function test_weight_is_cast_to_float(): void
    {
        $property = $this->reflection->getProperty('casts');
        $casts    = $property->getDefaultValue();

        $this->assertArrayHasKey('weight', $casts);
        $this->assertEquals('float', $casts['weight']);
    }
}
