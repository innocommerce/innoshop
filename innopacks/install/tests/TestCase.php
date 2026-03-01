<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Install module TestCase
 *
 * This TestCase does NOT use RefreshDatabase because Install tests
 * need to test the installation process before database tables exist.
 */
abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;

    /**
     * Creates the application.
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../../../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
}
