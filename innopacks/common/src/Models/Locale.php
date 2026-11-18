<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class Locale extends BaseModel
{
    protected $fillable = [
        'name', 'code', 'image', 'position', 'active',
    ];

    /**
     * Boot: drop the compiled route cache on every locale write.
     *
     * The storefront registers its /{locale} routes while the application
     * boots, based on the locales that are enabled at that moment. Installing,
     * enabling or removing a language therefore changes the route table, and a
     * stale route cache would keep answering with a 404 for that language.
     *
     * Covers create, update (incl. the active toggle) and delete.
     */
    protected static function booted(): void
    {
        static::saved(function (): void {
            static::clearRouteCache();
        });

        static::deleted(function (): void {
            static::clearRouteCache();
        });
    }

    /**
     * Drop the compiled route cache.
     *
     * Nothing has to be done when the site does not use a route cache: the
     * storefront routes are then registered on every request anyway.
     */
    protected static function clearRouteCache(): void
    {
        // The application container is not always available: container-less
        // unit tests and early bootstrap have no Application bound yet.
        // Stay inert instead of breaking the write.
        $container = Container::getInstance();

        if (! $container->bound('app')) {
            return;
        }

        try {
            $app = $container->make('app');

            if (! file_exists($app->getCachedRoutesPath())) {
                return;
            }

            Artisan::call('route:clear');
        } catch (Throwable $e) {
            // A failed cleanup must never break installing or removing a
            // language, and reporting must not throw either when the
            // container is only partially available.
            try {
                report($e);
            } catch (Throwable) {
                // Nothing to report to.
            }
        }
    }
}
