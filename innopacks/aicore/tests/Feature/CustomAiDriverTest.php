<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Laravel\Ai\Ai;
use Laravel\Ai\Providers\DeepSeekProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomAiDriverTest extends TestCase
{
    public static function customDrivers(): array
    {
        return [
            'glm'     => ['glm'],
            'minimax' => ['minimax'],
        ];
    }

    #[DataProvider('customDrivers')]
    public function test_custom_driver_resolves_to_chat_completions_provider(string $driver): void
    {
        config(["ai.providers.custom_{$driver}" => [
            'driver' => $driver,
            'name'   => "custom_{$driver}",
            'key'    => 'fixture',
            'url'    => 'https://example.test/v1',
        ]]);

        $provider = Ai::textProvider("custom_{$driver}");

        $this->assertInstanceOf(DeepSeekProvider::class, $provider);
        $this->assertSame("custom_{$driver}", $provider->name());
        $this->assertSame('deepseek', $provider->driver());
    }
}
