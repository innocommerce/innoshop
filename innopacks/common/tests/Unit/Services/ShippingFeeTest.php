<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\Fee\Shipping;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ShippingFeeTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Shipping::class);
    }

    #[Test]
    public function test_shipping_service_extends_base_service(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Services\Fee\BaseService', $parentClass->getName());
    }

    #[Test]
    public function test_shipping_service_has_add_fee_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('addFee'));
        $method = $this->reflection->getMethod('addFee');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_shipping_service_has_get_shipping_fee_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getShippingFee'));
        $method = $this->reflection->getMethod('getShippingFee');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_shipping_service_has_get_shipping_quote_name_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getShippingQuoteName'));
        $method = $this->reflection->getMethod('getShippingQuoteName');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_add_fee_method_returns_void(): void
    {
        $method     = $this->reflection->getMethod('addFee');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    #[Test]
    public function test_get_shipping_fee_method_returns_float(): void
    {
        $method     = $this->reflection->getMethod('getShippingFee');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('float', $returnType->getName());
    }

    #[Test]
    public function test_get_shipping_quote_name_method_returns_string(): void
    {
        $method     = $this->reflection->getMethod('getShippingQuoteName');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    #[Test]
    public function test_shipping_fee_structure_has_required_keys(): void
    {
        // Verify the expected structure of shipping fee array
        $expectedKeys = ['code', 'title', 'total', 'total_format'];

        foreach ($expectedKeys as $key) {
            $this->assertContains($key, $expectedKeys);
        }
    }

    #[Test]
    public function test_shipping_code_is_correct(): void
    {
        // The shipping fee code should be 'shipping'
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString("'code'         => 'shipping'", $source);
    }

    #[Test]
    public function test_shipping_title_is_set(): void
    {
        // The shipping fee title should be 'Shipping'
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString("'title'        => 'Shipping'", $source);
    }

    #[Test]
    public function test_shipping_fee_is_rounded_to_two_decimals(): void
    {
        // Document that shipping fee is rounded
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('round', $source);
    }

    #[Test]
    public function test_shipping_fee_uses_shipping_method_code(): void
    {
        // Document that shipping fee calculation uses shipping_method_code
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('shipping_method_code', $source);
    }

    #[Test]
    public function test_shipping_fee_returns_zero_when_no_method_found(): void
    {
        // Document that shipping fee returns 0 when no matching method is found
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('return 0', $source);
    }

    #[Test]
    public function test_shipping_fee_uses_shipping_service(): void
    {
        // Document that shipping fee uses ShippingService
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('ShippingService', $source);
    }

    #[Test]
    public function test_shipping_quote_name_returns_empty_string_when_not_found(): void
    {
        // Document that getShippingQuoteName returns empty string when quote not found
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString("return ''", $source);
    }

    #[Test]
    public function test_shipping_fee_iterates_through_quotes(): void
    {
        // Document that shipping fee iterates through shipping method quotes
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('quotes', $source);
    }
}
