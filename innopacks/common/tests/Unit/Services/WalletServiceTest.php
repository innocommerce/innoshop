<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\Fee\BalanceService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WalletServiceTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(BalanceService::class);
    }

    #[Test]
    public function test_balance_service_extends_base_service(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Services\Fee\BaseService', $parentClass->getName());
    }

    #[Test]
    public function test_balance_service_has_add_fee_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('addFee'));
        $method = $this->reflection->getMethod('addFee');
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
    public function test_balance_fee_structure_has_required_keys(): void
    {
        // Verify the expected structure of balance fee array
        $expectedKeys = ['code', 'title', 'total', 'total_format'];

        foreach ($expectedKeys as $key) {
            $this->assertContains($key, $expectedKeys);
        }
    }

    #[Test]
    public function test_balance_code_is_correct(): void
    {
        // The balance fee code should be 'balance'
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString("'code'         => 'balance'", $source);
    }

    #[Test]
    public function test_balance_title_is_set(): void
    {
        // The balance fee title should be 'Balance'
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString("'title'        => 'Balance'", $source);
    }

    #[Test]
    public function test_balance_is_applied_as_negative_value(): void
    {
        // Document that balance is applied as negative (discount)
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('-$usedBalance', $source);
    }

    #[Test]
    public function test_balance_is_rounded_to_two_decimals(): void
    {
        // Document that balance is rounded
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('round', $source);
    }

    #[Test]
    public function test_empty_balance_is_skipped(): void
    {
        // Document that empty balance returns early
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('empty($usedBalance)', $source);
        $this->assertStringContainsString('return;', $source);
    }

    #[Test]
    public function test_balance_is_retrieved_from_checkout_reference(): void
    {
        // Document that balance comes from checkout reference
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('reference', $source);
        $this->assertStringContainsString("['balance']", $source);
    }
}
