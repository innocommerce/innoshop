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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->decimal('weight')->default(0)->comment('Weight');
        });

        // Backfill existing SKU rows with the parent product's weight value so
        // cart/checkout behaviour stays unchanged after moving weight to SKU level.
        if (Schema::hasTable('product_skus')) {
            Product::query()->select(['id', 'weight'])->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    DB::table('product_skus')
                        ->where('product_id', $product->id)
                        ->update(['weight' => $product->weight]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
