<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link https://www.innoshop.com
 * @author InnoShop <team@innoshop.com>
 * @license https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace InnoShop\Panel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clears storefront catalog data before theme demo import (optional, user-triggered).
 * Order is safe with FOREIGN_KEY_CHECKS disabled on MySQL/MariaDB.
 */
class ThemeDemoCatalogResetService extends BaseService
{
    /**
     * Truncate tables that reference products, categories, or brands.
     */
    public function clearDefaultCatalogData(): void
    {
        $tables = [
            'order_return_histories',
            'order_return_payments',
            'order_returns',
            'order_option_values',
            'order_fees',
            'order_shipments',
            'order_payments',
            'order_histories',
            'order_items',
            'orders',
            'cart_option_values',
            'cart_items',
            'article_products',
            'customer_favorites',
            'reviews',
            'product_bundles',
            'product_option_values',
            'product_options',
            'product_attributes',
            'product_relations',
            'product_images',
            'product_videos',
            'product_translations',
            'product_skus',
            'product_categories',
            'products',
            'category_translations',
            'category_paths',
            'categories',
            'brand_translations',
            'brands',
        ];

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        smart_log('info', '[ThemeDemo] Default catalog data cleared before demo import');
    }
}
