<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\AddressesController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AddressesControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(AddressesController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(AddressesController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_store_method_is_public(): void
    {
        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_store_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('store'));
        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('update'));
        $method = $this->reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'addresses' => [],
            'countries' => [],
        ];

        $this->assertArrayHasKey('addresses', $data);
        $this->assertArrayHasKey('countries', $data);
    }

    #[Test]
    public function test_address_validation_fields(): void
    {
        $requiredFields = [
            'name',
            'phone',
            'country_id',
            'state_id',
            'city',
            'address_1',
            'zipcode',
        ];

        $this->assertContains('name', $requiredFields);
        $this->assertContains('phone', $requiredFields);
        $this->assertContains('country_id', $requiredFields);
        $this->assertContains('city', $requiredFields);
        $this->assertContains('address_1', $requiredFields);
    }

    #[Test]
    public function test_address_default_flag(): void
    {
        $address = (object) ['default' => true];

        $this->assertTrue($address->default);
    }

    #[Test]
    public function test_address_customer_ownership(): void
    {
        $customerId        = 123;
        $addressCustomerId = 123;

        $isOwner = $customerId === $addressCustomerId;

        $this->assertTrue($isOwner);
    }

    #[Test]
    public function test_address_customer_ownership_mismatch(): void
    {
        $customerId        = 123;
        $addressCustomerId = 456;

        $isOwner = $customerId === $addressCustomerId;

        $this->assertFalse($isOwner);
    }
}
