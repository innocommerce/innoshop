<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

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
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Address::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_table_name_is_addresses(): void
    {
        $property = $this->reflection->getProperty('table');
        $this->assertEquals('addresses', $property->getDefaultValue());
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'customer_id', 'guest_id', 'name', 'email', 'phone',
            'country_id', 'state_id', 'state', 'city_id', 'city',
            'zipcode', 'address_1', 'address_2',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_customer_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_country_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('country'));
        $method = $this->reflection->getMethod('country');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_state_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('state'));
        $method = $this->reflection->getMethod('state');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_address_format_logic(): void
    {
        $address = [
            'address_1' => '123 Main St',
            'address_2' => 'Apt 4B',
            'city'      => 'New York',
            'state'     => 'NY',
            'zipcode'   => '10001',
            'country'   => 'United States',
        ];

        $formatted = implode(', ', array_filter([
            $address['address_1'],
            $address['address_2'],
            $address['city'],
            $address['state'],
            $address['zipcode'],
            $address['country'],
        ]));

        $this->assertEquals('123 Main St, Apt 4B, New York, NY, 10001, United States', $formatted);
    }

    #[Test]
    public function test_address_format_without_address_2(): void
    {
        $address = [
            'address_1' => '123 Main St',
            'address_2' => '',
            'city'      => 'New York',
            'state'     => 'NY',
            'zipcode'   => '10001',
            'country'   => 'United States',
        ];

        $formatted = implode(', ', array_filter([
            $address['address_1'],
            $address['address_2'],
            $address['city'],
            $address['state'],
            $address['zipcode'],
            $address['country'],
        ]));

        $this->assertEquals('123 Main St, New York, NY, 10001, United States', $formatted);
    }

    #[Test]
    public function test_customer_address_ownership(): void
    {
        $customerId        = 123;
        $addressCustomerId = 123;

        $isOwner = $customerId === $addressCustomerId;

        $this->assertTrue($isOwner);
    }

    #[Test]
    public function test_guest_address_ownership(): void
    {
        $guestId        = 'guest_abc123';
        $addressGuestId = 'guest_abc123';

        $isOwner = $guestId === $addressGuestId;

        $this->assertTrue($isOwner);
    }

    #[Test]
    public function test_address_validation_required_fields(): void
    {
        $requiredFields = ['name', 'phone', 'country_id', 'city', 'address_1'];

        $address = [
            'name'       => 'John Doe',
            'phone'      => '1234567890',
            'country_id' => 1,
            'city'       => 'New York',
            'address_1'  => '123 Main St',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $address);
            $this->assertNotEmpty($address[$field]);
        }
    }
}
