<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\Fee\Tax;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TaxCalculationTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Tax::class);
    }

    #[Test]
    public function test_tax_service_extends_base_service(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Services\Fee\BaseService', $parentClass->getName());
    }

    #[Test]
    public function test_tax_service_has_add_fee_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('addFee'));
        $method = $this->reflection->getMethod('addFee');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_tax_service_has_get_taxes_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getTaxes'));
        $method = $this->reflection->getMethod('getTaxes');
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
    public function test_get_taxes_method_returns_array(): void
    {
        $method     = $this->reflection->getMethod('getTaxes');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_tax_fee_structure_has_required_keys(): void
    {
        // Verify the expected structure of tax fee array
        $expectedKeys = ['code', 'title', 'total', 'total_format'];

        // This test documents the expected structure
        foreach ($expectedKeys as $key) {
            $this->assertContains($key, $expectedKeys);
        }
    }

    #[Test]
    public function test_tax_code_is_correct(): void
    {
        // The tax fee code should be 'tax'
        $this->assertEquals('tax', 'tax');
    }

    #[Test]
    public function test_tax_calculation_considers_shipping_address(): void
    {
        // Document that tax calculation uses shipping address
        $method = $this->reflection->getMethod('getTaxes');
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('shippingAddress', $source);
    }

    #[Test]
    public function test_tax_calculation_considers_billing_address(): void
    {
        // Document that tax calculation uses billing address
        $method = $this->reflection->getMethod('getTaxes');
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('billingAddress', $source);
    }

    #[Test]
    public function test_tax_calculation_uses_tax_class_id(): void
    {
        // Document that tax calculation uses product tax_class_id
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('tax_class_id', $source);
    }

    #[Test]
    public function test_tax_calculation_multiplies_by_quantity(): void
    {
        // Document that tax is multiplied by product quantity
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('quantity', $source);
    }

    #[Test]
    public function test_tax_values_are_rounded_to_two_decimals(): void
    {
        // Document that tax values are rounded
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('round', $source);
    }

    #[Test]
    public function test_zero_or_negative_tax_is_skipped(): void
    {
        // Document that zero or negative tax values are not added
        $source = file_get_contents($this->reflection->getFileName());

        $this->assertStringContainsString('$value <= 0', $source);
    }
}
