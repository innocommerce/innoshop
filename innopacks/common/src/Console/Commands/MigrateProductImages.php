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
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Product;

/**
 * Product image migration command
 *
 * This command requires InnoShop version >= 0.4.0
 */
class MigrateProductImages extends Command
{
    protected $signature = 'product:migrate-images';

    protected $description = 'Migrate product images form product_images to products';

    public function handle(): void
    {
        // Check if system version is greater than or equal to 0.4.0
        $currentVersion = config('innoshop.version');
        if (version_compare($currentVersion, '0.4.0', '<')) {
            $this->error('This command requires InnoShop version 0.4.0 or higher.');
            $this->error('Current version: '.$currentVersion);

            return;
        }

        $this->migrateProductImages();
        $this->migrateSkuImages();
    }

    /**
     * @return void
     */
    private function migrateProductImages(): void
    {
        $products = Product::query()->get();
        foreach ($products as $product) {
            $this->info("Migrating product {$product->id} - {$product->translation->name}");
            $images = DB::table('product_images')
                ->where('product_id', $product->id)
                ->get();
            $images = $images->map(function ($image) {
                return $image->path;
            });
            DB::table('products')->where('id', $product->id)->update([
                'images' => $images->toArray(),
            ]);
        }
    }

    /**
     * @return void
     */
    private function migrateSkuImages(): void
    {
        $skus = Product\Sku::query()->get();
        foreach ($skus as $sku) {
            $this->info("Migrating sku {$sku->id} - {$sku->code}");
            $images = DB::table('product_images')
                ->where('product_id', $sku->product_id)
                ->where('belong_sku', true)
                ->get();
            $images = $images->map(function ($image) {
                return $image->path;
            });
            DB::table('product_skus')->where('id', $sku->id)->update([
                'images' => $images->toArray(),
            ]);
        }
    }
}
