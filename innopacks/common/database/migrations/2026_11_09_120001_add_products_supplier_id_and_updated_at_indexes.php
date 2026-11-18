<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two missing product indexes that the front page and the supplier
     * statistics depend on:
     *
     * - products.supplier_id: the supplier statistics subquery counts the
     *   products of every supplier with a correlated COUNT, which scanned the
     *   whole catalogue (7.4M rows) because no index existed on the column.
     * - products.updated_at: the "latest products" block sorts the whole
     *   catalogue (12K rows) on every front page render.
     */
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        if (Schema::hasColumn('products', 'supplier_id') && ! Schema::hasIndex('products', 'idx_products_supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('supplier_id', 'idx_products_supplier_id');
            });
        }

        if (! Schema::hasIndex('products', 'idx_products_updated_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('updated_at', 'idx_products_updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        if (Schema::hasIndex('products', 'idx_products_supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_supplier_id');
            });
        }

        if (Schema::hasIndex('products', 'idx_products_updated_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('idx_products_updated_at');
            });
        }
    }
};
