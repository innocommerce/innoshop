<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Services\CliLoginService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CliLoginControllerTest extends TestCase
{
    #[DataProvider('callbackProvider')]
    public function test_callback_only_accepts_explicit_loopback_urls(string $url, bool $expected): void
    {
        $this->assertSame($expected, CliLoginService::getInstance()->isValidCallbackUrl($url));
    }

    public static function callbackProvider(): array
    {
        return [
            'localhost HTTP'      => ['http://localhost:8000/callback', true],
            'IPv4 loopback HTTPS' => ['https://127.0.0.1/callback', true],
            'IPv6 loopback'       => ['http://[::1]:9000/callback', true],
            'public domain'       => ['https://attacker.example/callback', false],
            'private IP'          => ['http://192.168.1.20/callback', false],
            'localhost subdomain' => ['http://localhost.attacker.example/callback', false],
            'userinfo host spoof' => ['http://localhost@attacker.example/callback', false],
            'non-HTTP scheme'     => ['file:///etc/passwd', false],
            'missing host'        => ['https:///callback', false],
        ];
    }
}
