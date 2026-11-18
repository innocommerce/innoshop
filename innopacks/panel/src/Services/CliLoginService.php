<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Services;

class CliLoginService extends BaseService
{
    /**
     * Validate that the callback URL points to a local address.
     *
     * The callback URL carries an admin API token, so only an explicit
     * loopback host is safe: never resolve or redirect to arbitrary hosts.
     *
     * @param  string  $url
     * @return bool
     */
    public function isValidCallbackUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }
        if (! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return false;
        }
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return false;
        }

        return in_array(strtolower($parsed['host']), ['localhost', '127.0.0.1', '[::1]'], true);
    }
}
