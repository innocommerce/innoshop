<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base TestCase for RestAPI tests.
 * Uses pure PHPUnit without Laravel dependencies.
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Setup method called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Teardown method called after each test.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
