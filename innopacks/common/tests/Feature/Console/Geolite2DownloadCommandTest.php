<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Console;

use InnoShop\Common\Services\GeoLite2Service;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class Geolite2DownloadCommandTest extends TestCase
{
    #[Test]
    public function test_command_exits_zero_on_success(): void
    {
        $this->instance(GeoLite2Service::class, new class extends GeoLite2Service
        {
            public function downloadDatabase(): array
            {
                return ['success' => true, 'message' => 'ok'];
            }
        });

        $this->artisan('geolite2:download')->assertExitCode(0);
    }

    #[Test]
    public function test_command_exits_one_on_failure(): void
    {
        $this->instance(GeoLite2Service::class, new class extends GeoLite2Service
        {
            public function downloadDatabase(): array
            {
                return ['success' => false, 'message' => 'download_failed'];
            }
        });

        $this->artisan('geolite2:download')->assertExitCode(1);
    }
}
