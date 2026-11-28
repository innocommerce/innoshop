<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Repositories\OrderReturnRepo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OrderReturnCustomerFilterTest extends TestCase
{
    #[Test]
    public function test_builder_applies_customer_id_filter(): void
    {
        $source = file_get_contents((new ReflectionClass(OrderReturnRepo::class))->getFileName());

        $this->assertMatchesRegularExpression(
            "/\\\$customerID\s*=\s*\\\$filters\['customer_id'\]/",
            $source
        );
        $this->assertStringContainsString("where('customer_id', \$customerID)", $source);
    }
}
