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

class BalanceClampTest extends TestCase
{
    #[Test]
    public function test_add_fee_clamps_client_balance_to_available_and_payable(): void
    {
        $source = file_get_contents((new ReflectionClass(BalanceService::class))->getFileName());

        $this->assertStringContainsString('getBalanceAmount()', $source);
        $this->assertStringContainsString('getAccumulatedFeeTotal()', $source);
        $this->assertStringContainsString('min($requested, $available, $payable)', $source);
        $this->assertStringNotContainsString('empty($usedBalance)', $source);
    }
}
