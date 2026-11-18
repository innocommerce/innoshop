<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Middleware;

use Closure;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Admin;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelPermission
{
    private const AUTHENTICATED_ONLY = [
        'auth.admin',
        'dashboard.index',
        'dashboard.daily',
    ];

    /**
     * Apply the same route permission model used by the web panel. Unknown
     * routes fail closed so a newly-added sensitive endpoint is not public to
     * every admin role by default.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user();
        if (! $admin instanceof Admin) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $routeName = $request->route()->getName();
        $shortName = str_starts_with((string) $routeName, 'api.panel.')
            ? substr($routeName, strlen('api.panel.'))
            : $routeName;
        $permissions = self::permissionsFor($shortName);

        if ($permissions === null || ($permissions !== [] && ! $admin->canAny($permissions))) {
            return json_fail('Forbidden', null, 403);
        }

        return $next($request);
    }

    /**
     * Return allowed web-panel permission codes, [] for authenticated-only
     * routes, or null for an unmapped route (which must be denied).
     */
    public static function permissionsFor(?string $routeName): ?array
    {
        if (in_array($routeName, self::AUTHENTICATED_ONLY, true)) {
            return [];
        }

        $map = [
            'products.names'            => ['products_index', 'products_create', 'products_edit', 'articles_create', 'articles_edit', 'reviews_create', 'reviews_edit', 'themes_settings_index', 'themes_settings_update'],
            'products.autocomplete'     => ['products_index', 'products_create', 'products_edit', 'articles_create', 'articles_edit', 'reviews_create', 'reviews_edit', 'themes_settings_index', 'themes_settings_update'],
            'products.sku_autocomplete' => ['products_index', 'products_create', 'products_edit'],
            'products.import'           => ['products_create'],
            'categories.names'          => ['categories_index', 'products_create', 'products_edit', 'themes_settings_index', 'themes_settings_update'],
            'categories.autocomplete'   => ['categories_index', 'products_create', 'products_edit', 'themes_settings_index', 'themes_settings_update'],
            'brands.name'               => ['brands_index', 'products_create', 'products_edit'],
            'brands.autocomplete'       => ['brands_index', 'products_create', 'products_edit'],
            'articles.names'            => ['articles_index', 'articles_create', 'articles_edit', 'themes_settings_index', 'themes_settings_update'],
            'articles.autocomplete'     => ['articles_index', 'articles_create', 'articles_edit', 'themes_settings_index', 'themes_settings_update'],
            'catalogs.names'            => ['catalogs_index', 'articles_create', 'articles_edit'],
            'catalogs.autocomplete'     => ['catalogs_index', 'articles_create', 'articles_edit'],
            'pages.names'               => ['pages_index', 'pages_create', 'pages_edit', 'themes_settings_index', 'themes_settings_update'],
            'pages.autocomplete'        => ['pages_index', 'pages_create', 'pages_edit', 'themes_settings_index', 'themes_settings_update'],
            'tags.index'                => ['tags_index', 'articles_create', 'articles_edit'],
            'tags.name'                 => ['tags_index', 'articles_create', 'articles_edit'],
            'tags.autocomplete'         => ['tags_index', 'articles_create', 'articles_edit'],
            'customers.name'            => ['customers_index', 'transactions_create', 'reviews_create', 'reviews_edit'],
            'customers.autocomplete'    => ['customers_index', 'transactions_create', 'reviews_create', 'reviews_edit'],
            'shipments.index'           => ['orders_index'],
            'shipments.show'            => ['orders_show'],
            'shipments.get_traces'      => ['orders_show'],
            'shipments.store'           => ['orders_edit'],
            'shipments.destroy'         => ['orders_edit'],
            'orders.update_note'        => ['orders_edit'],
            'attribute_values.index'    => ['attributes_index', 'attributes_create', 'attributes_update', 'products_create', 'products_edit'],
            'options.available'         => ['options_available', 'products_create', 'products_edit'],
            'options.values'            => ['options_values'],
            'media.get_storage_config'  => ['settings_index'],
            'media.save_storage_config' => ['settings_update'],
            'locales.store'             => ['locales_install'],
            'locales.destroy'           => ['locales_uninstall'],
            'order_returns.update'      => ['order_returns_update'],
            'ai.generate_image'         => ['media_index', 'media_iframe', 'upload_images'],
            'ai.models_info'            => ['media_index', 'media_iframe', 'upload_images'],
        ];

        foreach (['products', 'categories', 'brands', 'articles', 'catalogs', 'pages', 'tags', 'customers', 'currencies', 'reviews', 'tax_rates', 'tax_classes', 'locales', 'countries', 'regions', 'order_returns'] as $module) {
            $map[$module.'.index'] ??= [$module.'_index'];
            $map[$module.'.show'] ??= [$module.'_show'];
            $map[$module.'.store'] ??= [$module.'_store'];
            $map[$module.'.update'] ??= [$module.'_update'];
            $map[$module.'.patch'] ??= [$module.'_update'];
            $map[$module.'.destroy'] ??= [$module.'_destroy'];
        }

        $map['products.show']        = ['products_index'];
        $map['categories.show']      = ['categories_index'];
        $map['reviews.show']         = ['reviews_index'];
        $map['locales.show']         = ['locales_index'];
        $map['order_returns.show']   = ['order_returns_index'];
        $map['orders.index']         = ['orders_index'];
        $map['orders.show']          = ['orders_show'];
        $map['orders.change_status'] = ['orders_change_status'];
        $map['attributes.index']     = ['attributes_index'];

        $mediaRead = ['media_index', 'media_iframe'];
        foreach (['media.get_files', 'media.get_directories', 'media.stats', 'media.media_detail'] as $route) {
            $map[$route] = $mediaRead;
        }

        $mediaWrite = ['media_index', 'media_iframe'];
        foreach (['media.create_directory', 'media.rename', 'media.delete_files', 'media.delete_directories', 'media.move_directories', 'media.move_files', 'media.copy_files', 'media.media_update'] as $route) {
            $map[$route] = $mediaWrite;
        }

        $map['media.upload']     = ['media_index', 'media_iframe', 'upload_images', 'upload_files'];
        $map['media.set_poster'] = ['media_index', 'media_iframe', 'upload_images', 'upload_files'];
        $map['settings.index']   = ['settings_index'];
        $map['settings.update']  = ['settings_update'];

        return $map[$routeName] ?? null;
    }
}
