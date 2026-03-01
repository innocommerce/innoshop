<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Common\Models\Address;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AddressTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Address::class);
    }

    #[Test]
    public function test_address_model_uses_correct_table(): void
    {
        $model = new Address;
        $this->assertEquals('addresses', $model->getTable());
    }

    #[Test]
    public function test_address_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'customer_id', 'guest_id', 'name', 'email', 'phone', 'country_id', 'state_id', 'state', 'city_id', 'city',
            'zipcode', 'address_1', 'address_2',
        ];

        $model    = new Address;
        $fillable = $model->getFillable();

        $this->assertEquals($expectedFillable, $fillable);
    }

    #[Test]
    public function test_address_model_has_customer_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_address_model_has_country_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('country'));
        $method = $this->reflection->getMethod('country');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_address_model_has_state_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('state'));
        $method = $this->reflection->getMethod('state');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_address_model_extends_base_model(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Models\BaseModel', $parentClass->getName());
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
    public function test_country_relationship_returns_belongs_to(): void
    {
        $method     = $this->reflection->getMethod('country');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Database\Eloquent\Relations\BelongsTo', $returnType->getName());
    }

    #[Test]
    public function test_state_relationship_returns_belongs_to(): void
    {
        $method     = $this->reflection->getMethod('state');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Database\Eloquent\Relations\BelongsTo', $returnType->getName());
    }

    #[Test]
    public function test_address_supports_guest_checkout(): void
    {
        // Document that address supports guest_id for guest checkout
        $model    = new Address;
        $fillable = $model->getFillable();

        $this->assertContains('guest_id', $fillable);
    }

    #[Test]
    public function test_address_has_two_address_lines(): void
    {
        // Document that address supports two address lines
        $model    = new Address;
        $fillable = $model->getFillable();

        $this->assertContains('address_1', $fillable);
        $this->assertContains('address_2', $fillable);
    }

    #[Test]
    public function test_address_supports_state_as_text(): void
    {
        // Document that address supports state as both ID and text
        $model    = new Address;
        $fillable = $model->getFillable();

        $this->assertContains('state_id', $fillable);
        $this->assertContains('state', $fillable);
    }
}
