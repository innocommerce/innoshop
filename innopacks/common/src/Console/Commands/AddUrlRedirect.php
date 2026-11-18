<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use InnoShop\Common\Services\UrlRedirectService;

/**
 * Manual entry point for permanent 301 rules — the production stopgap for
 * deleted content ("deleted demo product → its category page") until the
 * redirect-manager plugin ships.
 *
 *   php artisan redirects:add product-demo-a product-real-b
 *   php artisan redirects:add gone-page /                    (home)
 *   php artisan redirects:add old-site https://example.com/  (external)
 */
class AddUrlRedirect extends Command
{
    protected $signature = 'redirects:add
        {from : Old path, locale-less canonical, e.g. product-demo-a}
        {to : New internal path, / for home, or an absolute external URL}';

    protected $description = 'Create a manual URL redirect (301) rule';

    public function handle(): int
    {
        $created = UrlRedirectService::upsert($this->argument('from'), $this->argument('to'));

        if (! $created) {
            $this->error('Rejected: empty path, identical paths, or a redirect loop.');

            return self::FAILURE;
        }

        $this->info('Redirect rule saved.');

        return self::SUCCESS;
    }
}
