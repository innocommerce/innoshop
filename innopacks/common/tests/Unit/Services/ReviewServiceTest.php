<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Models\Review;
use InnoShop\Common\Repositories\ReviewRepo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReviewServiceTest extends TestCase
{
    private ReflectionClass $modelReflection;

    private ReflectionClass $repoReflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelReflection = new ReflectionClass(Review::class);
        $this->repoReflection  = new ReflectionClass(ReviewRepo::class);
    }

    #[Test]
    public function test_review_model_uses_correct_table(): void
    {
        $model = new Review;
        $this->assertEquals('reviews', $model->getTable());
    }

    #[Test]
    public function test_review_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'customer_id', 'product_id', 'order_item_id', 'rating', 'content', 'like', 'dislike', 'active',
        ];

        $model    = new Review;
        $fillable = $model->getFillable();

        $this->assertEquals($expectedFillable, $fillable);
    }

    #[Test]
    public function test_review_model_has_customer_relationship(): void
    {
        $this->assertTrue($this->modelReflection->hasMethod('customer'));
        $method = $this->modelReflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_review_model_has_product_relationship(): void
    {
        $this->assertTrue($this->modelReflection->hasMethod('product'));
        $method = $this->modelReflection->getMethod('product');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_review_model_has_order_item_relationship(): void
    {
        $this->assertTrue($this->modelReflection->hasMethod('orderItem'));
        $method = $this->modelReflection->getMethod('orderItem');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_review_repo_has_get_criteria_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getCriteria'));
        $method = $this->repoReflection->getMethod('getCriteria');
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_review_repo_has_product_reviewed_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('productReviewed'));
        $method = $this->repoReflection->getMethod('productReviewed');
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_review_repo_has_order_reviewed_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('orderReviewed'));
        $method = $this->repoReflection->getMethod('orderReviewed');
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_review_repo_has_get_list_by_product_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getListByProduct'));
        $method = $this->repoReflection->getMethod('getListByProduct');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_review_repo_has_create_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('create'));
        $method = $this->repoReflection->getMethod('create');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_review_repo_has_builder_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('builder'));
        $method = $this->repoReflection->getMethod('builder');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_product_reviewed_returns_bool(): void
    {
        $method     = $this->repoReflection->getMethod('productReviewed');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    #[Test]
    public function test_order_reviewed_returns_bool(): void
    {
        $method     = $this->repoReflection->getMethod('orderReviewed');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    #[Test]
    public function test_get_criteria_returns_array(): void
    {
        $method     = $this->repoReflection->getMethod('getCriteria');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_review_repo_extends_base_repo(): void
    {
        $parentClass = $this->repoReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Repositories\BaseRepo', $parentClass->getName());
    }

    #[Test]
    public function test_review_model_extends_base_model(): void
    {
        $parentClass = $this->modelReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Models\BaseModel', $parentClass->getName());
    }
}
