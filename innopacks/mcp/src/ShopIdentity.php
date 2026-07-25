<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\MCP;

/**
 * Encapsulates shop identity metadata for MCP tool responses,
 * so clients can distinguish which instance a result came from.
 *
 * Reserved for SaaS: when multi-tenant support lands, toMeta() will
 * include the active shop_id as resolved by the request scope.
 */
class ShopIdentity
{
    /**
     * @return array{shop: array{name: string, host: string, url: string}}
     */
    public function toMeta(): array
    {
        return [
            'shop' => [
                'name' => $this->name(),
                'host' => $this->host(),
                'url'  => config('app.url', ''),
            ],
        ];
    }

    public function name(): string
    {
        return system_setting('shop_name') ?: config('app.name', 'InnoShop');
    }

    public function host(): string
    {
        return parse_url(config('app.url', ''), PHP_URL_HOST) ?: '';
    }
}
