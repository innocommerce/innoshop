<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Unit\Services;

use InnoShop\Aicore\Services\AIServiceManager;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class AIServiceManagerBaseUrlTest extends TestCase
{
    #[Test]
    public function test_override_url_targeting_loopback_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('public address');

        AIServiceManager::getInstance()->fetchAvailableModels('unknown-provider', '', 'http://127.0.0.1:11434/v1');
    }

    #[Test]
    public function test_override_url_targeting_link_local_metadata_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('public address');

        AIServiceManager::getInstance()->fetchAvailableModels('unknown-provider', 'sk-x', 'http://169.254.169.254/latest');
    }

    #[Test]
    public function test_override_url_without_explicit_key_never_gets_stored_key(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('explicit api_key');

        // 8.8.8.8 is public, so the SSRF guard passes; the request must then
        // fail because a custom URL may not fall back to the stored key.
        AIServiceManager::getInstance()->fetchAvailableModels('unknown-provider', '', 'https://8.8.8.8/v1');
    }
}
