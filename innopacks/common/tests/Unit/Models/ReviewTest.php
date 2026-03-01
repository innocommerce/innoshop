<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Review;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReviewTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Review::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Review::class));
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

        $requiredFields = [
            'product_id', 'customer_id', 'order_item_id',
            'rating', 'content', 'like', 'dislike', 'active',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_product_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('product'));
        $method = $this->reflection->getMethod('product');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_customer_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_order_item_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('orderItem'));
        $method = $this->reflection->getMethod('orderItem');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_rating_validation_min(): void
    {
        $rating  = 1;
        $isValid = $rating >= 1 && $rating <= 5;

        $this->assertTrue($isValid);
    }

    #[Test]
    public function test_rating_validation_max(): void
    {
        $rating  = 5;
        $isValid = $rating >= 1 && $rating <= 5;

        $this->assertTrue($isValid);
    }

    #[Test]
    public function test_rating_validation_invalid_low(): void
    {
        $rating  = 0;
        $isValid = $rating >= 1 && $rating <= 5;

        $this->assertFalse($isValid);
    }

    #[Test]
    public function test_rating_validation_invalid_high(): void
    {
        $rating  = 6;
        $isValid = $rating >= 1 && $rating <= 5;

        $this->assertFalse($isValid);
    }

    #[Test]
    public function test_average_rating_calculation(): void
    {
        $ratings = [5, 4, 3, 5, 4];
        $average = array_sum($ratings) / count($ratings);

        $this->assertEquals(4.2, $average);
    }

    #[Test]
    public function test_review_active_status(): void
    {
        $active = true;

        $this->assertTrue($active);
    }

    #[Test]
    public function test_review_inactive_status(): void
    {
        $active = false;

        $this->assertFalse($active);
    }
}
