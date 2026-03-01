<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Common\Models\Customer\Favorite;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FavoriteTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Favorite::class);
    }

    #[Test]
    public function test_favorite_model_uses_correct_table(): void
    {
        $model = new Favorite;
        $this->assertEquals('customer_favorites', $model->getTable());
    }

    #[Test]
    public function test_favorite_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['customer_id', 'product_id'];

        $model    = new Favorite;
        $fillable = $model->getFillable();

        $this->assertEquals($expectedFillable, $fillable);
    }

    #[Test]
    public function test_favorite_model_has_customer_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_favorite_model_has_product_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('product'));
        $method = $this->reflection->getMethod('product');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_favorite_model_extends_base_model(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Models\BaseModel', $parentClass->getName());
    }

    #[Test]
    public function test_favorite_model_is_in_customer_namespace(): void
    {
        $this->assertEquals(
            'InnoShop\Common\Models\Customer',
            $this->reflection->getNamespaceName()
        );
    }

    #[Test]
    public function test_customer_relationship_returns_belongs_to(): void
    {
        $method     = $this->reflection->getMethod('customer');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Database\Eloquent\Relations\BelongsTo', $returnType->getName());
    }

    #[Test]
    public function test_product_relationship_returns_belongs_to(): void
    {
        $method     = $this->reflection->getMethod('product');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Database\Eloquent\Relations\BelongsTo', $returnType->getName());
    }
}
