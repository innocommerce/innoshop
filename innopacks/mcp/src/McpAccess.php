<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Mcp;

use InnoShop\Aicore\Contracts\ToolInterface;

/**
 * MCP exposure policy: the endpoint is opt-in, and write tools stay hidden
 * until the merchant explicitly enables them in the panel settings card.
 */
final class McpAccess
{
    public static function enabled(): bool
    {
        return (bool) system_setting('mcp_enabled', false);
    }

    public static function writeEnabled(): bool
    {
        return (bool) system_setting('mcp_write_enabled', false);
    }

    /**
     * @param  array<string, ToolInterface>  $tools
     * @return array<string, ToolInterface>
     */
    public static function filterTools(array $tools): array
    {
        if (self::writeEnabled()) {
            return $tools;
        }

        return array_filter($tools, fn (ToolInterface $tool) => ! $tool->isWrite());
    }

    /**
     * Every language pack the MCP welcome page ships with. Independent of the
     * store's frontend locale settings — mirroring the panel login page, all
     * available packs are offered regardless of what's enabled for the front.
     *
     * @return array<string, array{code: string, name: string, image: string}>
     */
    public static function locales(): array
    {
        $items = [];
        foreach (glob(__DIR__.'/../lang/*/welcome.php') as $file) {
            $code         = basename(dirname($file));
            $items[$code] = [
                'code'  => $code,
                'name'  => self::languageName($code),
                'image' => "images/flags/$code.svg",
            ];
        }
        ksort($items);

        return $items;
    }

    private static function languageName(string $code): string
    {
        $baseFile = base_path("lang/$code/common/base.php");
        if (is_file($baseFile)) {
            $data = require $baseFile;

            return $data['language_name'] ?? $code;
        }

        return $code;
    }
}
