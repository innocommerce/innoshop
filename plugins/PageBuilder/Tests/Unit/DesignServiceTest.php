<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Plugin\PageBuilder\Services\DesignService;
use ReflectionMethod;

class DesignServiceTest extends TestCase
{
    public function test_module_ids_accept_only_positive_integers(): void
    {
        $method = new ReflectionMethod(DesignService::class, 'extractIds');

        $ids = $method->invoke(new DesignService, [
            ['id' => '3'],
            ['id' => 1],
            ['id' => '(SELECT SLEEP(3))'],
            ['id' => '1,updatexml(1,1,1)'],
            ['id' => ['nested' => 1]],
            ['id' => -1],
            '2',
            '0',
            4.5,
        ]);

        $this->assertSame([3, 1, 2], $ids);
    }
}
