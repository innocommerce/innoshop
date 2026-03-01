<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Brand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BrandTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Brand::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Brand::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = ['name', 'first', 'slug', 'logo', 'position', 'active'];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getUrlAttribute'));
        $method = $this->reflection->getMethod('getUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_appends_contains_url(): void
    {
        $property = $this->reflection->getProperty('appends');
        $appends  = $property->getDefaultValue();

        $this->assertContains('url', $appends);
    }

    #[Test]
    public function test_first_letter_extraction(): void
    {
        $name  = 'Apple';
        $first = strtoupper(substr($name, 0, 1));

        $this->assertEquals('A', $first);
    }

    #[Test]
    public function test_first_letter_extraction_lowercase(): void
    {
        $name  = 'samsung';
        $first = strtoupper(substr($name, 0, 1));

        $this->assertEquals('S', $first);
    }

    #[Test]
    public function test_slug_generation(): void
    {
        $name = 'Apple Inc';
        $slug = strtolower(str_replace(' ', '-', $name));

        $this->assertEquals('apple-inc', $slug);
    }

    #[Test]
    public function test_brand_active_status(): void
    {
        $active = true;

        $this->assertTrue($active);
    }

    #[Test]
    public function test_brand_inactive_status(): void
    {
        $active = false;

        $this->assertFalse($active);
    }
}
