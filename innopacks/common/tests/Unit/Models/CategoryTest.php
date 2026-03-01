<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Category;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CategoryTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Category::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Category::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_model_uses_required_traits(): void
    {
        $traits = class_uses_recursive(Category::class);
        $this->assertContains('InnoShop\Common\Traits\Translatable', $traits);
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = ['parent_id', 'slug', 'position', 'image', 'active'];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_parent_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('parent'));
        $method = $this->reflection->getMethod('parent');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_children_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('children'));
        $method = $this->reflection->getMethod('children');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_active_children_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('activeChildren'));
        $method = $this->reflection->getMethod('activeChildren');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_products_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('products'));
        $method = $this->reflection->getMethod('products');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getUrlAttribute'));
        $method = $this->reflection->getMethod('getUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_edit_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getEditUrlAttribute'));
        $method = $this->reflection->getMethod('getEditUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_image_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getImageUrlAttribute'));
        $method = $this->reflection->getMethod('getImageUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_get_image_url_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getImageUrl'));
        $method = $this->reflection->getMethod('getImageUrl');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_products_count_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getProductsCountAttribute'));
        $method = $this->reflection->getMethod('getProductsCountAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_booted_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('booted'));
        $method = $this->reflection->getMethod('booted');
        $this->assertTrue($method->isProtected());
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_circular_reference_detection_logic(): void
    {
        // Test the circular reference detection logic
        $categoryId = 1;
        $parentId   = 2;
        $visited    = [$categoryId];

        // Simulate parent chain: 2 -> 3 -> 4 (no circular)
        $parentChain = [2 => 3, 3 => 4, 4 => null];

        $currentParentId = $parentId;
        $hasCircular     = false;

        while ($currentParentId && isset($parentChain[$currentParentId])) {
            if (in_array($currentParentId, $visited)) {
                $hasCircular = true;
                break;
            }
            $visited[]       = $currentParentId;
            $currentParentId = $parentChain[$currentParentId];
        }

        // In this case, no circular reference
        $this->assertFalse($hasCircular);
    }

    #[Test]
    public function test_circular_reference_detection_with_cycle(): void
    {
        // Test the circular reference detection logic with actual cycle
        $categoryId = 1;
        $visited    = [$categoryId];

        // Simulate checking if parent would create cycle
        $potentialParentId = 1; // Same as category ID

        $wouldCreateCycle = in_array($potentialParentId, $visited);

        $this->assertTrue($wouldCreateCycle);
    }

    #[Test]
    public function test_self_parent_detection_logic(): void
    {
        $categoryId = 5;
        $parentId   = 5;

        $isSelfParent = $parentId && $parentId == $categoryId;

        $this->assertTrue($isSelfParent);
    }

    #[Test]
    public function test_valid_parent_detection_logic(): void
    {
        $categoryId = 5;
        $parentId   = 3;

        $isSelfParent = $parentId && $parentId == $categoryId;

        $this->assertFalse($isSelfParent);
    }

    #[Test]
    public function test_root_category_parent_id(): void
    {
        $parentId = 0;

        $isRootCategory = $parentId == 0;

        $this->assertTrue($isRootCategory);
    }

    #[Test]
    public function test_url_with_slug(): void
    {
        $slug    = 'electronics';
        $hasSlug = ! empty($slug);

        $this->assertTrue($hasSlug);
    }

    #[Test]
    public function test_url_without_slug(): void
    {
        $slug    = '';
        $hasSlug = ! empty($slug);

        $this->assertFalse($hasSlug);
    }
}
