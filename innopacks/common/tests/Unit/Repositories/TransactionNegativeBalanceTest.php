<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Repositories\Customer\TransactionRepo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TransactionNegativeBalanceTest extends TestCase
{
    #[Test]
    public function test_handle_data_rejects_negative_resulting_balance(): void
    {
        $source = file_get_contents((new ReflectionClass(TransactionRepo::class))->getFileName());

        $this->assertStringContainsString('Insufficient customer balance', $source);
        $this->assertStringContainsString('$balance < 0', $source);
    }
}
