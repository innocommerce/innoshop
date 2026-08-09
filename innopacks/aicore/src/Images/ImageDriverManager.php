<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Images;

use Illuminate\Support\Facades\Log;

/**
 * Dispatches image generation to a vendor-specific driver based on provider.
 *
 * Built-in dispatch map covers OpenAI-compatible vendors (default) and
 * MiniMax (vendor-specific). Plugins can register an entirely different
 * driver class on `ai.image_generate_driver` with higher priority to
 * bypass this dispatcher entirely.
 */
class ImageDriverManager
{
    /**
     * Built-in vendor driver map, keyed by provider code.
     * Vendors not listed here fall back to OpenAiCompatibleImageDriver.
     */
    private const VENDOR_DRIVERS = [
        'minimax' => MinimaxImageDriver::class,
    ];

    public function generate(string $prompt, array $options = []): array
    {
        $provider = $options['provider'] ?? config('ai.default_for_images', 'openai');
        $driver   = self::VENDOR_DRIVERS[$provider] ?? OpenAiCompatibleImageDriver::class;

        Log::info('ImageDriverManager dispatch', [
            'provider'    => $provider,
            'driver'      => $driver,
            'has_options' => ! empty($options),
        ]);

        return (new $driver)->generate($prompt, $options);
    }
}
